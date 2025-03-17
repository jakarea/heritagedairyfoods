<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f9f9f9;">
    <div
        style="width: 100%; max-width: 800px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); position: relative;">
        

        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 style="font-size: 24px; font-weight: bold; color: #4F46E5;">Invoice</h1> 
                </td>
                <td style="text-align: right">
                    <img src="https://i.ibb.co.com/KzzYNY4G/Screenshot-1.png" alt="Heritage Logo" style="max-width: 180px;  margin-left: auto;">
                </td>
            </tr>
        </table>

        <table style="width: 100%;">
            <tr>
                <td  valign="top">
                    <p style="margin: 0; font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Name:</strong> {{
                        $order->customer_name }}</p>
                    <p style="margin: 0;font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Phone:</strong> {{
                        $order->customer_phone }}</p>
                    <p style="margin: 0;font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Address:</strong> {{
                        $order->customer_address }}</p>
                    <p style="margin: 0;font-size: 14px; font-weight: normal;"><strong style="font-size: 16px;">Shipping
                            zone:</strong> {{ $order->shipping_zone }}</p>
                    <p style="margin: 0;font-size: 14px; font-weight: normal; text-transform: capitalize;"><strong
                            style="font-size: 16px;">Status:</strong> {{ $order->status }}</p>
                </td>
                <td valign="top" style="text-align: right">
                    <p style="font-size: 14px; color: #4F46E5; margin-bottom: 10px;"><strong>Order ID#</strong>{{
                        $order->order_number }}</p>
                    <p style="margin: 0;"><span style="font-weight: bold;">Order Date:</span> {{
                        \Carbon\Carbon::parse($order->created_at)->format('jS F, Y') }}</p>
                </td>
            </tr>
        </table> 

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
                        <td style="padding: 12px; border: 1px solid #ddd;">
                            {{-- Check if the product exists --}}
                            {{ isset($products[$orderItem->product_id]) ? $products[$orderItem->product_id]['name'] : 'Product not found' }}
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ $orderItem->quantity
                            }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                            {{-- Check if price exists --}}
                            {{ isset($products[$orderItem->product_id]) ?
                            number_format($products[$orderItem->product_id]['price'], 1) : '0.00' }} BDT
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{
                            number_format($orderItem->price, 1) }} BDT</td>
                    </tr>
                    @endforeach

                    <tr>
                        <td colspan="4" style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>Subtotal:</strong></td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{
                            number_format($order->total_price - $order->shipping_cost, 1) }} BDT</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>Shipping cost:</strong></td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{
                            number_format($order->shipping_cost, 1) }} BDT</td>
                    </tr>

                    <tr>
                        <td colspan="4" style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            <strong>Total:</strong></td>
                        <td
                            style="padding: 12px; border: 1px solid #ddd; text-align: center; background-color: #f3f4f6; font-size: 18px; font-weight: bold;">
                            {{ number_format($order->total_price, 1) }} BDT
                        </td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%; margin-top: 50px;">
                <tr>
                    <td>
                        <p style="text-align: center; font-size: 12px; margin-top: 20px;">
                            This is a computer-generated invoice. No signature is required.<br>
                            &copy; 2025 Heritage Dairy Food Products. All Rights Reserved.
                        </p>                        
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>