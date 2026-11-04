<x-app-layout>
    <section class="mx-auto max-w-2xl px-5 py-20 text-center text-slate-900 dark:text-slate-100">
        <div class="mx-auto mb-7 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-3xl text-amber-800" aria-hidden="true">↩</div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Checkout canceled</p>
        <h1 class="mb-4 text-3xl font-semibold">Your bag is still here.</h1>
        <p class="mx-auto max-w-lg text-slate-600 dark:text-slate-300">The checkout was canceled. You can return to your bag when you are ready.</p>
        <a href="{{ route('cart.show') }}" class="mt-8 inline-flex rounded-md bg-slate-900 px-6 py-3 font-semibold text-white hover:bg-slate-700">Return to bag</a>
    </section>
</x-app-layout>
