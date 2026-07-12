{include file='header.tpl'}

<div class="page-head">
  <div>
    <h1 style="font-size:1.5rem">{if $isAr}مرحباً، {$authUser.name}{else}Welcome, {$authUser.name}{/if}</h1>
    <p>{if $isAr}نظرة عامة على لوحة التحكم{else}An overview of your dashboard{/if}</p>
  </div>
</div>

<div class="stats">
  <div class="stat">
    <div class="ic"><i class="fa-solid fa-users"></i></div>
    <div class="n">{$st.usersTotal}</div>
    <div class="l">{if $isAr}المستخدمون{else}Users{/if}</div>
  </div>
  <div class="stat">
    <div class="ic"><i class="fa-solid fa-user-check"></i></div>
    <div class="n">{$st.usersActive}</div>
    <div class="l">{if $isAr}نشطون{else}Active{/if}</div>
  </div>
  <div class="stat">
    <div class="ic"><i class="fa-solid fa-user-shield"></i></div>
    <div class="n">{$st.staff}</div>
    <div class="l">{if $isAr}الطاقم{else}Staff{/if}</div>
  </div>
  <div class="stat">
    <div class="ic"><i class="fa-solid fa-user-plus"></i></div>
    <div class="n">{$st.newToday}</div>
    <div class="l">{if $isAr}جديد اليوم{else}New today{/if}</div>
  </div>
</div>

<div class="card">
  <div class="card-h">
    <h2>{if $isAr}أحدث المستخدمين{else}Recent users{/if}</h2>
    <div class="spacer"></div>
    {if $caps.users}<a class="btn ghost sm" href="/admin/users/index">{if $isAr}عرض الكل{else}View all{/if}</a>{/if}
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th>{if $isAr}الاسم{else}Name{/if}</th>
        <th>{if $isAr}البريد{else}Email{/if}</th>
        <th>{if $isAr}الجوال{else}Mobile{/if}</th>
        <th>{if $isAr}الحالة{else}Status{/if}</th>
        <th>{if $isAr}انضم{else}Joined{/if}</th>
      </tr></thead>
      <tbody>
      {foreach $recentUsers as $u}
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px">
            <span class="uav">{if $u->avatar}<img src="/api/avatar/{$u->id}" alt="">{else}{$u->name|truncate:1:""|upper}{/if}</span>
            <span>{$u->name|escape}</span>
          </div></td>
          <td>{$u->email|escape}</td>
          <td dir="ltr" style="text-align:start">{$u->mobile|default:'—'}</td>
          <td>{if $u->is_active}<span class="badge green">{if $isAr}نشط{else}Active{/if}</span>{else}<span class="badge red">{if $isAr}محظور{else}Banned{/if}</span>{/if}</td>
          <td>{$u->created_at|truncate:10:""}</td>
        </tr>
      {foreachelse}
        <tr><td colspan="5" class="tbl-empty">{if $isAr}لا يوجد مستخدمون بعد{else}No users yet{/if}</td></tr>
      {/foreach}
      </tbody>
    </table>
  </div>
</div>

{include file='footer.tpl'}
