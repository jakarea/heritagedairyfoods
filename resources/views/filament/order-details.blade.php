<!-- resources/views/filament/order-details.blade.php -->
<table class="w-full border-collapse border border-gray-300">
    <thead>
        <tr>
            <th class="border border-gray-300 px-4 py-2">SL</th>
            <th class="border border-gray-300 px-4 py-2">Order ID</th>
            <th class="border border-gray-300 px-4 py-2">Product ID</th>
            <th class="border border-gray-300 px-4 py-2">Quantity</th>
            <th class="border border-gray-300 px-4 py-2">Price</th>
            <th class="border border-gray-300 px-4 py-2">Subtotal</th>
            <th class="border border-gray-300 px-4 py-2">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($orderItems as $key => $orderItem)
        <tr>
            <td class="border border-gray-300 px-4 py-2">{{ $key + 1 }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $orderItem->order_id }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $orderItem->product_id }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $orderItem->quantity }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $orderItem->price }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $orderItem->subtotal }}</td>
            <td class="border border-gray-300 px-4 py-2">
                {{ \Carbon\Carbon::parse($orderItem->createdA_at)->translatedFormat('jS F, Y') }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center border border-gray-300 px-4 py-2">No payment history available.</td>
        </tr>
        @endforelse
    </tbody>
</table>
