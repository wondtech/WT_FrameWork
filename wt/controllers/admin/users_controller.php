<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2026 WondTech for Integrated Digital Solutions
# *          @package    : WT FrameWork (2.0) — Admin: users
# ************************************************************************/

namespace WT\Controllers\Admin;

use WT\LIBS\Wt_DB;
use WT\Models\Users_Model;

class Users_Controller extends Admin_Base_Controller
{
    private const PER_PAGE = 15;
    private const ROLES    = ['user', 'moderator', 'admin'];

    protected array $actionCaps = [
        'index'  => 'users',
        'edit'   => 'users',
        'save'   => 'users',
        'toggle' => 'users',
        'delete' => 'users',
    ];

    public function Index_Action(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $q    = trim((string)($_GET['q'] ?? ''));
        $role = (string)($_GET['role'] ?? '');

        $where = 'WHERE 1=1';
        $bind  = [];
        if ($q !== '') {
            $where .= ' AND (name LIKE :q1 OR email LIKE :q2 OR mobile LIKE :q3)';
            $bind[':q1'] = [\PDO::PARAM_STR, "%$q%"];
            $bind[':q2'] = [\PDO::PARAM_STR, "%$q%"];
            $bind[':q3'] = [\PDO::PARAM_STR, "%$q%"];
        }
        if (in_array($role, self::ROLES, true)) {
            $where .= ' AND role = :role';
            $bind[':role'] = [\PDO::PARAM_STR, $role];
        }
        $total = Users_Model::wt_countData($where, $bind);
        $users = Users_Model::wt_getData($where . ' ORDER BY id DESC', $bind, self::PER_PAGE, $page);

        // ad counts per listed user (single aggregate)
        $adCounts = [];
        if ($users) {
            $ids = implode(',', array_map(fn($u) => (int)$u->id, $users));
            $rows = Wt_DB::getInstance()->getPDO()->query(
                "SELECT user_id, COUNT(*) c FROM ads WHERE status!='deleted' AND user_id IN ($ids) GROUP BY user_id"
            )->fetchAll();
            foreach ($rows as $r) $adCounts[(int)$r['user_id']] = (int)$r['c'];
        }

        $this->tpl('users/list.tpl', [
            'active'   => 'users',
            'subTitle' => $this->L('المستخدمون', 'Users'),
            'users'    => $users,
            'adCounts' => $adCounts,
            'q'        => $q,
            'role'     => $role,
            'page'     => $page,
            'pages'    => (int)ceil($total / self::PER_PAGE),
            'total'    => $total,
            'meId'     => (int)$this->authUser()['id'],
        ]);
    }

    public function Edit_Action(): void
    {
        $id = (int)($this->params[0] ?? 0);
        $u  = $id > 0 ? Users_Model::wt_getByPkey($id) : new Users_Model();
        if ($id > 0 && !$u) {
            self::Wt_Flash('error', $this->L('المستخدم غير موجود', 'User not found'));
            $this->Wt_ReDir('/admin/users/index');
        }
        $this->tpl('users/edit.tpl', [
            'active'   => 'users',
            'subTitle' => $id > 0 ? $this->L('تعديل مستخدم', 'Edit user') : $this->L('إضافة مستخدم', 'Add user'),
            'u'        => $u,
            'isNew'    => $id === 0,
            'roles'    => self::ROLES,
        ]);
    }

