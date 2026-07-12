{include file='_head.tpl' page_title=$auth_title page_sub=$auth_sub}

    <form method="post" action="/auth/login" autocomplete="off" novalidate>
      <input type="hidden" name="csrf" value="{$csrf}">
      <div class="field">
        <label for="identifier">{$auth_identifier}</label>
        <div class="inp">
          <input type="text" id="identifier" name="identifier" placeholder="{$auth_identifier_ph}"
                 value="{$old_identifier|default:''|escape}" required autofocus>
        </div>
      </div>
      <div class="field">
        <label for="password">{$auth_password}</label>
        <div class="inp">
          <input type="password" id="password" name="password" placeholder="{$auth_password_ph}" required>
          <button type="button" class="pw-toggle" aria-label="{$auth_show}"><i class="fa-solid fa-eye"></i></button>
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
      <div class="auth-links"><a href="/auth/forgot">{$auth_forgot}</a></div>
      <button type="submit" class="btn2 auth-btn"><i class="fa-solid fa-right-to-bracket"></i> {$auth_signin}</button>
    </form>

    <div class="auth-foot">
      <a href="/"><i class="fa-solid {if $dir=='rtl'}fa-arrow-right{else}fa-arrow-left{/if}"></i> {$auth_back}</a>
    </div>

{include file='_foot.tpl'}
