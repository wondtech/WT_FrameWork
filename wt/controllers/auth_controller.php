<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2026 WondTech for Integrated Digital Solutions
# *          @package    : WT FrameWork (2.0) — Admin auth
# *
# *  Web sign-in + password recovery for the Admin panel (staff only).
# *  Mobile app users authenticate through the token API instead.
# ************************************************************************/

namespace WT\Controllers;

use WT\LIBS\Wt_Controller;
use WT\LIBS\Wt_Auth;
use WT\LIBS\Wt_Sec;
use WT\LIBS\Wt_Helper;
use WT\Models\Users_Model;
use WT\Models\Otps_Model;
use WT\Models\Tokens_Model;

class Auth_Controller extends Wt_Controller {

    use Wt_Auth, Wt_Sec, Wt_Helper;

    private function isAr(): bool { return ($_SESSION['lang'] ?? 'EN') === 'AR'; }
    private function L(string $ar, string $en): string { return $this->isAr() ? $ar : $en; }
    private function debug(): bool { return filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN); }

    private function tpl(string $file, array $vars = []): void {
        $tpl = $this->view('auth');
        $tpl->assign('csrf', $this->Wt_GenCsrf());
        $tpl->assign('flash', self::Wt_GetFlash());
        foreach ($vars as $k => $v) $tpl->assign($k, $v);
        $tpl->view($file);
    }

    /** Fresh captcha image (base64 PNG) for a rendered form. */
    private function newCaptcha(): string {
        unset($_SESSION['captcha']);
        return $this->Wt_DrwCap();
    }

    /** Validate then consume the captcha (single use). */
    private function checkCaptcha(): bool {
        $ok = isset($_SESSION['captcha'])
            && (string)($_POST['captcha'] ?? '') === (string)$_SESSION['captcha'];
        unset($_SESSION['captcha']);
        return $ok;
    }

    public function Index_Action(): void { $this->Wt_ReDir('/auth/login'); }

    /** JSON endpoint used by the "refresh captcha" button. */
    public function Captcha_Action(): void {
        header('Content-Type: application/json; charset=UTF-8');
        unset($_SESSION['captcha']);
        echo json_encode(['img' => $this->Wt_DrwCap()]);
        exit;
    }

    /* ───────────────────────────── sign in ───────────────────────────── */

    public function Login_Action(): void {
        if (in_array($_SESSION['auth_role'] ?? '', Users_Model::STAFF_ROLES, true)) {
            $this->Wt_ReDir('/admin/index');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleLogin();
        }
        $this->tpl('login.tpl', ['captcha_img' => $this->newCaptcha()]);
    }

    private function handleLogin(): void {
        if (!$this->Wt_ChkCsrf($_POST['csrf'] ?? null)) {
            self::Wt_Flash('error', $this->L('انتهت الجلسة، أعد المحاولة', 'Session expired, please try again'));
            $this->Wt_ReDir('/auth/login');
        }
        if (!$this->checkCaptcha()) {
            self::Wt_Flash('error', $this->L('الرمز المرئي غير صحيح', 'Incorrect captcha code'));
            $this->Wt_ReDir('/auth/login');
        }
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = (string)($_POST['password'] ?? '');
        if ($identifier === '' || $password === '') {
            self::Wt_Flash('error', $this->L('أدخل بيانات الدخول وكلمة المرور', 'Enter your credentials and password'));
            $this->Wt_ReDir('/auth/login');
        }
        $u = Users_Model::findByLogin($identifier);
        if (!$u || !password_verify($password, $u->password)) {
            self::Wt_Flash('error', $this->L('بيانات الدخول غير صحيحة', 'Invalid credentials'));
            $this->Wt_ReDir('/auth/login');
        }
        if (!in_array($u->role, Users_Model::STAFF_ROLES, true)) {
            self::Wt_Flash('error', $this->L('ليس لديك صلاحية الوصول للوحة الإدارة', 'You do not have admin access'));
            $this->Wt_ReDir('/auth/login');
        }
        $this->loginUser($u);
        Users_Model::markLogin((int)$u->id);
        $this->Wt_ReDir('/admin/index');
    }

    public function Logout_Action(): void {
        $this->logoutUser();
        self::Wt_Flash('success', $this->L('تم تسجيل خروجك', 'You have been signed out'));
        $this->Wt_ReDir('/auth/login');
    }

    /* ───────────────────────────── forgot password ───────────────────────────── */

    public function Forgot_Action(): void {
        if (in_array($_SESSION['auth_role'] ?? '', Users_Model::STAFF_ROLES, true)) {
            $this->Wt_ReDir('/admin/index');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleForgot();
        }
        $this->tpl('forgot.tpl', ['captcha_img' => $this->newCaptcha(), 'old_email' => '']);
    }

    private function handleForgot(): void {
        if (!$this->Wt_ChkCsrf($_POST['csrf'] ?? null)) {
            self::Wt_Flash('error', $this->L('انتهت الجلسة، أعد المحاولة', 'Session expired, please try again'));
            $this->Wt_ReDir('/auth/forgot');
        }
        if (!$this->checkCaptcha()) {
            self::Wt_Flash('error', $this->L('الرمز المرئي غير صحيح', 'Incorrect captcha code'));
            $this->Wt_ReDir('/auth/forgot');
        }
        $email = $this->Wt_SecInput((string)($_POST['email'] ?? ''), 'email');
        if ($email === false) {
            self::Wt_Flash('error', $this->L('أدخل بريداً إلكترونياً صالحاً', 'Please enter a valid email address'));
            $this->Wt_ReDir('/auth/forgot');
        }

        $dev = '';
        $u = Users_Model::findByEmail($email);
        // Only staff accounts can recover through the admin panel.
        if ($u && in_array($u->role, Users_Model::STAFF_ROLES, true)) {
            $code = Otps_Model::issue((int)$u->id, $u->email, 'email', 'reset');
            $this->sendResetEmail($u->email, $code);
            if ($this->debug()) $dev = ' (dev code: ' . $code . ')';
        }

        $_SESSION['reset_email'] = $email;   // drives the reset page; no account enumeration
        self::Wt_Flash('success', $this->L('إذا كان الحساب موجوداً فقد أُرسل رمز إعادة التعيين', 'If the account exists, a reset code has been sent') . $dev);
        $this->Wt_ReDir('/auth/reset');
    }

    private function sendResetEmail(string $to, string $code): void {
        $isAr    = $this->isAr();
        $subject = $isAr ? 'رمز إعادة تعيين كلمة المرور - WT App' : 'WT App password reset code';
        $text    = $isAr
            ? "رمز إعادة تعيين كلمة المرور الخاص بك في WT App هو: $code — صالح لمدة 10 دقائق."
            : "Your WT App password reset code is: $code — valid for 10 minutes.";
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n"
                 . 'From: ' . ($_ENV['MAIL_SEND_EMAIL'] ?? 'noreply@example.com') . "\r\n";
        try { @mail($to, $subject, $text, $headers); } catch (\Throwable $e) {}
        // Never log the reset code — only that a reset email was sent.
        error_log('[Admin reset] email sent to ' . substr($to, 0, 3) . '***');
    }

    /* ───────────────────────────── reset password ───────────────────────────── */

    public function Reset_Action(): void {
        $email = $_SESSION['reset_email'] ?? '';
        if ($email === '') $this->Wt_ReDir('/auth/forgot');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleReset($email);
        }
        $this->tpl('reset.tpl');
    }

    private function handleReset(string $email): void {
        if (!$this->Wt_ChkCsrf($_POST['csrf'] ?? null)) {
            self::Wt_Flash('error', $this->L('انتهت الجلسة، أعد المحاولة', 'Session expired, please try again'));
            $this->Wt_ReDir('/auth/reset');
        }
        $code = trim($_POST['code'] ?? '');
        $pw   = (string)($_POST['password'] ?? '');
        $pw2  = (string)($_POST['password_confirm'] ?? '');

        if (strlen($pw) < 8) {
            self::Wt_Flash('error', $this->L('كلمة المرور 8 أحرف على الأقل', 'Password must be at least 8 characters'));
            $this->Wt_ReDir('/auth/reset');
        }
        if ($pw !== $pw2) {
            self::Wt_Flash('error', $this->L('كلمتا المرور غير متطابقتين', 'Passwords do not match'));
            $this->Wt_ReDir('/auth/reset');
        }
        $otp = Otps_Model::latestLive($email, 'reset');
        if (!$otp || (int)$otp->attempts >= Otps_Model::MAX_TRIES) {
            self::Wt_Flash('error', $this->L('انتهت صلاحية الرمز أو غير موجود', 'The code has expired or was not found'));
            $this->Wt_ReDir('/auth/reset');
        }
        if (!hash_equals($otp->code_hash, Otps_Model::hash($code))) {
            $otp->attempts = (int)$otp->attempts + 1;
            $otp->wt_save();
            self::Wt_Flash('error', $this->L('رمز التحقق غير صحيح', 'Invalid verification code'));
            $this->Wt_ReDir('/auth/reset');
        }
        $u = Users_Model::findByEmail($email);
        if (!$u) {
            self::Wt_Flash('error', $this->L('الحساب غير موجود', 'Account not found'));
            $this->Wt_ReDir('/auth/forgot');
        }
        $u->password = password_hash($pw, PASSWORD_BCRYPT);
        $u->touch();
        $u->wt_save();
        $otp->consumed = 1;
        $otp->wt_save();
        Tokens_Model::revokeAllFor((int)$u->id);
        unset($_SESSION['reset_email']);

        self::Wt_Flash('success', $this->L('تم تعيين كلمة المرور بنجاح — يمكنك الدخول الآن', 'Password reset successfully — you can sign in now'));
        $this->Wt_ReDir('/auth/login');
    }
}
