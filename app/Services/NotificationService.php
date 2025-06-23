<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;


class NotificationService
{

    public $products = [];

    public function __construct()
    {
        $this->loadProducts();
    }

    private function loadProducts()
    {
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

    public function sendSms($recipient, $message)
    {
        $apiKey = config('sms.api_key');
        $apiUrl = config('sms.api_url');

        $response = Http::get($apiUrl, [
            'api_key' => $apiKey,
            'to' => $recipient,
            'msg' => $message,
        ]);

        // Log the API response
        // Log::info('SMS API Response', [
        //     'status' => $response->status(),
        //     'body' => $response->body(),
        // ]);
    }

    public function sendEmail($subject, $order, $orderItems)
    {

        // Create the email body
        $body = [
            'order' => $order,
            'orderItems' => $orderItems,
            'products' => $this->products,
        ];

        $mail = env('RECIPIENT_EMAIL_ADDRESS', 'heritagedairyfoods@gmail.com');

        // Load mPDF
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

        $mpdf->SetFont('nikosh');

        // Create the PDF output
        $pdfOutput = view('emails.order-mail-attachment', $body)->render();
        $mpdf->WriteHTML($pdfOutput);

        // Save the PDF file to a variable
        $pdfOutput = $mpdf->Output('', 'S');

        // Send the email with the attached PDF
        Mail::send('emails.order-mail-attachment', $body, function ($message) use ($mail, $subject, $pdfOutput, $order) {
            $message->to($mail);
            $message->subject($subject);
            $message->attachData($pdfOutput, 'order-' . $order->order_number . '-' . $order->customer_name . '.pdf', [
                'mime' => 'application/pdf'
            ]);
        });

        // Log email details
        Log::info('Email Sent', [
            'recipient' => $mail,
            'subject' => $subject,
            'attachment' => 'order-' . $order->order_number . '-' . $order->customer_name . '.pdf'
        ]);
    }
}