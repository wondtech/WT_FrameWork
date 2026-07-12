<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Stripe Payments API
 ************************************************************************/

namespace WT\Plugins;

class Stripe
{
    private string $base = 'https://api.stripe.com/v1';
    private string $secretKey;
    private string $webhookSecret;
    private string $apiVersion;

    public function __construct()
    {
        $this->secretKey     = $_ENV['STRIPE_SECRET_KEY']     ?? '';
        $this->webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        $this->apiVersion    = $_ENV['STRIPE_API_VERSION']    ?? '';
    }

    // ── Checkout ────────────────────────────────────────────────────────────

    /** Hosted Checkout Session. Pass Stripe params (line_items, mode, success_url, cancel_url…). */
    public function createCheckoutSession(array $params): ?array
    {
        return $this->request('POST', '/checkout/sessions', $params);
    }

    public function retrieveCheckoutSession(string $id): ?array
    {
        return $this->request('GET', '/checkout/sessions/' . rawurlencode($id));
    }

    // ── Payment Intents ─────────────────────────────────────────────────────

    /** $amount is in the smallest currency unit (e.g. cents / halalas). */
    public function createPaymentIntent(int $amount, string $currency, array $opts = []): ?array
    {
        return $this->request('POST', '/payment_intents', array_merge([
            'amount'   => $amount,
            'currency' => strtolower($currency),
        ], $opts));
    }

    public function retrievePaymentIntent(string $id): ?array
    {
        return $this->request('GET', '/payment_intents/' . rawurlencode($id));
    }

    public function capturePaymentIntent(string $id, array $opts = []): ?array
    {
        return $this->request('POST', '/payment_intents/' . rawurlencode($id) . '/capture', $opts);
    }

    public function cancelPaymentIntent(string $id): ?array
    {
        return $this->request('POST', '/payment_intents/' . rawurlencode($id) . '/cancel');
    }

    // ── Customers ───────────────────────────────────────────────────────────

    public function createCustomer(array $params): ?array
    {
        return $this->request('POST', '/customers', $params);
    }

    public function retrieveCustomer(string $id): ?array
    {
        return $this->request('GET', '/customers/' . rawurlencode($id));
    }

    // ── Refunds ─────────────────────────────────────────────────────────────

    /** Refund a PaymentIntent fully, or partially when $amount (smallest unit) is given. */
    public function createRefund(string $paymentIntentId, ?int $amount = null): ?array
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount !== null) {
            $params['amount'] = $amount;
        }
        return $this->request('POST', '/refunds', $params);
    }

    // ── Webhook ─────────────────────────────────────────────────────────────

    /**
     * Verify a webhook payload against the `Stripe-Signature` header
     * (HMAC-SHA256 over "{timestamp}.{payload}" with STRIPE_WEBHOOK_SECRET).
     * Returns the decoded event on success, null on failure.
     */
    public function verifyWebhook(string $payload, string $sigHeader, int $tolerance = 300): ?array
    {
        if ($this->webhookSecret === '' || $sigHeader === '') {
            return null;
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $sigHeader) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) { continue; }
            if ($kv[0] === 't')  { $timestamp = $kv[1]; }
            if ($kv[0] === 'v1') { $signatures[] = $kv[1]; }
        }

        if ($timestamp === null || $signatures === []) {
            return null;
        }
        if ($tolerance > 0 && abs(time() - (int)$timestamp) > $tolerance) {
            return null;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $this->webhookSecret);
        $matched  = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) { $matched = true; break; }
        }
        if (!$matched) {
            return null;
        }

        $event = json_decode($payload, true);
        return is_array($event) ? $event : null;
    }

    // ── HTTP ────────────────────────────────────────────────────────────────

    /**
     * Signed request to the Stripe API. Body is form-encoded (Stripe uses
     * application/x-www-form-urlencoded with bracket notation for nested params).
     * Returns the decoded JSON response, or null on transport/HTTP error.
     */
    private function request(string $method, string $path, array $params = []): ?array
    {
        if ($this->secretKey === '') {
            return null;
        }

        $url     = $this->base . $path;
        $headers = ['Authorization: Bearer ' . $this->secretKey];
        if ($this->apiVersion !== '') {
            $headers[] = 'Stripe-Version: ' . $this->apiVersion;
        }

        $ch = curl_init();
        if (strtoupper($method) === 'GET') {
            if ($params) {
                $url .= '?' . http_build_query($params);
            }
        } else {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }
        if ($status >= 400) {
            error_log('[WT\Plugins\Stripe] HTTP ' . $status . ': ' . ($decoded['error']['message'] ?? $response));
            return null;
        }

        return $decoded;
    }
}
