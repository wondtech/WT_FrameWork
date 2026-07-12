# WT Framework — PHP Edition

<p align="center">
  <img src="https://wondtech.com/pub_wt/imgs/logo.svg" width="200" alt="WondTech Logo"/>
</p>

<p align="center">
  <b>WT Framework - PHP Edition v2.0</b><br/>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-2.0-blue"/>
  <img src="https://img.shields.io/badge/php-%3E%3D8.2-blue?logo=PHP"/>
  <img src="https://img.shields.io/badge/mysql-%3E%3D5.7-blue?logo=mysql"/>
  <img src="https://img.shields.io/badge/license-MIT-green"/>
</p>

---

## Overview

A lightweight, secure PHP MVC framework built for rapid web application development. Clean architecture, minimal dependencies, and production-ready security out of the box.

---

## What's new in v2.0

- **`Wt_Auth` trait** — session-based role/capability authorization for controllers: `requireRole()`, `requireAnyRole()`, `can()`, `loginUser()`, `logoutUser()`. Define access by overriding `roleCapabilities()`.
- **Bearer-token API support** — the `.htaccess` now passes the `Authorization` header through to PHP, so token-authenticated API endpoints work out of the box.
- **Hardened front controller** (`index.php`) — baseline security headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, HSTS), a global exception handler (JSON for `/api`, no stack-trace leaks), hardened session cookie (HttpOnly + SameSite), and env-driven timezone / debug.
- **Integration plugins** (`wt/plugins/`, namespace `WT\Plugins`, config via `.env`):
  - **`Whatsapp`** — WhatsApp Cloud API (text, templates, media, buttons, OTP, webhooks).
  - **`Stripe`** — payments (Checkout, PaymentIntents, customers, refunds, signed webhooks).
- **Ready-made examples** — a full **light / WondTech-branded admin panel** (`wt/template/admin` + `wt/template/auth`): sign-in with captcha, a dashboard, and a users section, wired through `Auth_Controller`, `Admin_Controller`, and `Admin\Users_Controller`.
- **`WtHelper` additions** — richer date/format/relative-time helpers.

---

## Features

- MVC Architecture (Model, View, Controller)
- Built-in Security (XSS, SQL Injection, CSRF protection)
- Multi-language support (AR & EN with easy extension)
- Smarty Template Engine integration
- Zero Composer dependency (optional)
- Lightweight & fast with minimal overhead
- PDO-based ORM with Singleton connection
- AES-256-CBC encryption built-in
- Email sending with header injection protection
- Image upload, compression & validation
- Automatically minifies HTML, CSS, and JS output with zero configuration.

### Minification Results
```
HTML: 47KB → 31KB  (~34% smaller)
CSS:  28KB → 18KB  (~36% smaller)
JS:   95KB → 61KB  (~36% smaller)
```

---

## Requirements

- PHP 8.2+
- MySQL 5.7+
- Apache with mod_rewrite enabled
- cURL enabled

---

## Getting Started

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/wondtech/wt-framework.git
cd wt-framework
```

**2. Configure environment**
```bash
cp .env.example .env
```

Edit `.env`:
```env
# Database
DB_HOST=127.0.0.1
DB_NAME=your_database
DB_USER=root
DB_PASSWORD=

# Mail
MAIL_APP_NAME=WT App
MAIL_SEND_EMAIL=info@example.com
MAIL_GET_EMAIL=notif@example.com

# Application
APP_ENV=development
APP_URL=http://localhost
APP_SECRET_KEY=your-strong-secret-key
APP_CACHE=false

```

**3. Set permissions**
```bash
chmod 755 wt/template/temp_sys/cache
chmod 755 wt/template/temp_sys/templates_c
```

---

## Project Structure
```
project/
├── .env                    ← Environment variables
├── .env.example            ← Environment template
├── .htaccess               ← URL rewriting & security rules
├── index.php               ← Entry point
├── pub_wt/                 ← Public assets
│   ├── css/                ← Stylesheets
│   ├── js/                 ← JavaScript files
│   ├── imgs/               ← Images
│   └── fonts/              ← Fonts
└── wt/
    ├── controllers/        ← Application controllers
    ├── models/             ← Database models
    ├── libs/               ← Framework core
    │   ├── wt_auto.php     ← Autoloader
    │   ├── wt_config.php   ← App configuration
    │   ├── wt_controller.php ← Base controller
    │   ├── wt_db.php       ← Database (PDO Singleton)
    │   ├── wt_env.php      ← .env loader
    │   ├── wt_front.php    ← Front controller (Router)
    │   ├── wt_helper.php   ← Helper trait
    │   ├── wt_model.php    ← Base model (ORM)
    │   ├── wt_sec.php      ← Security trait
    │   ├── wt_send.php     ← Mail trait
    │   └── wt_smarty.php   ← Smarty wrapper
    ├── lang/               ← Language files
    │   ├── wt_lang.php
    │   ├── wt_ar.php
    │   └── wt_en.php
    └── template/           ← Smarty templates
