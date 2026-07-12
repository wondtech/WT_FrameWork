{include file='header.tpl'}

<div class="page-head">
  <div>
    <h1 style="font-size:1.4rem">{if $isAr}ملفي الشخصي{else}My Profile{/if}</h1>
    <p>{if $isAr}حدّث بياناتك وكلمة المرور{else}Update your details and password{/if}</p>
  </div>
</div>

<div class="grid-2">
  <div class="card card-pad">
    <h2 style="font-size:1.05rem;margin-bottom:18px">{if $isAr}المعلومات الأساسية{else}Basic info{/if}</h2>
    <form class="frm" method="post" action="/admin/profile/save" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="{$csrf}">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:6px">
        <span class="uav" style="width:64px;height:64px;font-size:1.4rem">{if $u->avatar}<img src="/api/avatar/{$u->id}" alt="">{else}{$u->name|truncate:1:""|upper}{/if}</span>
        <div class="fld" style="flex:1">
          <label>{if $isAr}الصورة الرمزية{else}Avatar{/if}</label>
          <input type="file" name="avatar" accept="image/*">
        </div>
      </div>
      <div class="fld">
        <label>{if $isAr}الاسم{else}Name{/if}</label>
        <input type="text" name="name" value="{$u->name|escape}" required>
      </div>
      <div class="fld">
        <label>{if $isAr}البريد الإلكتروني{else}Email{/if}</label>
        <input type="email" value="{$u->email|escape}" disabled>
        <span class="hint">{if $isAr}لا يمكن تغيير البريد من هنا{else}Email cannot be changed here{/if}</span>
      </div>
      <div>
        <button class="btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> {if $isAr}حفظ{else}Save{/if}</button>
      </div>
    </form>
  </div>

  <div class="card card-pad">
    <h2 style="font-size:1.05rem;margin-bottom:18px">{if $isAr}تغيير كلمة المرور{else}Change password{/if}</h2>
    <form class="frm" method="post" action="/admin/profile/save">
      <input type="hidden" name="csrf" value="{$csrf}">
      <div class="fld">
        <label>{if $isAr}كلمة المرور الحالية{else}Current password{/if}</label>
        <div class="inp" style="position:relative">
          <input type="password" name="current_password" autocomplete="current-password">
          <button type="button" class="pw-toggle" style="position:absolute;inset-inline-end:10px;top:50%;transform:translateY(-50%);background:none;border:0;color:var(--muted-2);cursor:pointer"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <div class="fld">
        <label>{if $isAr}كلمة المرور الجديدة{else}New password{/if}</label>
        <div class="inp" style="position:relative">
          <input type="password" name="new_password" minlength="8" autocomplete="new-password">
          <button type="button" class="pw-toggle" style="position:absolute;inset-inline-end:10px;top:50%;transform:translateY(-50%);background:none;border:0;color:var(--muted-2);cursor:pointer"><i class="fa-solid fa-eye"></i></button>
        </div>
        <span class="hint">{if $isAr}8 أحرف على الأقل — اتركه فارغاً لعدم التغيير{else}At least 8 characters — leave blank to keep current{/if}</span>
      </div>
      <div>
        <button class="btn" type="submit"><i class="fa-solid fa-key"></i> {if $isAr}تحديث كلمة المرور{else}Update password{/if}</button>
      </div>
    </form>
  </div>
</div>

{include file='footer.tpl'}
