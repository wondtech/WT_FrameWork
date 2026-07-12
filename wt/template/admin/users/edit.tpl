{include file='header.tpl'}

<div class="page-head">
  <a class="btn ghost sm icon" href="/admin/users/index"><i class="fa-solid fa-angle-{if $isAr}right{else}left{/if}"></i></a>
  <div>
    <h1 style="font-size:1.4rem">{if $isNew}{if $isAr}إضافة مستخدم{else}Add user{/if}{else}{if $isAr}تعديل مستخدم{else}Edit user{/if}{/if}</h1>
    {if !$isNew}<p>{$u->email|escape}</p>{/if}
  </div>
</div>

<div class="card card-pad" style="max-width:640px">
  <form class="frm" method="post" action="/admin/users/save">
    <input type="hidden" name="csrf" value="{$csrf}">
    <input type="hidden" name="id" value="{$u->id|default:0}">
    <div class="fld">
      <label>{if $isAr}الاسم{else}Name{/if}</label>
      <input type="text" name="name" value="{$u->name|default:''|escape}" required>
    </div>
    <div class="frm-2">
      <div class="fld">
        <label>{if $isAr}البريد الإلكتروني{else}Email{/if}</label>
        <input type="email" name="email" value="{$u->email|default:''|escape}" required>
      </div>
      <div class="fld">
        <label>{if $isAr}الجوال{else}Mobile{/if}</label>
        <input type="text" name="mobile" dir="ltr" inputmode="numeric" value="{$u->mobile|default:''}" placeholder="9665xxxxxxxx"
               oninput="this.value=this.value.replace(/[^0-9]/g,'')" minlength="11" maxlength="12">
      </div>
    </div>
    <div class="frm-2">
      <div class="fld">
        <label>{if $isAr}الدور{else}Role{/if}</label>
        <select name="role">
          <option value="user" {if $u->role=='user' || $isNew}selected{/if}>{if $isAr}مستخدم{else}User{/if}</option>
          <option value="moderator" {if $u->role=='moderator'}selected{/if}>{if $isAr}مشرف{else}Moderator{/if}</option>
          <option value="admin" {if $u->role=='admin'}selected{/if}>{if $isAr}مدير{else}Admin{/if}</option>
        </select>
      </div>
      <div class="fld">
        <label>{if $isAr}الحالة{else}Status{/if}</label>
        <label class="sw" style="margin-top:6px">
          <input type="checkbox" name="is_active" {if $u->is_active || $isNew}checked{/if}>
          <span class="sl"></span>
        </label>
        <span class="hint">{if $isAr}نشط / محظور{else}Active / Banned{/if}</span>
      </div>
    </div>
    <div class="fld">
      <label>{if $isAr}كلمة المرور{else}Password{/if}</label>
      <input type="password" name="password" minlength="8" autocomplete="new-password" {if $isNew}required{/if}>
      <span class="hint">{if $isNew}{if $isAr}8 أحرف على الأقل{else}At least 8 characters{/if}{else}{if $isAr}اتركه فارغاً للإبقاء على الحالية{else}Leave blank to keep current{/if}{/if}</span>
    </div>
    <div class="btn-row">
      <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> {if $isAr}حفظ{else}Save{/if}</button>
      <a class="btn ghost" href="/admin/users/index">{if $isAr}إلغاء{else}Cancel{/if}</a>
    </div>
  </form>
</div>

{include file='footer.tpl'}
