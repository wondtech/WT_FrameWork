<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Official WhatsApp Cloud API
 ************************************************************************/

namespace WT\Plugins;

class Whatsapp
{
    private string $base;
    private string $phoneId;
    private string $token;
    private string $businessId;
    private string $verifyToken;

    public function __construct()
    {
        $version           = $_ENV['WHATSAPP_API_VERSION'] ?? 'v21.0';
        $this->base        = 'https://graph.facebook.com/' . trim($version, '/');
        $this->phoneId     = $_ENV['WHATSAPP_PHONE_ID']     ?? '';
        $this->token       = $_ENV['WHATSAPP_TOKEN']        ?? '';
        $this->businessId  = $_ENV['WHATSAPP_BUSINESS_ID']  ?? '';
        $this->verifyToken = $_ENV['APP_SECRET_KEY'] ?? '';
    }

    // ── Send API ──────────────────────────────────────────────────────────

    /** Plain text message (only valid inside the 24h customer-service window). */
    public function sendText(string $phone, string $text, bool $previewUrl = false, ?int $toUserId = null): ?array
    {
        $result = $this->sendMessage([
            'type' => 'text',
            'text' => ['preview_url' => $previewUrl, 'body' => $text],
        ], $phone);

        $this->log($phone, $text, $result, $toUserId);
        return $result;
    }

    /**
     * Template message (required to start a conversation outside the 24h window).
     * $components lets you pass body/header variables; leave empty for static templates.
     */
    public function sendTemplate(string $phone, string $template, string $lang = 'en_US', array $components = [], ?int $toUserId = null): ?array
    {
        $payload = [
            'type'     => 'template',
            'template' => [
                'name'     => $template,
                'language' => ['code' => $lang],
            ],
        ];
        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        $result = $this->sendMessage($payload, $phone);
        $this->log($phone, 'template:' . $template, $result, $toUserId);
        return $result;
    }

    /** Image by public URL (or pass a media id via sendMediaId). */
    public function sendImage(string $phone, string $imageUrl, string $caption = ''): ?array
    {
        return $this->sendMessage([
            'type'  => 'image',
            'image' => array_filter(['link' => $imageUrl, 'caption' => $caption]),
        ], $phone);
    }

    /** Document by public URL. */
    public function sendDocument(string $phone, string $fileUrl, string $caption = '', string $filename = ''): ?array
    {
        return $this->sendMessage([
            'type'     => 'document',
            'document' => array_filter(['link' => $fileUrl, 'caption' => $caption, 'filename' => $filename]),
        ], $phone);
    }

