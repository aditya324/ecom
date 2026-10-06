<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $pages = [
            'about' => [
                'title' => 'About Us',
                'body' => [
                    'Sunrise Digital is a marketplace for agency-grade digital services. Businesses buy websites, branding, marketing, and SEO as clear offers with a price, a scope, and a delivery time.',
                    'The catalog is run by Sunrise. Each service is sold and delivered through this store, with GST added on the price and an invoice for every placed order.',
                ],
            ],
            'careers' => [
                'title' => 'Careers',
                'body' => [
                    'Sunrise Digital hires people who can ship client work: design, development, marketing, and project delivery.',
                    'There are no open roles listed on this page yet. Write to the address in the footer when a role is a fit, and include the work you want to do.',
                ],
            ],
            'press' => [
                'title' => 'Press',
                'body' => [
                    'Sunrise Digital is a store for packaged digital services. For a press question, use the email in the footer and include the publication and the deadline.',
                    'Product facts live on this site: the services, the packages, and the prices shown at checkout.',
                ],
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'body' => [
                    'Sunrise Digital stores the account details you give us: your name, email, and the billing address used at checkout. Orders, payments, and reviews are kept so we can deliver the service and show the invoice.',
                    'Payment card details are handled by Razorpay. We store the payment reference Razorpay returns, not the card number.',
                    'We use this information to run the store, send invoices, and answer support requests. We do not sell the account list.',
                ],
            ],
            'terms' => [
                'title' => 'Terms of Service',
                'body' => [
                    'Buying a service or package on Sunrise Digital is an order for that listed scope. The price at checkout, plus 18% GST, is the amount charged. Subscriptions renew for the period shown until they are cancelled.',
                    'You need an account to pay. The work starts after the payment is placed. A quote request is not an order until you accept a price and pay.',
                ],
            ],
            'refund' => [
                'title' => 'Refund Policy',
                'body' => [
                    'A placed order can be refunded by Sunrise through Razorpay. The refund returns the amount of that payment. A subscription refund covers the charge that was taken, and cancelling the subscription stops later renewals.',
                    'When a refund is recorded, it appears on the order and on the invoice. Write to support with the order number if a payment was taken and the order is not on your account.',
                ],
            ],
        ];

        abort_unless(isset($pages[$page]), 404);

        return view('pages.show', [
            'page' => $pages[$page],
        ]);
    }
}
