<?php
/***********************************************************************
# *          @Project    : WT FrameWork
# *          @version    : 2.0
# *          @author     : Mogbil Sourketti info[@]wondtech.com
# *          @copyright  : 2026 WondTech for Integrated Digital Solutions
# *          @package    : WT FrameWork (2.0) — Admin shell
# *
# *  Abstract shell shared by every admin section controller: staff guard,
# *  one-shot CSRF gate on POST, per-action capability gate, and tpl() which
# *  injects the sidebar / topbar context. Section controllers only declare
# *  their actions and an $actionCaps map.
# ************************************************************************/

namespace WT\Controllers\Admin;

use WT\LIBS\Wt_Controller;
use WT\LIBS\Wt_Auth;
use WT\LIBS\Wt_Sec;
use WT\LIBS\Wt_Helper;
use WT\Models\Users_Model;

abstract class Admin_Base_Controller extends Wt_Controller
{
    use Wt_Auth, Wt_Sec, Wt_Helper;

    /** action(lowercase) => capability. Actions absent here need no extra cap. */
    protected array $actionCaps = [];

    public function __construct()
    {
        // Staff-only. Non-staff (or logged-out) are bounced to the sign-in page.
        $this->requireAnyRole(Users_Model::STAFF_ROLES);

        // Central CSRF gate: every POST must carry a valid token.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && !$this->Wt_ChkCsrf($_POST['csrf'] ?? null)) {
            self::Wt_Flash('error', $this->L('انتهت الجلسة، أعد المحاولة', 'Session expired, please try again'));
            $this->Wt_ReDir($_SERVER['HTTP_REFERER'] ?? '/admin/index');
        }
    }

    public function setAction(string $actionName): void
    {
        parent::setAction($actionName);
        $cap = $this->actionCaps[strtolower($actionName)] ?? null;
        if ($cap !== null && !$this->can($cap)) {
            self::Wt_Flash('error', $this->L('ليس لديك صلاحية لهذا القسم', 'You do not have permission for this section'));
            $this->Wt_ReDir('/admin/index');
        }
    }

    protected function isAr(): bool { return ($_SESSION['lang'] ?? 'EN') === 'AR'; }
    protected function L(string $ar, string $en): string { return $this->isAr() ? $ar : $en; }

    /** Render an admin template with the shared shell context. */
    protected function tpl(string $file, array $vars = []): void
    {
        $tpl = $this->view('admin');
        $tpl->assign('authUser', $this->authUser());
        $tpl->assign('csrf',     $this->Wt_GenCsrf());
        $tpl->assign('flash',    self::Wt_GetFlash());
        $tpl->assign('isAr',     $this->isAr());
        $tpl->assign('caps', [
            'users' => $this->can('users'),
        ]);
        $tpl->assign('counts', $this->sidebarCounts());
        $tpl->assign('active', $vars['active'] ?? '');
        foreach ($vars as $k => $v) $tpl->assign($k, $v);
        $tpl->view($file);
    }

    /** Badge counts shown in the sidebar. Override per app. */
    protected function sidebarCounts(): array
    {
        return [];
    }

    /** Round-trip a Y-m-d string; return null when invalid/empty. */
    protected function validDate(?string $d): ?string
    {
        $d = trim((string)$d);
        if ($d === '') return null;
        $dt = \DateTime::createFromFormat('Y-m-d', $d);
        return ($dt && $dt->format('Y-m-d') === $d) ? $d : null;
    }

}
