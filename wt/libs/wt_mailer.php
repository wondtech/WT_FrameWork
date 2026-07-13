<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @package    : WT FrameWork (2.0) — mail transport
 *
 *  Reliable mail helper. Uses SMTP when SMTP_HOST is configured in .env
 *  (recommended for production — PHP mail() often silently fails or lands
 *  in spam on hosts without a local MTA), otherwise falls back to a
 *  hardened mail() call. Never suppresses the result: returns bool and
 *  logs failures, so callers can react instead of assuming success.
 *
 *  .env keys (all optional; absence = mail() fallback):
 *    SMTP_HOST, SMTP_PORT (587), SMTP_USER, SMTP_PASS,
 *    SMTP_SECURE (tls | ssl | ''), SMTP_FROM, SMTP_FROM_NAME
 ************************************************************************/

namespace WT\LIBS;

final class Wt_Mailer
{
    /** Send an email. Returns true on accepted-for-delivery, false otherwise. */
    public static function send(
        string $to,
        string $subject,
        string $body,
        bool $isHtml = false,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[Wt_Mailer] invalid recipient');
            return false;
        }
        // Header-injection guard: strip CR/LF from all header-bound values.
        $strip     = static fn(string $s): string => str_replace(["\r", "\n"], '', $s);
        $subject   = $strip($subject);
        $fromEmail = $strip($fromEmail ?: ($_ENV['SMTP_FROM'] ?? $_ENV['MAIL_SEND_EMAIL'] ?? 'noreply@localhost'));
        $fromName  = $strip($fromName  ?: ($_ENV['SMTP_FROM_NAME'] ?? $_ENV['MAIL_APP_NAME'] ?? 'WT'));
        $type      = $isHtml ? 'text/html' : 'text/plain';

        $host = trim((string)($_ENV['SMTP_HOST'] ?? ''));
        $ok = $host !== ''
            ? self::smtp($host, $to, $subject, $body, $type, $fromEmail, $fromName)
            : self::phpMail($to, $subject, $body, $type, $fromEmail, $fromName);

        if (!$ok) error_log('[Wt_Mailer] send failed to ' . substr($to, 0, 3) . '***');
        return $ok;
    }

    /* Hardened mail() — no @ suppression, result captured by the caller. */
    private static function phpMail(string $to, string $subject, string $body, string $type, string $fromEmail, string $fromName): bool
    {
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: ' . $type . '; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion(),
        ]);
        return mail($to, $subject, $body, $headers);
    }

    /* Minimal SMTP client (ESMTP + AUTH LOGIN + optional STARTTLS/SSL). */
    private static function smtp(string $host, string $to, string $subject, string $body, string $type, string $fromEmail, string $fromName): bool
    {
        $port    = (int)($_ENV['SMTP_PORT'] ?? 587);
        $user    = (string)($_ENV['SMTP_USER'] ?? '');
        $pass    = (string)($_ENV['SMTP_PASS'] ?? '');
        $secure  = strtolower((string)($_ENV['SMTP_SECURE'] ?? ''));   // '', 'tls', 'ssl'
        $timeout = 15;

        $endpoint = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $fp = @stream_socket_client($endpoint, $errno, $errstr, $timeout);
        if (!$fp) { error_log("[Wt_Mailer] SMTP connect failed: $errstr ($errno)"); return false; }
        stream_set_timeout($fp, $timeout);

        $read = static function () use ($fp): string {
            $data = '';
            while (($line = fgets($fp, 515)) !== false) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;   // last line of a multiline reply
            }
            return $data;
        };
        $cmd = static function (string $c) use ($fp): void { fwrite($fp, $c . "\r\n"); };
        $is  = static fn(string $resp, string $code): bool => str_starts_with($resp, $code);
        $bail = static function () use ($fp): bool { fclose($fp); return false; };

        if (!$is($read(), '220')) return $bail();
        $ehlo = 'EHLO ' . (gethostname() ?: 'localhost');
        $cmd($ehlo); if (!$is($read(), '250')) return $bail();

        if ($secure === 'tls') {
            $cmd('STARTTLS'); if (!$is($read(), '220')) return $bail();
            $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            if (!@stream_socket_enable_crypto($fp, true, $crypto)) return $bail();
            $cmd($ehlo); if (!$is($read(), '250')) return $bail();
        }
        if ($user !== '') {
            $cmd('AUTH LOGIN');            if (!$is($read(), '334')) return $bail();
            $cmd(base64_encode($user));    if (!$is($read(), '334')) return $bail();
            $cmd(base64_encode($pass));    if (!$is($read(), '235')) return $bail();
        }
        $cmd('MAIL FROM:<' . $fromEmail . '>'); if (!$is($read(), '250')) return $bail();
        $rcpt = (function () use ($cmd, $read, $is, $to) { $cmd('RCPT TO:<' . $to . '>'); $r = $read(); return $is($r, '250') || $is($r, '251'); })();
        if (!$rcpt) return $bail();
        $cmd('DATA'); if (!$is($read(), '354')) return $bail();

        $enc     = static fn(string $s): string => '=?UTF-8?B?' . base64_encode($s) . '?=';
        $headers = 'Date: ' . date('r') . "\r\n"
                 . 'From: ' . $enc($fromName) . ' <' . $fromEmail . ">\r\n"
                 . 'To: <' . $to . ">\r\n"
                 . 'Subject: ' . $enc($subject) . "\r\n"
                 . 'MIME-Version: 1.0' . "\r\n"
                 . 'Content-Type: ' . $type . '; charset=UTF-8' . "\r\n"
                 . 'Content-Transfer-Encoding: 8bit' . "\r\n";
        // Normalise line endings + dot-stuff lines beginning with "." (RFC 5321).
        $bodyOut = preg_replace('/^\./m', '..', str_replace(["\r\n", "\r", "\n"], "\r\n", $body));
        fwrite($fp, $headers . "\r\n" . $bodyOut . "\r\n.\r\n");
        if (!$is($read(), '250')) return $bail();

        $cmd('QUIT'); fclose($fp);
        return true;
    }
}
