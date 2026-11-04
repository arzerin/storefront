<div class="grid grid-cols-2 gap-4">
    <x-panel title="Order #{{ $this->order->id }}" class="mt-12 col-span-2">
        <table class="w-full">
            <thead>
            <tr>
                <th class="text-left">Product</th>
                <th class="text-left">Quantity</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($this->order->items as $item)
                <tr>
                    <td>
                        {{ $item->name }}
                        {{ $item->description }}
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">
                        {{ $item->amount_total }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            @if($this->order->amount_shipping->isPositive())
                <tr>
                    <td colspan="2" class="pt-5 text-right font-medium">Shipping</td>
                    <td class="pt-5 font-medium text-right">{{ $this->order->amount_shipping }}</td>
                </tr>
            @endif
            @if($this->order->amount_discount->isPositive())
                <tr>
                    <td colspan="2" class="pt-5 text-right font-medium">Discount</td>
                    <td class="pt-5 font-medium text-right">{{ $this->order->amount_discount }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="2" class="pt-5 text-right font-medium">Tax</td>
                <td class="pt-5 font-medium text-right">{{ $this->order->amount_tax }}</td>
            </tr>
            <tr>
                <td colspan="2" class="pt-5 text-right font-medium">Subtotal</td>
                <td class="pt-5 font-medium text-right">{{ $this->order->amount_subtotal }}</td>
            </tr>
            <tr>
                <td colspan="2" class="pt-5 text-right font-medium">Total</td>
                <td class="pt-5 font-medium text-right">{{ $this->order->amount_total }}</td>
            </tr>
            </tfoot>
        </table>
    </x-panel>

    <x-panel title="Billing Information" class="col-span-1">
        @foreach($this->order->billing_address->filter() as $value)
            {{ $value }} <br/>
        @endforeach
    </x-panel>

    <x-panel title="Shipping Address" class="col-span-1">
        @foreach($this->order->shipping_address->filter() as $value)
            {{ $value }} <br/>
        @endforeach
    </x-panel>
</div>
