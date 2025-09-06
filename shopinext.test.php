<?php
require_once __DIR__ . '/shopinext.class.php';

// Configuration
$mode = 'test'; // 'test' or 'prod'
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Initialize the Shopinext API client
$shopinext = new Shopinext($clientId, $clientSecret, $mode);

// Authenticate with the API
if (!$shopinext->authenticate()) {
    die('Authentication failed.');
}

// Payment data
$paymentData = [
    'firstname' => 'Hasan',
    'surname' => 'Oncel',
    'email' => 'test@shopinext.com',
    'amount' => 200,
    'currency' => 'TRY',
    'max_installment' => 1,
    'merchant_order_id' => 'ORDER123456',
    'identity_number' => '11111111111',
    'is_digital' => 0,
    'company' => '',
    'tax_office' => '',
    'tax_number' => '',
    'order_products' => [
        [
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 100,
            'total' => 200
        ]
    ],
    'billing_info' => [
        'billing_firstname' => 'Hasan',
        'billing_surname' => 'Oncel',
        'billing_address' => 'Example St. No: 3/4',
        'billing_city' => 'Besiktas',
        'billing_state' => 'Istanbul',
        'billing_postal_code' => '34330',
        'billing_country' => 'Turkey',
        'billing_country_code'  => '+90',
        'billing_phone' => '5445553366'
    ],
    'shipping_info' => [
        'shipping_firstname' => 'Hasan',
        'shipping_surname' => 'Oncel',
        'shipping_address' => 'Example St. No: 3/4',
        'shipping_city' => 'Besiktas',
        'shipping_state' => 'Istanbul',
        'shipping_postal_code' => '34330',
        'shipping_country' => 'Turkey',
        'shipping_country_code'  => '+90',
        'shipping_phone' => '5445553366'
    ],
    'success_url' => 'https://www.example.com/success',
    'fail_url' => 'https://www.example.com/fail',
    'callback_url' => 'https://www.example.com/callback',
    'language' => 'EN'
];

// Create the payment
$response = $shopinext->createPayment($paymentData);

// Display result
if ($response['status'] == 1) {
    $paymentUrl = 'https://' . 
        ($mode === 'test' ? 'checkout.dev.shopinext.com' : 'checkout.shopinext.com') . 
        '/' . strtolower($paymentData["language"]) . '/' . 
        $response['payment_id'];

    echo 'Payment created successfully.<br><br>';
    echo '<a href="' . $paymentUrl . '" target="_blank">' . $paymentUrl . '</a>';
} else {
    echo 'Failed to create payment.';
}
?>
