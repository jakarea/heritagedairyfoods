<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderInfo;
use App\Services\NotificationService;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;


class OrderDetails extends Page
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.order-details';

    public $order;
    public $orderItems;
    public $products = [];
    public $status;
    protected $notificationService;

    public $messages = [
        'pending' => 'Your order is received and pending confirmation. Stay tuned! - Heritage Dairy Foods',
        'processing' => 'Your order is being processed. We will update you soon! - Heritage Dairy Foods',
        'shipped' => 'Great news! Your order has been shipped. Track it for updates. - Heritage Dairy Foods',
        'completed' => 'Your order has been delivered successfully. Enjoy! - Heritage Dairy Foods',
        'canceled' => 'Your order has been canceled. Contact support if needed. - Heritage Dairy Foods',
    ];

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function mount($id): void
    {
        $this->order = Order::findOrFail($id);
        $this->orderItems = $this->order->orderItems;
        $this->status = $this->order->status;

        $jsonPath = storage_path('app/public/products.json');

        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $products = json_decode($jsonContent, true);

            foreach ($products as $product) {
                $newProduct[$product['id']] = $product;
            }

            $this->products = $newProduct;
        }
    }

    public function updateStatus($status)
    {
        DB::table('orders')->where('id', $this->order->id)->update(['status' => $status]);
        $this->order->status = $status;
        $this->dispatch('statusUpdated', $status);

        // send sms
        $this->sendOrderUpdateSms();
        // $this->sendOrderUpdateEmail();
    }

    // send sms
    public function sendOrderUpdateSms()
    {

        $orderId = "Your Order ID #{$this->order->order_number}\n";
        $message = $orderId . $this->messages[$this->order->status];

        $this->notificationService->sendSms($this->order->customer_phone, $message);
    }

    // send email
    public function sendOrderUpdateEmail()
    {
        $this->notificationService->sendEmail('Order Status Updated - #' . $this->order->order_number, $this->order, $this->orderItems);
        session()->flash('success', 'Email sent successfully.');
    }

    public function generatePdf()
    {
        try {
            $body = [
                'order' => $this->order,
                'orderItems' => $this->orderItems,
                'products' => $this->products,
            ];

            // Create an instance of mPDF 
            $mpdf = new Mpdf([
                'default_font' => 'nikosh',
                'mode' => 'utf-8',
                'margin_top' => 10,
                'margin_bottom' => 10,
                'format' => 'A4',
                'fontdata' => [
                    'nikosh' => [
                        'R' => 'Nikosh.ttf',
                        'B' => 'Nikosh.ttf',
                    ]
                ]
            ]);

            // Define the PDF content (You can use Blade templates as well)
            $html = View::make('emails.order-mail-attachment', $body)->render();

            // Write HTML content to PDF
            $mpdf->WriteHTML($html);

            // Set the filename for download
            $fileName = 'invoice_' . $this->order->order_number . '.pdf';

            // Output PDF for download
            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', 'S'); // Send output to the browser
            }, $fileName, ['Content-Type' => 'application/pdf']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }
}
