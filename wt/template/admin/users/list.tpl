{include file='header.tpl'}

<div class="page-head">
  <div>
    <h1 style="font-size:1.4rem">{if $isAr}المستخدمون{else}Users{/if}</h1>
    <p>{if $isAr}إجمالي {$total} مستخدم{else}{$total} users total{/if}</p>
  </div>
  <div class="spacer"></div>
  <a class="btn" href="/admin/users/edit"><i class="fa-solid fa-user-plus"></i> {if $isAr}إضافة مستخدم{else}Add user{/if}</a>
</div>

<form class="toolbar" method="get" action="/admin/users/index">
  <div class="search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" name="q" value="{$q|escape}" placeholder="{if $isAr}بحث بالاسم أو البريد أو الجوال{else}Search name, email or mobile{/if}">
  </div>
  <select name="role" onchange="this.form.submit()">
    <option value="">{if $isAr}كل الأدوار{else}All roles{/if}</option>
    <option value="admin" {if $role=='admin'}selected{/if}>{if $isAr}مدير{else}Admin{/if}</option>
    <option value="moderator" {if $role=='moderator'}selected{/if}>{if $isAr}مشرف{else}Moderator{/if}</option>
    <option value="user" {if $role=='user'}selected{/if}>{if $isAr}مستخدم{else}User{/if}</option>
  </select>
  <button class="btn ghost" type="submit">{if $isAr}تصفية{else}Filter{/if}</button>
</form>

<div class="card">
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th>{if $isAr}المستخدم{else}User{/if}</th>
        <th>{if $isAr}البريد{else}Email{/if}</th>
        <th>{if $isAr}الجوال{else}Mobile{/if}</th>
        <th>{if $isAr}الدور{else}Role{/if}</th>
        <th>{if $isAr}الإعلانات{else}Ads{/if}</th>
        <th>{if $isAr}الحالة{else}Status{/if}</th>
        <th>{if $isAr}انضم{else}Joined{/if}</th>
        <th></th>
      </tr></thead>
      <tbody>
      {foreach $users as $u}
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px">
            <span class="uav">{if $u->avatar}<img src="/api/avatar/{$u->id}" alt="">{else}{$u->name|truncate:1:""|upper}{/if}</span>
            <span>{$u->name|escape}{if $u->id==$meId} <span class="badge grey" style="font-size:.68rem">{if $isAr}أنت{else}You{/if}</span>{/if}</span>
          </div></td>
          <td>{$u->email|escape}</td>
          <td dir="ltr" style="text-align:start">{$u->mobile|default:'—'}</td>
          <td>
            {if $u->role=='admin'}<span class="badge pink">{if $isAr}مدير{else}Admin{/if}</span>
            {elseif $u->role=='moderator'}<span class="badge blue">{if $isAr}مشرف{else}Moderator{/if}</span>
            {else}<span class="badge grey">{if $isAr}مستخدم{else}User{/if}</span>{/if}
          </td>
          <td>{if isset($adCounts[$u->id])}{$adCounts[$u->id]}{else}0{/if}</td>
          <td>{if $u->is_active}<span class="badge green">{if $isAr}نشط{else}Active{/if}</span>{else}<span class="badge red">{if $isAr}محظور{else}Banned{/if}</span>{/if}</td>
          <td>{$u->created_at|truncate:10:""}</td>
          <td>
            <div class="btn-row">
              <a class="btn ghost sm icon" href="/admin/users/edit/{$u->id}" title="{if $isAr}تعديل{else}Edit{/if}"><i class="fa-solid fa-pen"></i></a>
              {if $u->id != $meId}
                <form method="post" action="/admin/users/toggle" style="display:inline">
                  <input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="id" value="{$u->id}">
                  <button class="btn ghost sm icon" title="{if $u->is_active}{if $isAr}حظر{else}Ban{/if}{else}{if $isAr}تفعيل{else}Activate{/if}{/if}">
                    <i class="fa-solid {if $u->is_active}fa-ban{else}fa-circle-check{/if}"></i>
                  </button>
                </form>
                <form method="post" action="/admin/users/delete" style="display:inline" data-confirm="{if $isAr}حذف هذا المستخدم نهائياً؟{else}Permanently delete this user?{/if}">
                  <input type="hidden" name="csrf" value="{$csrf}"><input type="hidden" name="id" value="{$u->id}">
                  <button class="btn danger sm icon" title="{if $isAr}حذف{else}Delete{/if}"><i class="fa-solid fa-trash"></i></button>
                </form>
              {/if}
            </div>
          </td>
        </tr>
      {foreachelse}
        <tr><td colspan="8" class="tbl-empty">{if $isAr}لا يوجد مستخدمون{else}No users found{/if}</td></tr>
      {/foreach}
      </tbody>
    </table>
  </div>
</div>

{if $pages > 1}
<div class="pager">
  {if $page>1}<a href="?q={$q|escape:'url'}&role={$role|escape:'url'}&page={$page-1}"><i class="fa-solid fa-angle-{if $isAr}right{else}left{/if}"></i></a>{/if}
  {section name=p loop=$pages}{assign var=pn value=$smarty.section.p.index+1}
    {if $pn==$page}<span class="cur">{$pn}</span>{else}<a href="?q={$q|escape:'url'}&role={$role|escape:'url'}&page={$pn}">{$pn}</a>{/if}
  {/section}
  {if $page<$pages}<a href="?q={$q|escape:'url'}&role={$role|escape:'url'}&page={$page+1}"><i class="fa-solid fa-angle-{if $isAr}left{else}right{/if}"></i></a>{/if}
</div>
{/if}

{include file='footer.tpl'}
