<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2026 WondTech for Integrated Digital Solutions
# *          @package    : WT FrameWork (2.0) — Admin dashboard
# ************************************************************************/

namespace WT\Controllers;

use WT\Controllers\Admin\Admin_Base_Controller;
use WT\Models\Users_Model;

class Admin_Controller extends Admin_Base_Controller
{
    protected array $actionCaps = [
        'index'       => 'dashboard',
        'profile'     => 'profile',
        'profilesave' => 'profile',
    ];

    /* ───────────────────────────── dashboard ───────────────────────────── */

    public function Index_Action(): void
    {
        // Example dashboard metrics (adapt the WHERE clauses to your schema).
        $usersTotal  = Users_Model::wt_countData("WHERE role = 'user'");
        $usersActive = Users_Model::wt_countData("WHERE is_active = 1");
        $staff       = Users_Model::wt_countData("WHERE role IN ('admin','moderator')");
        $newToday    = Users_Model::wt_countData("WHERE DATE(created_at) = CURDATE()");

        $recentUsers = Users_Model::wt_getData("ORDER BY id DESC", [], 8, 1);

        $this->tpl('index.tpl', [
            'active'      => 'dashboard',
            'subTitle'    => $this->L('لوحة المعلومات', 'Dashboard'),
            'st'          => compact('usersTotal', 'usersActive', 'staff', 'newToday'),
            'recentUsers' => $recentUsers,
        ]);
    }

    /* ───────────────────────────── profile ───────────────────────────── */

    public function Profile_Action(): void
    {
        $u = Users_Model::wt_getByPkey((int)$this->authUser()['id']);
        $this->tpl('profile.tpl', [
            'active'   => 'profile',
            'subTitle' => $this->L('ملفي الشخصي', 'My Profile'),
            'u'        => $u,
        ]);
    }

    public function ProfileSave_Action(): void
    {
        $u = Users_Model::wt_getByPkey((int)$this->authUser()['id']);
        if (!$u) $this->Wt_ReDir('/admin/profile');

        $name = $this->Wt_SecInput((string)($_POST['name'] ?? ''), 'str');
        if ($name !== '') $u->name = $name;

        // Optional avatar — stored in-DB as compressed base64 (Wt_PostImg).
        if (isset($_FILES['avatar']) && ($_FILES['avatar']['size'] ?? 0) > 0) {
            $b64 = $this->Wt_PostImg($_FILES['avatar']);
            if ($b64 !== false) $u->avatar = $b64;
        }

        // Optional password change.
        $new = (string)($_POST['new_password'] ?? '');
        if ($new !== '') {
            $cur = (string)($_POST['current_password'] ?? '');
            if (!password_verify($cur, $u->password)) {
                self::Wt_Flash('error', $this->L('كلمة المرور الحالية غير صحيحة', 'Current password is incorrect'));
                $this->Wt_ReDir('/admin/profile');
            }
            if (strlen($new) < 8) {
                self::Wt_Flash('error', $this->L('كلمة المرور الجديدة 8 أحرف على الأقل', 'New password must be at least 8 characters'));
                $this->Wt_ReDir('/admin/profile');
            }
            $u->password = password_hash($new, PASSWORD_BCRYPT);
        }

        $u->touch();
        $u->wt_save();
        // Refresh cached session identity (name shown in the top bar; avatar is a flag).
        $_SESSION['auth_name']   = $u->name;
        $_SESSION['auth_avatar'] = !empty($u->avatar);

        self::Wt_Flash('success', $this->L('تم حفظ التغييرات', 'Changes saved'));
        $this->Wt_ReDir('/admin/profile');
    }

    /* ───────────────────────────── logout ───────────────────────────── */

    public function Logout_Action(): void
    {
        $this->logoutUser();
        self::Wt_Flash('success', $this->L('تم تسجيل خروجك', 'You have been signed out'));
        $this->Wt_ReDir('/auth/login');
    }
}
