<aside class="adm-side" id="admSide">
  <a href="/admin/index" class="adm-brand">
    <img src="/pub_wt/imgs/logo.png" alt="WondTech">
    <span>Wond<b>Tech</b></span>
  </a>
  <nav class="adm-nav">
    <div class="grp">{if $isAr}الرئيسية{else}Main{/if}</div>
    <a href="/admin/index" class="{if $active=='dashboard'}active{/if}">
      <i class="fa-solid fa-gauge-high"></i> {if $isAr}لوحة المعلومات{else}Dashboard{/if}
    </a>

    <div class="grp">{if $isAr}الإدارة{else}Manage{/if}</div>
    {if $caps.users}
    <a href="/admin/users/index" class="{if $active=='users'}active{/if}">
      <i class="fa-solid fa-users"></i> {if $isAr}المستخدمون{else}Users{/if}
    </a>
    {/if}

    <div class="grp">{if $isAr}الحساب{else}Account{/if}</div>
    <a href="/admin/profile" class="{if $active=='profile'}active{/if}">
      <i class="fa-solid fa-user-gear"></i> {if $isAr}ملفي الشخصي{else}My Profile{/if}
    </a>
    <a href="/admin/logout">
      <i class="fa-solid fa-right-from-bracket"></i> {if $isAr}تسجيل الخروج{else}Logout{/if}
    </a>
  </nav>
</aside>
