<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f9f9f9;">
    <div style="width: 100%; max-width: 800px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); position: relative;">
        <div style="text-align: center; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; color: #4F46E5;">Invoice</h1>
            <a href="{{ url('/') }}">
                <img src="{{ public_path('images/logo.svg') }}" alt="Company Logo" style="max-width: 150px;">
            </a>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 24px;">
            <div style="text-align: left;">
                <h3 style="font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Name:</strong> {{ $order->customer_name }}</h3>
                <h4 style="font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Phone:</strong> {{ $order->customer_phone }}</h4>
                <h4 style="font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Address:</strong> {{ $order->customer_address }}</h4>
                <h5 style="font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Shipping zone:</strong> {{ $order->shipping_zone }}</h5>
                <h5 style="font-size: 14px; font-weight: normal; text-transform: capitalize;"><strong style="font-size: 16px;">Status:</strong> {{ $order->status }}</h5>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 14px; color: #4F46E5; margin-bottom: 10px;"><strong>Order ID#</strong>{{ $order->order_number }}</p>
                <div style="font-size: 14px;">
                    <span style="font-weight: bold;">Order Date:</span> {{ \Carbon\Carbon::parse($order->created_at)->format('jS F, Y') }}
                </div>
            </div>
        </div>

        <!-- Order Details Table -->
        <div style="overflow-x: auto; margin-top: 24px;">
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <thead style="background-color: #f3f4f6;">
                    <tr>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">SL</th>
                        <th style="padding: 12px; border: 1px solid #ddd;">Product</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Quantity</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Unit Price</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderItems as $key => $orderItem)
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ $key + 1 }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $products[$orderItem->product_id]['name'] }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ $orderItem->quantity }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ number_format($products[$orderItem->product_id]['price'], 1) }} BDT</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ number_format($orderItem->price, 1) }} BDT</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" style="padding: 12px; border: 1px solid #ddd; text-align: right;"><strong>Subtotal:</strong></td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ number_format($order->total_price - $order->shipping_cost, 1) }} BDT</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding: 12px; border: 1px solid #ddd; text-align: right;"><strong>Shipping cost:</strong></td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ number_format($order->shipping_cost, 1) }} BDT</td>
                    </tr>

                    <tr>
                        <td colspan="4" style="padding: 12px; border: 1px solid #ddd; text-align: right;"><strong>Total:</strong></td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center; background-color: #f3f4f6; font-size: 18px; font-weight: bold;">
                            {{ number_format($order->total_price, 1) }} BDT
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
