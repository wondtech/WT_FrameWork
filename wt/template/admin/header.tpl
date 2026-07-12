<!DOCTYPE html>
<html lang="{$html_lang|default:'en'}" dir="{$dir}" data-theme="light" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>WT App {if $isAr}الإدارة{else}Admin{/if}{if $subTitle} — {$subTitle}{/if}</title>
<link rel="icon" type="image/png" href="/pub_wt/imgs/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{$bootstrap}">
<link rel="stylesheet" href="/pub_wt/css/fontawesome.min.css">
<link rel="stylesheet" href="/pub_wt/css/wt_admin.css">
</head>
<body>
<div class="adm">
{include file='sidebar.tpl'}
<div class="adm-backdrop" id="admBackdrop"></div>
<div class="adm-main">
  <header class="adm-top">
    <button class="adm-burger" id="admBurger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <h1>{$subTitle|default:''}</h1>
    <div class="spacer"></div>
    <a class="adm-chip" id="langToggle" data-to="{if $isAr}EN{else}AR{/if}" href="#" title="{if $isAr}English{else}العربية{/if}">
      <i class="fa-solid fa-globe"></i> {if $isAr}EN{else}ع{/if}
    </a>
    <a class="adm-chip" href="/" target="_blank" title="{if $isAr}الموقع{else}Website{/if}"><i class="fa-solid fa-globe-pointer"></i><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
    <div class="adm-user">
      <div class="av">{if $authUser.avatar}<img src="/api/avatar/{$authUser.id}" alt="">{else}<i class="fa-solid fa-user"></i>{/if}</div>
      <div>
        <div class="nm">{$authUser.name}</div>
        <div class="rl">{if $authUser.role=='admin'}{if $isAr}مدير{else}Admin{/if}{else}{if $isAr}مشرف{else}Moderator{/if}{/if}</div>
      </div>
    </div>
    <a class="btn ghost sm" href="/admin/logout" title="{if $isAr}خروج{else}Logout{/if}"><i class="fa-solid fa-right-from-bracket"></i></a>
  </header>
  <div class="adm-content">
    {if $flash}
      <div class="adm-flash {$flash.type}">
        <i class="fa-solid {if $flash.type=='error'}fa-circle-exclamation{else}fa-circle-check{/if}"></i>
        <span>{$flash.msg}</span>
      </div>
    {/if}
