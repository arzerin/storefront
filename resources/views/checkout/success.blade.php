<x-app-layout><div class="ayra-page ayra-result-page"><div class="ayra-result-card"><span class="ayra-result-icon success">✓</span><span class="ayra-eyebrow">AYRA · ORDER UPDATE</span>
@if($order)
    <h1>Order confirmed<span>.</span></h1><p>Thank you for shopping with Ayra. Order <strong>#{{ $order->id }}</strong> has been placed successfully.</p>
    <div class="ayra-order-timeline"><div class="current"><i>✓</i><span>Order placed</span></div><div><i>2</i><span>Processing</span></div><div><i>3</i><span>Shipped</span></div><div><i>4</i><span>Delivered</span></div></div>
    <div class="ayra-result-meta"><span>Payment<strong>{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid by card' }}</strong></span><span>Total<strong>{{ $order->amount_total }}</strong></span></div>
    <div class="ayra-result-actions"><a href="{{ route('order.show', $order->id) }}" class="ayra-button">View your order →</a><a href="{{ route('home') }}#collection" class="ayra-outline-button">Continue shopping</a></div>
@elseif($sessionId)
    <h1>Confirming your order<span>.</span></h1><p>We're waiting for payment confirmation. Your order will appear in My Orders once confirmed.</p><div class="ayra-result-actions"><a href="{{ route('my.orders') }}" class="ayra-button">View my orders →</a></div>
@else
    <h1>Checkout status unavailable<span>.</span></h1><p>Visit My Orders to see your confirmed purchases.</p><div class="ayra-result-actions"><a href="{{ route('my.orders') }}" class="ayra-button">View my orders →</a></div>
@endif
</div></div></x-app-layout>
