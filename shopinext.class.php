<?php

class Shopinext
{
    private $client_id;
    private $client_secret;
    private $access_token;
    private $refresh_token;
    private $base_url;

    /**
     * Constructor to initialize the Shopinext API client
     *
     * @param string $client_id
     * @param string $client_secret
     * @param string $mode 'test' or 'prod'
     */
    public function __construct($client_id, $client_secret, $mode = 'test')
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        // Choose API base URL based on environment
        $this->base_url = ($mode === 'prod') 
            ? 'https://api.shopinext.com' 
            : 'https://api.dev.shopinext.com';
    }

    /**
     * General-purpose HTTP request handler
     *
     * @param string $endpoint
     * @param array $data
     * @param bool $auth
     * @param string $method 'GET' or 'POST'
     * @return array|null
     */
    private function request($endpoint, $data = [], $auth = false, $method = 'POST')
    {
        $url = $this->base_url . $endpoint;

        // Prepare headers
        $headers = [
            'Content-Type: application/json',
            'Domain: ' . ($_SERVER['HTTP_HOST'] ?? 'localhost') // Fallback for CLI
        ];

        // Add Authorization header if needed
        if ($auth && $this->access_token) {
            $headers[] = 'Authorization: Bearer ' . $this->access_token;
        }

        $ch = curl_init();

        // Handle GET or POST methods
        if (strtoupper($method) === 'GET') {
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        // Execute the request
        $response = curl_exec($ch);

        // Throw an exception on error
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        // Decode and return the JSON response
        return json_decode($response, true);
    }

    /**
     * Authenticate and get access + refresh tokens
     *
     * @return bool
     */
    public function authenticate()
    {
        $data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];

        $response = $this->request('/authenticate', $data, false, 'POST');

        if ($response['status'] == 1) {
            $this->access_token = $response['access_token'];
            $this->refresh_token = $response['refresh_token'];
            return true;
        }

        return false;
    }

    /**
     * Refresh the access token using a valid refresh token
     *
     * @return bool
     * @throws Exception
     */
    public function refreshToken()
    {
        if (!$this->refresh_token) {
            throw new Exception('Refresh token is not set.');
        }

        $data = ['refresh_token' => $this->refresh_token];
        $response = $this->request('/refreshToken', $data, false, 'POST');

        if ($response['status'] == 1) {
            $this->access_token = $response['access_token'];
            $this->refresh_token = $response['refresh_token'];
            return true;
        }

        return false;
    }

    /**
     * Create a payment session
     *
     * @param array $paymentData
     * @return array|null
     */
    public function createPayment($paymentData)
    {
        return $this->request('/createPayment', $paymentData, true, 'POST');
    }

    /**
     * Retrieve a payment by its ID
     *
     * @param string $payment_id
     * @return array|null
     */
    public function getPayment($payment_id)
    {
        return $this->request('/getPayment', ['payment_id' => $payment_id], true, 'GET');
    }

    /**
     * Get installment options by BIN number
     *
     * @return array|null
     */
    public function getInstallments()
    {
        return $this->request('/getInstallments', [], true, 'GET');
    }

    /**
     * Get commission details for a given amount and currency
     *
     * @return array|null
     */
    public function getCommissions()
    {
        return $this->request('/getCommissions', [], true, 'GET');
    }
}
?>