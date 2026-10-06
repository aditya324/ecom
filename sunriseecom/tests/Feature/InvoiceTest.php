<?php

use App\Mail\InvoiceMail;
use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('a customer can open an invoice for a placed order', function () {
    $user = User::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-INV001',
        'user_id' => $user->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'coupon_code' => 'SAVE10',
        'discount' => 100,
        'gst' => 162,
        'total' => 1062,
        'refunded_amount' => 200,
    ]);
    $order->items()->create([
        'service_name' => 'Website Launch',
        'duration_label' => 'One-time',
        'price' => 1000,
        'discount' => 100,
        'gst' => 162,
    ]);

    $this->actingAs($user)
        ->get(route('profile.orders'))
        ->assertOk()
        ->assertSee(route('orders.invoice', $order), false);

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Download invoice')
        ->assertSee(route('orders.invoice', $order), false);

    $download = $this->actingAs($user)
        ->get(route('orders.invoice', $order));

    $download->assertOk();
    $download->assertHeader('content-type', 'application/pdf');
    expect($download->getContent())->toStartWith('%PDF')
        ->and(view('pdf.invoice', ['order' => $order->load('items')])->render())
        ->toContain('SR-INV001')
        ->toContain('Website Launch')
        ->toContain('SAVE10')
        ->toContain('1,062.00')
        ->toContain('200.00');
});

test('another customer cannot open the invoice', function () {
    $owner = User::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-INV002',
        'user_id' => $owner->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('orders.invoice', $order))
        ->assertNotFound();

    $order->update(['status' => 'pending']);

    $this->actingAs($owner)
        ->get(route('orders.invoice', $order))
        ->assertNotFound();
});

test('an admin can email the invoice to the customer', function () {
    Mail::fake();

    $admin = Admin::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-INV004',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'discount' => 0,
        'gst' => 180,
        'total' => 1180,
    ]);
    $order->items()->create([
        'service_name' => 'Website Launch',
        'duration_label' => 'One-time',
        'price' => 1000,
        'discount' => 0,
        'gst' => 180,
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Email invoice');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.invoice', $order))
        ->assertRedirect()
        ->assertSessionHas('status', 'Invoice emailed to asha@example.com.');

    Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail) use ($order) {
        $mail->assertSeeInHtml('attached as a PDF');

        $attached = $mail->attachments()[0];
        [$pdf, $meta] = $attached->attachWith(
            fn () => null,
            fn ($data) => [$data(), ['as' => $attached->as, 'mime' => $attached->mime]],
        );

        expect($meta['as'])->toBe('SR-INV004.pdf')
            ->and($meta['mime'])->toBe('application/pdf')
            ->and($pdf)->toStartWith('%PDF');

        return $mail->hasTo('asha@example.com') && $mail->order->is($order);
    });

    $order->load('items');

    expect(view('pdf.invoice', ['order' => $order])->render())->toContain('Website Launch')
        ->and(view('pdf.invoice', ['order' => $order])->render())->toContain('1,180.00');
});

test('an invoice email is not sent for an unpaid order', function () {
    Mail::fake();

    $admin = Admin::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-INV005',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'pending',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.invoice', $order))
        ->assertRedirect()
        ->assertSessionHasErrors('order');

    Mail::assertNothingSent();
});

test('a guest is sent to login before an invoice', function () {
    $order = Order::query()->create([
        'number' => 'SR-INV003',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);

    $this->get(route('orders.invoice', $order))
        ->assertRedirect(route('login'));
});