```

---

## Creating a Controller
```php
<?php
namespace WT\Controllers;

use WT\LIBS\Wt_Controller;
use WT\LIBS\Wt_Sec;

class Home_Controller extends Wt_Controller
{
    use Wt_Sec;

    public function Index_Action(): void
    {
        $tpl = $this->view();
        $tpl->assign('title', 'Welcome');
        $tpl->view('home.tpl');
    }

    public function About_Action(): void
    {
        $tpl = $this->view();
        $tpl->view('about.tpl');
    }
}
```

URL mapping:
```
/ or /home          → Home_Controller::Index_Action
/home/index         → Home_Controller::About_Action
/home/index/1/2     → Home_Controller::About_Action + params [1, 2]
```

---

## Creating a Model
```php
<?php
namespace WT\Models;

use WT\LIBS\Wt_Model;

class Post_Model extends Wt_Model
{
    public ?int    $id         = null;
    public ?string $title      = null;
    public ?string $content    = null;
    public ?string $created_at = null;
    public bool    $is_active  = true;

    protected static string $tableName = 'posts';
    protected static string $pKey      = 'id';

    protected static array $tableSchema = [
        'title'      => self::DATA_TYPE_STR,
        'content'    => self::DATA_TYPE_STR,
        'created_at' => self::DATA_TYPE_STR,
        'is_active'  => self::DATA_TYPE_BOOL,
    ];
}
```

Usage:
```php
// INSERT
$post = new Post_Model();
$post->title   = 'Hello World';
$post->content = 'My first post';
$id = $post->wt_save(); // returns new ID

// SELECT by primary key
$post = Post_Model::wt_getByPkey(1);

// SELECT with conditions
$posts = Post_Model::wt_getData(
    'WHERE is_active = :active ORDER BY id DESC',
    [':active' => [PDO::PARAM_INT, 1]],
    10, // items per page
    1   // page number
);

// UPDATE
$post->title = 'Updated Title';
$post->wt_save();

// DELETE
$post->wt_delete();
Post_Model::wt_deleteByPkey(1);

// COUNT
$total = Post_Model::wt_countData('WHERE is_active = :active', [':active' => [PDO::PARAM_INT, 1]]);

// TRANSACTION
Wt_Model::wt_transaction(function() use ($post, $log) {
    $post->wt_save();
    $log->wt_save();
});
```

---

## Multi-language

Add keys to `wt_ar.php` and `wt_en.php`:
```php
$this->Lang['welcome'] = 'مرحباً';   // AR
$this->Lang['welcome'] = 'Welcome';   // EN
```

Switch language via URL:
```
/?lang=AR
/?lang=EN
```

Use in Smarty template:
```smarty
{$welcome}
```

---

## Security Usage
```php
use WT\LIBS\Wt_Sec;

class My_Controller extends Wt_Controller
{
    use Wt_Sec;

    public function Index_Action(): void
    {
        // Input sanitization
        $name  = $this->Wt_SecInput($_POST['name'],  'str');
        $email = $this->Wt_SecInput($_POST['email'], 'email');
        $age   = $this->Wt_SecInput($_POST['age'],   'int');

        // Encryption
        $encoded = $this->Wt_Encode('sensitive data');
        $decoded = $this->Wt_Decode($encoded);

        // Captcha
        $tpl->assign('captcha', $this->Wt_CrtCap());
        $tpl->assign('capImg',  $this->Wt_DrwCap());
    }
}
```

---

## Security Features

| Feature | Implementation |
|---|---|
| SQL Injection | PDO prepared statements |
| XSS | `htmlspecialchars` on all outputs |
| Path Traversal | `realpath` validation in autoloader |
| Open Redirect | Host validation in `Wt_ReDir` |
| Header Injection | `\r\n` stripping in mail headers |
| Encryption | AES-256-CBC via OpenSSL |
| File Upload | MIME type validation via `finfo` |
| Captcha | `random_int` secure generation |

---

## License

© 2026 WT Framework — PHP Edition 2.0 — Built by [WondTech](https://wondtech.com). All rights reserved.
