{include file='_head.tpl' page_title=$fp_title page_sub=$fp_sub}

    <form method="post" action="/auth/forgot" autocomplete="off" novalidate>
      <input type="hidden" name="csrf" value="{$csrf}">
      <div class="field">
        <label for="email">{$fp_email}</label>
        <div class="inp">
          <input type="email" id="email" name="email" placeholder="{$auth_identifier_ph}"
                 value="{$old_email|default:''|escape}" required autofocus>
        </div>
      </div>
      <div class="field">
        <label>{$cap_label}</label>
        <div class="cap-box">
          <img id="capImg" class="cap-img" src="data:image/png;base64,{$captcha_img}" alt="captcha">
          <button type="button" id="capRefresh" class="cap-refresh" aria-label="{$cap_refresh}"><i class="fa-solid fa-rotate-right"></i></button>
          <input type="text" inputmode="numeric" name="captcha" placeholder="{$cap_ph}" required>
        </div>
      </div>
      <button type="submit" class="btn2 auth-btn"><i class="fa-solid fa-paper-plane"></i> {$fp_send}</button>
    </form>

    <div class="auth-foot">
      <a href="/auth/login"><i class="fa-solid {if $dir=='rtl'}fa-arrow-right{else}fa-arrow-left{/if}"></i> {$fp_back_login}</a>
    </div>

{include file='_foot.tpl'}
