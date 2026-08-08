@extends('admin.layouts.app')

@section('content')
<div style="font-family: Arial, Helvetica, sans-serif; color: #222; padding: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 style="margin:0;">{{ config('app.name', 'Gardening') }}</h2>
            <div style="color:#666">Garden supplies and services</div>
        </div>
        <div style="text-align:right">
            <h3 style="margin:0">Invoice</h3>
            <div style="color:#666">#{{ $invoice_number }}</div>
            <div style="margin-top:8px;">Date: {{ $order_date }}</div>
        </div>
    </div>

    <hr style="margin:18px 0; border:none; border-top:1px solid #eee">

    <div style="display:flex; justify-content:space-between; gap:24px;">
        <div style="flex:1">
            <strong>Bill To</strong>
            <div>{{ $customer->name }}</div>
            @if(!empty($customer->email))<div>{{ $customer->email }}</div>@endif
            @if(!empty($billing_address))
                <div style="margin-top:8px; white-space:pre-wrap;">{{ $billing_address }}</div>
            @endif
        </div>

        <div style="width:240px; text-align:right">
            <div><strong>Order #</strong></div>
            <div>{{ $order->order_number }}</div>
            <div style="margin-top:12px"><strong>Payment</strong></div>
            <div>Status: {{ ucfirst($payment_status) }}</div>
            <div>Method: {{ $payment_method }}</div>
        </div>
    </div>

    <div style="margin-top:18px">
        <table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5; text-align:left;">
                    <th>#</th>
                    <th>Product</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Unit Price</th>
                    <th style="text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product_name ?? $item->name ?? 'Product' }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:right">{{ number_format($item->price, 2) }}</td>
                    <td style="text-align:right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="display:flex; justify-content:flex-end; margin-top:18px;">
        <table style="width:360px; border-collapse:collapse;">
            <tr>
                <td>Subtotal</td>
                <td style="text-align:right">₹{{ number_format($summary['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td>GST (18%)</td>
                <td style="text-align:right">₹{{ number_format($summary['gst'], 2) }}</td>
            </tr>
            <tr>
                <td>Delivery Charge</td>
                <td style="text-align:right">₹{{ number_format($summary['delivery'], 2) }}</td>
            </tr>
            <tr style="font-weight:bold; border-top:1px solid #ddd">
                <td>Total</td>
                <td style="text-align:right">₹{{ number_format($summary['grand_total'], 2) }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($notes))
    <div style="margin-top:20px; color:#555">
        <strong>Notes</strong>
        <div style="white-space:pre-wrap">{{ $notes }}</div>
    </div>
    @endif

    <div style="margin-top:28px; color:#999; font-size:12px">This is a computer-generated invoice and does not require a signature.</div>
</div>
@endsection
