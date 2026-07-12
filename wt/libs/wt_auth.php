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

/**
 * Wt_Auth — session-based role / capability authorization for controllers.
 *
 * Mix this trait into a controller and gate actions with requireRole(),
 * requireAnyRole(), or can(). Capabilities are resolved from a role →
 * capabilities map: override roleCapabilities() in your app to declare your
 * own roles and permissions, and loginPath() to point at your sign-in route.
 *
 * The trait is framework-agnostic — it reads/writes only $_SESSION and never
 * touches your models. Populate the session with loginUser($user) after a
 * successful login (your $user object needs id, name, role and, optionally,
 * an avatar flag).
 */
trait Wt_Auth
{
    /** Where to redirect unauthenticated visitors. Override per app. */
    protected function loginPath(): string
    {
        return '/auth/login';
    }

    /**
     * Role → capabilities map. Override in your app to define access.
     * Grant a role every capability with the '*' wildcard.
     *
     * Example:
     *   return [
     *     'admin'     => ['*'],
     *     'moderator' => ['dashboard', 'posts', 'reports', 'profile'],
     *     'user'      => [],
     *   ];
     */
    protected function roleCapabilities(): array
    {
        return ['admin' => ['*']];
    }

    protected function requireRole(string $role): void
    {
        if (($_SESSION['auth_role'] ?? '') !== $role) {
            header('Location: ' . $this->loginPath());
            exit;
        }
    }

    protected function requireAnyRole(array $roles): void
    {
        if (!in_array($_SESSION['auth_role'] ?? '', $roles, true)) {
            header('Location: ' . $this->loginPath());
            exit;
        }
    }

    protected function can(string $cap): bool
    {
        $role  = $_SESSION['auth_role'] ?? '';
        $perms = $this->roleCapabilities()[$role] ?? [];
        if (in_array('*', $perms, true) || in_array($cap, $perms, true)) {
            return true;
        }
        // A *_view capability is also granted to holders of the base capability.
        if (str_ends_with($cap, '_view')) {
            return in_array(substr($cap, 0, -5), $perms, true);
        }
        return false;
    }

    /** The signed-in user as a plain array, or null. Reads the session only. */
    protected function authUser(): ?array
    {
        if (!isset($_SESSION['auth_id'])) return null;
        return [
            'id'     => $_SESSION['auth_id'],
            'name'   => $_SESSION['auth_name']   ?? null,
            'role'   => $_SESSION['auth_role']   ?? null,
            'avatar' => $_SESSION['auth_avatar'] ?? null,
        ];
    }

    /** Persist the identity in the session after a successful login. */
    protected function loginUser(object $user): void
    {
        session_regenerate_id(true);
        $_SESSION['auth_id']     = (int)$user->id;
        $_SESSION['auth_name']   = $user->name;
        $_SESSION['auth_role']   = $user->role;
        $_SESSION['auth_avatar'] = !empty($user->avatar);   // existence flag only
    }

    protected function logoutUser(): void
    {
        unset($_SESSION['auth_id'], $_SESSION['auth_name'], $_SESSION['auth_role'], $_SESSION['auth_avatar']);
        session_regenerate_id(true);
    }
}
