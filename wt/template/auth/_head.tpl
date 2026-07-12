<!DOCTYPE html>
<html lang="{$html_lang|default:'en'}" dir="{$dir}" data-theme="light" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>{$page_title} — WT App</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="/pub_wt/imgs/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{$bootstrap}">
<link rel="stylesheet" href="/pub_wt/css/fontawesome.min.css">
<link rel="stylesheet" href="/pub_wt/css/wt.css">
</head>
<body>
<div class="fx-bg"><span class="fx-orb a"></span><span class="fx-orb b"></span></div>

<a class="lang-toggle auth-lang" href="{if $dir=='rtl'}?lang=EN{else}?lang=AR{/if}">
  <i class="fa-solid fa-globe"></i><span>{$lang_switch}</span>
</a>

<main class="auth-wrap">
  <div class="auth-card">
    <div class="auth-brand">
      <img src="/pub_wt/imgs/logo.png" alt="WondTech">
      <span class="name">Wond<b>Tech</b></span>
    </div>
    <span class="eyebrow auth-badge"><i class="fa-solid fa-shield-halved"></i> {$auth_badge}</span>

    <h1>{$page_title}</h1>
    <p class="auth-sub">{$page_sub}</p>

    {if $flash}
      <div class="auth-alert {$flash.type}">
        <i class="fa-solid {if $flash.type=='error'}fa-circle-exclamation{else}fa-circle-check{/if}"></i>
        <span>{$flash.msg}</span>
      </div>
    {/if}