    public function Save_Action(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $u  = $id > 0 ? Users_Model::wt_getByPkey($id) : new Users_Model();
        if ($id > 0 && !$u) { self::Wt_Flash('error', $this->L('المستخدم غير موجود', 'User not found')); $this->Wt_ReDir('/admin/users/index'); }

        $name   = $this->Wt_SecInput((string)($_POST['name'] ?? ''), 'str');
        $email  = $this->Wt_SecInput((string)($_POST['email'] ?? ''), 'email');
        $rawMob = (string)($_POST['mobile'] ?? '');
        $mobile = Users_Model::normalizeMobile($rawMob);
        $role   = in_array($_POST['role'] ?? '', self::ROLES, true) ? $_POST['role'] : 'user';
        $active = isset($_POST['is_active']) ? 1 : 0;
        $pass   = (string)($_POST['password'] ?? '');
        $back   = $id > 0 ? "/admin/users/edit/$id" : '/admin/users/edit';

        if ($name === '')                              { self::Wt_Flash('error', $this->L('الاسم مطلوب', 'Name is required')); $this->Wt_ReDir($back); }
        if ($email === false)                          { self::Wt_Flash('error', $this->L('البريد الإلكتروني غير صالح', 'Invalid email')); $this->Wt_ReDir($back); }
        if (Users_Model::emailExists($email, $id))     { self::Wt_Flash('error', $this->L('البريد مستخدم مسبقاً', 'Email already in use')); $this->Wt_ReDir($back); }
        if ($rawMob !== '' && !Users_Model::validMobile($rawMob)) { self::Wt_Flash('error', $this->L('رقم الجوال غير صالح', 'Invalid mobile number')); $this->Wt_ReDir($back); }
        if ($mobile !== '' && Users_Model::mobileExists($mobile, $id)) { self::Wt_Flash('error', $this->L('رقم الجوال مستخدم مسبقاً', 'Mobile already in use')); $this->Wt_ReDir($back); }

        if ($id === 0 && strlen($pass) < 8) { self::Wt_Flash('error', $this->L('كلمة المرور 8 أحرف على الأقل', 'Password must be at least 8 characters')); $this->Wt_ReDir($back); }

        // Never let an admin lock themselves out (demote / deactivate own account).
        if ($id === (int)$this->authUser()['id']) { $role = 'admin'; $active = 1; }

        $u->name      = $name;
        $u->email     = $email;
        $u->mobile    = $mobile ?: null;
        $u->role      = $role;
        $u->is_active = $active;
        if ($pass !== '') {
            if (strlen($pass) < 8) { self::Wt_Flash('error', $this->L('كلمة المرور 8 أحرف على الأقل', 'Password must be at least 8 characters')); $this->Wt_ReDir($back); }
            $u->password = password_hash($pass, PASSWORD_BCRYPT);
        }
        if ($id > 0) $u->touch();
        $u->wt_save();

        self::Wt_Flash('success', $this->L('تم حفظ المستخدم', 'User saved'));
        $this->Wt_ReDir('/admin/users/index');
    }

    public function Toggle_Action(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $u  = $id > 0 ? Users_Model::wt_getByPkey($id) : false;
        if (!$u) { self::Wt_Flash('error', $this->L('المستخدم غير موجود', 'User not found')); $this->Wt_ReDir('/admin/users/index'); }
        if ((int)$u->id === (int)$this->authUser()['id']) {
            self::Wt_Flash('error', $this->L('لا يمكنك حظر حسابك', 'You cannot ban your own account'));
            $this->Wt_ReDir('/admin/users/index');
        }
        $u->is_active = (int)$u->is_active === 1 ? 0 : 1;
        $u->touch();
        $u->wt_save();
        self::Wt_Flash('success', $u->is_active ? $this->L('تم تفعيل المستخدم', 'User activated') : $this->L('تم حظر المستخدم', 'User banned'));
        $this->Wt_ReDir('/admin/users/index');
    }

    public function Delete_Action(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $u  = $id > 0 ? Users_Model::wt_getByPkey($id) : false;
        if (!$u) { self::Wt_Flash('error', $this->L('المستخدم غير موجود', 'User not found')); $this->Wt_ReDir('/admin/users/index'); }
        if ((int)$u->id === (int)$this->authUser()['id']) {
            self::Wt_Flash('error', $this->L('لا يمكنك حذف حسابك', 'You cannot delete your own account'));
            $this->Wt_ReDir('/admin/users/index');
        }
        $u->wt_delete();   // FK cascade clears their ads/messages/notifications
        self::Wt_Flash('success', $this->L('تم حذف المستخدم', 'User deleted'));
        $this->Wt_ReDir('/admin/users/index');
    }
}