    /**
     * Upload a local file to WhatsApp and return its media id (valid ~30 days), or null.
     * Use the returned id with sendMediaId() to send within the 24h window.
     */
    public function uploadMedia(string $localPath, string $mime): ?string
    {
        if ($this->token === '' || !is_file($localPath)) {
            return null;
        }
        $ch = curl_init("{$this->base}/{$this->phoneId}/media");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->token],
            CURLOPT_POSTFIELDS     => [
                'messaging_product' => 'whatsapp',
                'type'              => $mime,
                'file'              => new \CURLFile($localPath, $mime, basename($localPath)),
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('[Whatsapp] media upload failed: ' . $response);
            return null;
        }
        $decoded = json_decode($response, true);
        return $decoded['id'] ?? null;
    }

    /**
     * Send an already-uploaded media id. $type = image|document|audio|video.
     * Only valid inside the 24h customer-service window (free-form).
     */
    public function sendMediaId(string $phone, string $mediaId, string $type, string $caption = '', string $filename = ''): ?array
    {
        $media = array_filter(['id' => $mediaId, 'caption' => $caption, 'filename' => $filename]);
        // audio/video don't take a filename; caption only valid on image/document/video.
        return $this->sendMessage(['type' => $type, $type => $media], $phone);
    }

    /**
     * Interactive reply buttons (max 3). $buttons = [['id'=>'yes','title'=>'Yes'], ...].
     */
    public function sendButtons(string $phone, string $text, array $buttons, string $footer = ''): ?array
    {
        $rows = [];
        foreach (array_slice($buttons, 0, 3) as $b) {
            $rows[] = ['type' => 'reply', 'reply' => [
                'id'    => (string)($b['id']    ?? $b['title'] ?? ''),
                'title' => (string)($b['title'] ?? $b['id']    ?? ''),
            ]];
        }

        $interactive = [
            'type'   => 'button',
            'body'   => ['text' => $text],
            'action' => ['buttons' => $rows],
        ];
        if ($footer !== '') {
            $interactive['footer'] = ['text' => $footer];
        }

        return $this->sendMessage(['type' => 'interactive', 'interactive' => $interactive], $phone);
    }

    /** Mark an inbound message as read (blue ticks). $messageId from the webhook payload. */
    public function markRead(string $messageId): ?array
    {
        return $this->request('POST', "/{$this->phoneId}/messages", [
            'messaging_product' => 'whatsapp',
            'status'            => 'read',
            'message_id'        => $messageId,
        ]);
    }

    // ── Transactional template helpers (approved templates) ──────────────

    /**
     * Invoice reminder via the approved `invoice_notification` template.
     * $amount should already include the currency (e.g. "1500.00 SAR").
     * Returns the API result, or null on failure — the caller may fall back to WAHA.
     */
    public function sendInvoiceReminder(string $phone, string $name, string $invoiceNo, string $amount, string $lang = 'en', ?int $toUserId = null): ?array
    {
        return $this->sendTemplate($phone, 'invoice_notification', $lang, [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $name],
                ['type' => 'text', 'text' => $invoiceNo],
                ['type' => 'text', 'text' => $amount],
            ]],
        ], $toUserId);
    }

    /** Quote reminder via the approved `quote_notification` template. */
    public function sendQuoteReminder(string $phone, string $name, string $quoteNo, string $amount, string $lang = 'en', ?int $toUserId = null): ?array
    {
        return $this->sendTemplate($phone, 'quote_notification', $lang, [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $name],
                ['type' => 'text', 'text' => $quoteNo],
                ['type' => 'text', 'text' => $amount],
            ]],
        ], $toUserId);
    }

    /**
     * Login OTP via the approved `login_code` AUTHENTICATION template (ar + en).
     * The code must be passed BOTH in the body AND in the COPY_CODE url button
     * (index 0); Meta rejects the send otherwise. Returns null on failure — caller
     * should fall back to email.
     */
    public function sendOtp(string $phone, string $code, string $lang = 'en', ?int $toUserId = null): ?array
    {
        return $this->sendTemplate($phone, 'login_code', $lang, [
            ['type' => 'body', 'parameters' => [['type' => 'text', 'text' => $code]]],
            ['type' => 'button', 'sub_type' => 'url', 'index' => '0',
             'parameters' => [['type' => 'text', 'text' => $code]]],
        ], $toUserId);
    }

    /** New-account welcome via `account_creation_confirmation_3` (body: name, email; static login button). */
    public function sendAccountWelcome(string $phone, string $name, string $email, string $lang = 'en', ?int $toUserId = null): ?array
    {
        return $this->sendTemplate($phone, 'account_creation_confirmation_3', $lang, [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $name],
                ['type' => 'text', 'text' => $email],
            ]],
        ], $toUserId);
    }

    /** Review request via `feedback_collection` (body: name, service date). */
    public function sendReviewRequest(string $phone, string $name, string $date, string $lang = 'en', ?int $toUserId = null): ?array
    {
        return $this->sendTemplate($phone, 'feedback_collection', $lang, [
            ['type' => 'body', 'parameters' => [
                ['type' => 'text', 'text' => $name],
                ['type' => 'text', 'text' => $date],
            ]],
        ], $toUserId);
    }

    // ── Account / number management ───────────────────────────────────────

    /**
     * Register the phone number on the Cloud API (one-time activation).
     * $pin = 6-digit two-step-verification PIN you choose.
     */
    public function register(string $pin): ?array
    {
        return $this->request('POST', "/{$this->phoneId}/register", [
            'messaging_product' => 'whatsapp',
            'pin'               => $pin,
        ]);
    }

    /** Deregister the number from the Cloud API. */
    public function deregister(): ?array
    {
        return $this->request('POST', "/{$this->phoneId}/deregister", []);
    }

    /** Phone number details (verified name, quality rating, status). */
    public function phoneInfo(): ?array
    {
        return $this->request('GET', "/{$this->phoneId}");
    }

    /** True when the number is connected and ready to send. */
    public function isConnected(): bool
    {
        $info = $this->phoneInfo();
        return isset($info['id']);
    }

    /** List message templates on the business account (needs WHATSAPP_BUSINESS_ID). */
    public function templates(): ?array
    {
        if ($this->businessId === '') {
            return null;
        }
        return $this->request('GET', "/{$this->businessId}/message_templates");
    }

    // ── Webhook helpers (static — used by the controller) ─────────────────

    /**
     * Verify Meta's GET handshake. Returns the challenge string to echo on success, or null.
     * PHP maps the dotted query keys (hub.mode) to underscores (hub_mode).
     */
    public function verifyWebhook(array $query): ?string
    {
        $mode      = $query['hub_mode']         ?? '';
        $token     = $query['hub_verify_token'] ?? '';
        $challenge = $query['hub_challenge']    ?? '';
        if ($mode === 'subscribe' && $this->verifyToken !== '' && hash_equals($this->verifyToken, (string)$token)) {
            return (string)$challenge;
        }
        return null;
    }

    /**
     * Flatten an inbound webhook body into simple message rows:
     *   [ ['from'=>'9665..', 'name'=>'..', 'type'=>'text', 'text'=>'..', 'id'=>'wamid..'], ... ]
     */
    public static function parseInbound(array $body): array
    {
        $out = [];
        foreach ($body['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value    = $change['value']    ?? [];
                $contacts = $value['contacts']  ?? [];
                $name     = $contacts[0]['profile']['name'] ?? '';
                foreach ($value['messages'] ?? [] as $m) {
                    $type = $m['type'] ?? '';
                    $text = $m['text']['body']
                        ?? ($m['button']['text']
                        ?? ($m['interactive']['button_reply']['title']
                        ?? ($m['interactive']['list_reply']['title'] ?? '')));
                    $out[] = [
                        'from' => $m['from'] ?? '',
                        'name' => $name,
                        'type' => $type,
                        'text' => $text,
                        'id'   => $m['id'] ?? '',
                    ];
                }
            }
        }
        return $out;
    }

    /**
     * Flatten outbound delivery-status callbacks into simple rows:
     *   [ ['id'=>'wamid..', 'status'=>'sent|delivered|read|failed', 'recipient'=>'9665..'], ... ]
     * The `id` is the same wamid returned by the send API (stored in wa_log.response).
     */
    public static function parseStatuses(array $body): array
    {
        $out = [];
        foreach ($body['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                foreach ($change['value']['statuses'] ?? [] as $s) {
                    $out[] = [
                        'id'        => $s['id'] ?? '',
                        'status'    => $s['status'] ?? '',
                        'recipient' => $s['recipient_id'] ?? '',
                    ];
                }
            }
        }
        return $out;
    }

    // ── Internals ─────────────────────────────────────────────────────────

    private function sendMessage(array $payload, string $phone): ?array
    {
        return $this->request('POST', "/{$this->phoneId}/messages", array_merge([
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $this->normalize($phone),
        ], $payload));
    }

    /** Cloud API wants raw international digits, no '+' / spaces / @c.us. */
    private function normalize(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }

    private function log(string $phone, string $text, ?array $result, ?int $toUserId): void
    {
        if (!class_exists('\WT\Models\Wa_Log_Model')) {
            return;
        }
        \WT\Models\Wa_Log_Model::record(
            $phone,
            $text,
            $result !== null ? 'sent' : 'failed',
            $result !== null ? json_encode($result, JSON_UNESCAPED_UNICODE) : null,
            $toUserId,
            $_SESSION['auth_id'] ?? null
        );
    }

    private function request(string $method, string $endpoint, array $data = []): ?array
    {
        if ($this->token === '') {
            return null;
        }

        $ch = curl_init($this->base . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_errno($ch);
        curl_close($ch);

        if ($curlErr || $response === false) {
            error_log('[Whatsapp] curl error: ' . $curlErr);
            return null;
        }

        $decoded = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $msg = $decoded['error']['message'] ?? ('HTTP ' . $httpCode);
            error_log('[Whatsapp] API error: ' . $msg);
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
