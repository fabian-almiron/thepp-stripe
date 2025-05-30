<?php
require_once(__DIR__ . '/vendor/autoload.php');

$endpoint_secret = 'your_webhook_signing_secret';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object;
        // Handle successful payment
        // You might want to update your database here
        break;
    case 'payment_intent.payment_failed':
        $paymentIntent = $event->data->object;
        // Handle failed payment
        break;
}

http_response_code(200);
