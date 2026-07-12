{include file='_head.tpl' page_title=$rp_title page_sub=$rp_sub}

    <form method="post" action="/auth/reset" autocomplete="off" novalidate>
      <input type="hidden" name="csrf" value="{$csrf}">
      <div class="field">
        <label for="code">{$rp_code}</label>
        <div class="inp">
          <input type="text" id="code" name="code" inputmode="numeric" placeholder="- - - - -" required autofocus>
        </div>
      </div>
      <div class="field">
        <label for="password">{$rp_password}</label>
        <div class="inp">
          <input type="password" id="password" name="password" placeholder="{$auth_password_ph}" required minlength="8">
          <button type="button" class="pw-toggle" aria-label="{$auth_show}"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <div class="field">
        <label for="password_confirm">{$rp_confirm}</label>
        <div class="inp">
          <input type="password" id="password_confirm" name="password_confirm" placeholder="{$auth_password_ph}" required minlength="8">
          <button type="button" class="pw-toggle" aria-label="{$auth_show}"><i class="fa-solid fa-eye"></i></button>
        </div>
      </div>
      <button type="submit" class="btn2 auth-btn"><i class="fa-solid fa-check"></i> {$rp_submit}</button>
    </form>

    <div class="auth-foot">
      <a href="/auth/login"><i class="fa-solid {if $dir=='rtl'}fa-arrow-right{else}fa-arrow-left{/if}"></i> {$fp_back_login}</a>
    </div>

{include file='_foot.tpl'}
