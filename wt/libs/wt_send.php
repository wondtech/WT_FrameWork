<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Improved
 ************************************************************************/

namespace WT\LIBS;

trait Wt_Send
{
    private string $appName;
    private string $gEmail;
    private string $sEmail;

    private function initMailConfig(): void
    {
        $this->appName = $_ENV['MAIL_APP_NAME']  ?? '';
        $this->gEmail  = $_ENV['MAIL_GET_EMAIL'] ?? '';
        $this->sEmail  = $_ENV['MAIL_SEND_EMAIL'] ?? '';
    }

    private function buildHeaders(string $fromName, string $fromEmail): string
    {
        $fromName  = str_replace(["\r", "\n"], '', $fromName);
        $fromEmail = str_replace(["\r", "\n"], '', $fromEmail);
        $from      = $fromName . '<' . $fromEmail . '>';

        return implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: '     . $from,
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . phpversion(),
        ]);
    }

    public function Wt_GetEmail(
        string $senderName,
        string $senderEmail,
        string $subject,
        string $message
    ): bool {
        $this->initMailConfig();

        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $subject = str_replace(["\r", "\n"], '', $subject);

        $headers = $this->buildHeaders($senderName, $senderEmail);

        return mail($this->gEmail, $subject, $message, $headers);
    }

    public function Wt_SendEmail(
        string $to,
        string $subject,
        string $message,
        ?int $toUserId = null
    ): bool {
        $this->initMailConfig();

        $sentBy = $_SESSION['auth_id'] ?? null;

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->logEmail($to, $subject, $message, 'failed', 'invalid recipient', $toUserId, $sentBy);
            return false;
        }

        $subject = str_replace(["\r", "\n"], '', $subject);
        $headers = $this->buildHeaders($this->appName, $this->sEmail);
        $ok      = mail($to, $subject, $message, $headers);

        $this->logEmail($to, $subject, $message, $ok ? 'sent' : 'failed', $ok ? null : 'mail() returned false', $toUserId, $sentBy);
        return $ok;
    }

    /**
     * Hook: record a sent / failed email. No-op by default — override in your
     * app (or a subclass) to persist to your own log or model.
     */
    protected function logEmail(
        string $to,
        string $subject,
        string $message,
        string $status,
        ?string $error,
        ?int $toUserId,
        ?int $sentBy
    ): void {
        // no-op by default; override to persist an email-log entry.
    }

}