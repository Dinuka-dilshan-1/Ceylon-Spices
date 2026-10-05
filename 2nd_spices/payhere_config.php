<?php
declare(strict_types=1);

/*
 * PayHere configuration for Ceylon Spice Hub.
 *
 * IMPORTANT:
 * - Keep the merchant secret on the server. Never put it in JavaScript or HTML.
 * - Start with SANDBOX while developing.
 * - For payment notifications, PAYHERE_PUBLIC_BASE_URL must be a public HTTPS URL
 *   that points to this project (PayHere cannot call localhost directly).
 */
const PAYHERE_MODE = 'sandbox'; // sandbox | live
const PAYHERE_MERCHANT_ID = '1238498';
const PAYHERE_MERCHANT_SECRET = 'Mjk1NDIyMzU3MTMxNzgyNjQwNjMzNTM0NjIwNTA2MTE0NTczNjI2OA==';

// CHANGE THIS to your public URL before testing payment callbacks.
// Example: https://xxxx.ngrok-free.app
const PAYHERE_PUBLIC_BASE_URL = 'http://localhost/2nd_spices';

function payhere_checkout_url(): string {
    return PAYHERE_MODE === 'live'
        ? 'https://www.payhere.lk/pay/checkout'
        : 'https://sandbox.payhere.lk/pay/checkout';
}

function payhere_url(string $path): string {
    return rtrim(PAYHERE_PUBLIC_BASE_URL, '/') . '/' . ltrim($path, '/');
}

function payhere_hash(int|string $merchantId, string $orderId, float $amount, string $currency, string $secret): string {
    return strtoupper(md5(
        (string)$merchantId .
        $orderId .
        number_format($amount, 2, '.', '') .
        $currency .
        strtoupper(md5($secret))
    ));
}

function payhere_notify_hash(string $merchantId, string $orderId, string $amount, string $currency, string $statusCode, string $secret): string {
    return strtoupper(md5(
        $merchantId .
        $orderId .
        $amount .
        $currency .
        $statusCode .
        strtoupper(md5($secret))
    ));
}
