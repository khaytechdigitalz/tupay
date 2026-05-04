<?php

use App\Models\SendMail;
use Illuminate\Support\Facades\Mail;

if (!function_exists('amount')) {
    function amount($amount, $currency)
    {
        return $currency.''.number_format($amount, 2);
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($email, $message, $page)
    {
        $content = [
            'message' => $message,
            'name' => $message['name'],
            'subject' => $message['subject'],
            'page' => $page,
        ];

        try {
            Mail::to($email)->send(new SendMail($content));
        } catch (Exception $exp) {
        }
    }
}
