<div class="bg-white rounded shadow mt-12 p-5 max-w-xl mx-auto">
    @if($this->order)
        <p>
            Thank you for your order (#{{ $this->order->id }})
        </p>
    @else
        <p wire:poll>
            Waiting for payment confirmation. Please standby...
        </p>
    @endif
</div>
