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

trait Wt_Helper
{
    public static function Wt_GetIP(): string|false
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return false;
    }

    public function Wt_ReDir(?string $path = null, ?int $timer = null): void
    {
        session_write_close();

        if (empty($path)) {
            $path = $_SESSION['url'] ?? '/';
        }

        $parsedUrl  = parse_url($path);
        $parsedHost = $parsedUrl['host'] ?? null;
        $serverHost = $_SERVER['HTTP_HOST'] ?? '';

        if ($parsedHost && $parsedHost !== $serverHost) {
            $path = '/';
        }

        if ($timer) {
            header('refresh: ' . $timer . ';url=' . $path);
        } else {
            header('Location: ' . $path);
            exit;
        }
    }

    public function Wt_SecMsg(string $msg): string
    {
        $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        return '<div class="alert alert-success alert-dismissible animated fadeIn">'
            . '<i class="fa fa-check-circle" aria-hidden="true"></i> '
            . $msg . '</div>';
    }

    public function Wt_WrMsg(string $msg): string
    {
        $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        return '<div class="alert alert-warning alert-dismissible animated fadeIn">'
            . '<i class="fa fa-info-circle" aria-hidden="true"></i> '
            . $msg . '</div>';
    }

    public function Wt_ErMsg(string $msg): string
    {
        $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        return '<div class="alert alert-danger alert-dismissible animated fadeIn">'
            . '<i class="fa fa-minus-circle" aria-hidden="true"></i> '
            . $msg . '</div>';
    }

    public static function Wt_GlbMsg(?string $msg = null): void
    {
        if (isset($_SESSION['captcha'])) {unset($_SESSION['captcha']);}

        if ($msg) {
            $msg     = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
            $imgPath = '/pub_wt/imgs/admin/wt.png';

            echo '
            <div style="display:flex; justify-content:center; margin-top:20%">
                <div style="color:gray; text-align:center; background-color:whitesmoke;
                            padding:30px; width:50%; border:gray dotted 1px; border-radius:20px">
                    <img src="' . $imgPath . '" width="130px">
                    <h3>WT Framework</h3>
                    <h5>' . $msg . '</h5>
                </div>
            </div>';
            exit;
        }
    }

    public function Wt_GenCsrf(): string
    {
        // Stable per-session token (synchronizer pattern): generated once, then reused
        // for the session so multiple tabs / back-button navigation keep a valid token.
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function Wt_ChkCsrf(?string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
    }

    /**
     * Builds a ZATCA-compliant (Phase 1) Base64 TLV QR payload for invoices/quotes.
     * Shared by admin and client print views.
     */
    public function Wt_ZatcaQr(string $sellerName, string $vatNumber, string $invoiceDate, float $total, float $vatAmount): string
    {
        $tlv = function (int $tag, string $value): string {
            $v = mb_convert_encoding($value, 'UTF-8');
            return chr($tag) . chr(strlen($v)) . $v;
        };
        return base64_encode(
            $tlv(1, $sellerName)
            . $tlv(2, $vatNumber)
            . $tlv(3, $invoiceDate)
            . $tlv(4, number_format($total, 2, '.', ''))
            . $tlv(5, number_format($vatAmount, 2, '.', ''))
        );
    }

    public static function Wt_Flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    public static function Wt_GetFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $f;
        }
        return null;
    }
}