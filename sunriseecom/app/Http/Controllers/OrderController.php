<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $this->visibleTo($request, $order);

        $order->load(['items', 'subscriptions']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    public function invoice(Request $request, Order $order): Response
    {
        $this->visibleTo($request, $order);

        $order->load('items');

        return Pdf::loadView('pdf.invoice', ['order' => $order])
            ->download($order->number.'.pdf');
    }

    private function visibleTo(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id && in_array($order->status, Order::settledStatuses(), true), 404);
    }
}
