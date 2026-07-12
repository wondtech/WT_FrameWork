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

trait Wt_Sec
{
    private string $encKey;

    private function initEncKey(): void
    {
        $this->encKey = $_ENV['APP_SECRET_KEY'] ?? '';
    }

    public function Wt_SecInt(mixed $get): int
    {
        return intval($get);
    }

    public function Wt_SecStr(mixed $get): string
    {
        return strval($get);
    }

    private function Wt_cleanString(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9-أ-ي٠-٩@._:\/ &(),!?+-]+/u', '', $string) ?? '';
    }

    private function Wt_cleanInt(string $number): string
    {
        return str_replace(
            ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9'],
            $number
        );
    }

    public function Wt_SecInput(string $input, string $opt): mixed
    {
        $input = trim($input);
        $input = $this->Wt_cleanString($input);

        return match($opt) {
            'str'   => strip_tags($input),
            'int'   => filter_var($this->Wt_cleanInt($input), FILTER_SANITIZE_NUMBER_INT),
            'flo'   => filter_var($this->Wt_cleanInt($input), FILTER_SANITIZE_NUMBER_FLOAT),
            'email' => filter_var($input, FILTER_VALIDATE_EMAIL),
            default => false,
        };
    }

    public function  Wt_CrtCap(): int
    {
        $_SESSION['captcha'] = random_int(111111, 999999);
        return $_SESSION['captcha'];
    }

    public function Wt_DrwCap(): string
    {
        ob_start();
        $captchaCode = $_SESSION['captcha'] ?? $this->Wt_CrtCap();
        $captchaImg  = imagecreatetruecolor(75, 37);
        imagealphablending($captchaImg, false);
        imagesavealpha($captchaImg, true);
        $transparent = imagecolorallocatealpha($captchaImg, 0, 0, 0, 127);
        imagefill($captchaImg, 0, 0, $transparent);
        imagealphablending($captchaImg, true);
        $captchaTxt  = imagecolorallocate($captchaImg, 255, 255, 255);
        imagestring($captchaImg, 5, 10, 10, (string)$captchaCode, $captchaTxt);
        imagepng($captchaImg);
        $img = base64_encode(ob_get_contents());
        ob_end_clean();
        return $img;
    }

    private function Wt_Compress(string $source, string $destination, int $quality): string|false
    {
        $info = getimagesize($source);
        if (!$info) return false;
        $image = match($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/gif'  => imagecreatefromgif($source),
            'image/png'  => imagecreatefrompng($source),
            default      => false,
        };
        if (!$image) return false;
        imagejpeg($image, $destination, $quality);
        return $destination;
    }

    public function Wt_PostImg(array $img): string|false
    {
        if (!isset($img['tmp_name']) || !is_uploaded_file($img['tmp_name'])) {
            return false;
        }

        if ($img['size'] > 2097152) {
            return false;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $img['tmp_name']);
        if (!in_array($mimeType, $allowedTypes, true)) {
            return false;
        }

        $info = getimagesize($img['tmp_name']);
        if (!$info || $info[0] < 100 || $info[1] < 100) {
            return false;
        }

        $compressed = $this->Wt_Compress($img['tmp_name'], $img['tmp_name'], 50);
        if (!$compressed) return false;

        return base64_encode(file_get_contents($compressed));
    }

    public function Wt_Encode(string $value): string|false
    {
        if (empty($value)) return false;
        $this->initEncKey();
        $ivLength  = openssl_cipher_iv_length('aes-256-cbc');
        $iv        = random_bytes($ivLength);
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $this->encKey, 0, $iv);
        if ($encrypted === false) return false;
        return base64_encode($iv . $encrypted);
    }

    public function Wt_Decode(string $value): string|false
    {
        if (empty($value)) return false;
        $this->initEncKey();
        $data      = base64_decode($value, true);
        if ($data === false) return false;
        $ivLength  = openssl_cipher_iv_length('aes-256-cbc');
        $iv        = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->encKey, 0, $iv);
    }
}