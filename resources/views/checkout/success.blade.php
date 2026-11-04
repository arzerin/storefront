<x-app-layout>
    <section class="mx-auto max-w-2xl px-5 py-20 text-center text-slate-900 dark:text-slate-100">
        <div class="mx-auto mb-7 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-3xl text-emerald-800" aria-hidden="true">✓</div>
        @if($order)
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Payment confirmed</p>
            <h1 class="mb-4 text-3xl font-semibold">Thank you for your order.</h1>
            <p class="mx-auto max-w-lg text-slate-600 dark:text-slate-300">Order #{{ $order->id }} is confirmed. You can review its details anytime.</p>
            <a href="{{ route('order.show', $order->id) }}" class="mt-8 inline-flex rounded-md bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800">View order</a>
        @elseif($sessionId)
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Checkout received</p>
            <h1 class="mb-4 text-3xl font-semibold">We are confirming your payment.</h1>
            <p class="mx-auto max-w-lg text-slate-600 dark:text-slate-300">Your order will appear in My Orders after Stripe confirms it. Please check again shortly.</p>
            <a href="{{ route('my.orders') }}" class="mt-8 inline-flex rounded-md bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800">View my orders</a>
        @else
            <h1 class="mb-4 text-3xl font-semibold">Checkout status unavailable.</h1>
            <p class="mx-auto max-w-lg text-slate-600 dark:text-slate-300">Open My Orders to see any confirmed purchases.</p>
            <a href="{{ route('my.orders') }}" class="mt-8 inline-flex rounded-md bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800">View my orders</a>
        @endif
    </section>
</x-app-layout>
