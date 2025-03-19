<x-filament-panels::page>
    <!-- Invoice Header -->

    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .invoice-table,
            .invoice-table * {
                visibility: visible;
            }

            .invoice-table {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>

    @if (session()->has('success'))
    <div class="bg-green-500 text-white p-4 rounded-md mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="w-full">
        <h2 class="font-semibold text-xl">Order Status</h2>
        <div class="flex items-center gap-x-3 mt-2">
            @foreach(['pending', 'processing', 'shipped','completed', 'canceled'] as $status)
            <div class="text-center flex items-center gap-x-2">
                <input type="radio" name="status" id="{{ $status }}" wire:click="updateStatus('{{ $status }}')"
                    @checked($order->status == $status)
                >
                <label for="{{ $status }}" class="block text-sm font-medium cursor-pointer">
                    {{ ucfirst($status) }}
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="w-full mx-auto p-6 shadow-lg rounded-lg border border-gray-300 invoice-table">
        <div class="text-center flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-indigo-600">Invoice</h1>
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="">
            </a>
        </div>

        <div class="flex justify-between items-start mt-6">
            <div class="text-start">
                <h3 class="text-sm font-normal"><strong class="text-base">Name</strong>: {{ $order->customer_name }}
                </h3>
                <h4 class="text-sm font-normal"><strong class="text-base">Phone</strong>: {{ $order->customer_phone }}
                </h4>
                <h4 class="text-sm font-normal"><strong class="text-base">Address</strong>: {{ $order->customer_address
                    }}</h4>
                <h5 class="text-sm font-normal"><strong class="text-base">Shipping zone</strong>: {{
                    $order->shipping_zone }}</h5>
                <h5 class="text-sm font-normal capitalize"><strong class="text-base">Status</strong>: {{ $order->status
                    }}</h5>

            </div>
            <div class="text-end">
                <p class="text-gray-700 mb-2"><strong>Order ID#</strong>{{ $order->order_number }}</p>
                <div class="text-sm">
                    <span class="font-bold">Order Date:</span> {{ \Carbon\Carbon::parse($order->created_at)->format('jS
                    F, Y') }}
                </div>
            </div>
        </div>

        <!-- Order Details Table -->
        <div class="overflow-x-auto mt-6">
            <table class="w-full border-collapse border border-gray-300">
                <thead class="bg-gray-200 dark:bg-transparent">
                    <tr>
                        <th class="border px-4 py-2">SL</th>
                        <th class="border px-4 py-2">Product</th>
                        <th class="border px-4 py-2">Quantity</th>
                        <th class="border px-4 py-2">Unit Price</th>
                        <th class="border px-4 py-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderItems as $key => $orderItem)
                    <tr class="border-b">
                        <td class="border px-4 py-2 text-center">{{ $key + 1 }}</td>
                        <td class="border px-4 py-2">{{ $products[$orderItem->product_id]['name'] }}</td>
                        <td class="border px-4 py-2 text-center">{{ $orderItem->quantity }}</td>
                        <td class="border px-4 py-2 text-center">{{
                            number_format($products[$orderItem->product_id]['price'], 1) }} BDT</td>
                        <td class="border px-4 py-2 text-center">{{ number_format($orderItem->price, 1) }} BDT</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-right"><strong>Subtotal</strong>:</td>
                        <td class="border px-4 py-2 text-center">{{ number_format($order->total_price -
                            $order->shipping_cost, 1) }} BDT</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-right"><strong>Shipping cost</strong>:</td>
                        <td class="border px-4 py-2 text-center">{{ number_format($order->shipping_cost, 1) }} BDT</td>
                    </tr>

                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-right"><strong>Total</strong>:</td>
                        <td class="border px-4 py-2 text-center text-lg font-semibold bg-gray-100 dark:bg-transparent">
                            {{ number_format($order->total_price, 1) }} BDT</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Payment and Date Info -->
        <div class="mt-6 flex justify-start gap-x-3 items-center">
            
            <button type="button" onclick="window.print();" style="background: #ccc"
                class="text-black text-sm font-semibold py-2 px-6 rounded-md anim hover:bg-third">
                Print
            </button>
            <button type="button" style="background: #ccc" wire:click="generatePdf()" wire:loading.attr="disabled"
                class="text-black text-sm font-semibold py-2 px-6 rounded-md anim hover:bg-third flex items-center gap-x-2">
                <span wire:loading.remove wire:target="generatePdf">Download</span>
                <span wire:loading wire:target="generatePdf" class="flex items-center gap-x-2">Downloading
                    <svg class="w-3 h-3 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 01-8 8z">
                        </path>
                    </svg>
                </span>
            </button>

            <button type="button" style="background: #ddd" wire:click="sendOrderUpdateEmail()"
                wire:loading.attr="disabled"
                class="text-black text-sm font-semibold py-2 px-6 rounded-md anim hover:bg-third flex items-center gap-x-2">
                <span wire:loading.remove wire:target="sendOrderUpdateEmail">Email</span>
                <span wire:loading wire:target="sendOrderUpdateEmail" class="flex items-center gap-x-2">Sending
                    <svg class="w-3 h-3 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 01-8 8z">
                        </path>
                    </svg>
                </span>
            </button>

            <button type="button" style="background: #ddd" wire:click="sendOrderUpdateSms()"
                wire:loading.attr="disabled"
                class="text-black text-sm font-semibold py-2 px-6 rounded-md anim hover:bg-third flex items-center gap-x-2">
                <span wire:loading.remove wire:target="sendOrderUpdateSms">SMS</span>
                <span wire:loading wire:target="sendOrderUpdateSms" class="flex items-center gap-x-2">Sending
                    <svg class="w-3 h-3 animate-spin text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 01-8 8z">
                        </path>
                    </svg>
                </span>
            </button>

        </div>
    </div>

    <!-- Invoice Form for Dynamic Data -->

</x-filament-panels::page>