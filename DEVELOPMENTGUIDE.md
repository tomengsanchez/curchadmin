# Admin Scaffold – Development Guide
This document describes the project structure, stack conventions, database schema, and extension rules for developers.

---

## 1. Project structure

```
paper/
├── App/
│   ├── Controllers/        # HTTP controllers (Controller@action)
│   │   ├── Api/           # API controllers (under /api/*)
│   │   └── *.php          # e.g. UserController, SettingsController, AuthController
│   ├── Views/             # PHP view templates
│   │   ├── layout/       # main.php (master layout: sidebar or top nav)
│   │   └── partials/     # reusable UI fragments (list_*, history_sidebar.php)
│   ├── Capabilities.php   # RBAC capability registry + menu mapping helpers
│   ├── UserUiSettings.php # Per-user UI (theme/layout/mobile-friendly)
│   ├── UserNotificationSettings.php # Per-user notification preferences (scaffold-only by default)
│   ├── NotificationService.php       # Notification persistence + in-app history helpers
│   ├── AuditLog.php       # Generic entity history (created/updated/...)
│   ├── UserProjects.php  # Optional product scoping hook (currently no-op / returns null)
│   ├── DashboardConfig.php # Dashboard widget/config helper
│   ├── ListHelper.php     # List operations: search/sort/paginate
│   ├── ListConfig.php     # Column config (list + export UI)
│   ├── CsvExporter.php    # CSV streaming export
│   ├── ApiToken.php       # REST Bearer token create/validate/revoke
│   ├── UserSession.php    # Track active sessions (user_sessions table)
│   ├── PasswordPolicy.php # Enforce password rules + history
│   ├── GeneralSettings.php
│   ├── DevelopmentSettings.php
│   └── DevClock.php
├── Core/                  # Framework core
│   ├── Router.php
│   ├── Controller.php
│   ├── Auth.php
│   ├── Database.php
│   ├── Csrf.php
│   ├── MigrationRunner.php
│   ├── Logger.php
│   ├── Mailer.php
│   ├── LoginThrottle.php
│   ├── SystemDebug.php
│   └── ...
├── config/
│   ├── database.php       # DB credentials (copy from database-sample.php)
│   └── app.php            # Optional base_url (copy from app-sample.php)
├── database/
│   └── migration_*.php    # Schema migrations (currently 000 + 001)
├── cli/
│   ├── migrate.php        # Run migrations; status; rollback
│   └── send_queued_emails.php # Send pending email_queue rows (cron)
├── public/                # Web root (DocumentRoot should point here)
│   └── index.php          # Front controller – defines all routes
├── logs/                  # php_error.log, auth.log
├── bootstrap.php          # Autoload, ROOT, DB init, Auth init, BASE_URL
└── index.php              # If doc root = project root: forwards to public/index.php
```

---

## 2. Frameworks and stack

- **Backend:** Custom PHP MVC (no framework). PHP 8.0+.
- **Database:** MySQL 5.7+ / MariaDB 10.2+; PDO. Use UTF-8 (utf8mb4). Avoid MySQL-only features if targeting MariaDB (see config/database-sample.php note).
- **Frontend:** Bootstrap 5.3.2, jQuery 3.7.1, Select2. Layout and theme in `App/Views/layout/main.php`.
- **Routing:** `Core\Router`: routes in `public/index.php`; pattern `Controller@action`; path params like `{id}`.
- **Auth:** Session-based; `Core\Auth` (check, user, id, can, login, logout). Roles: Administrator, Standard User, Coordinator. Capabilities in `role_capabilities`; admin bypasses checks. Optional **idle session timeout** (`user_logout_after_minutes` in Security Settings; 0 = disabled). Optional **email-based 2FA** (Security Settings: enable_email_2fa, 2fa_expiration_minutes); routes `/login/2fa`, `/login/2fa/verify`.
- **API auth:** For requests under `/api/`, when no session exists, Bearer token is accepted via `App\ApiToken::validate()`; tokens stored hashed in `api_tokens`. Use `$this->requireAuthApi()` in API controllers to return 401 JSON when unauthenticated.
- **CSRF:** `Core\Csrf::validate()`; call `$this->validateCsrf()` at start of any POST action (web only; API uses token).

---

## 3. Routing and controllers

- Routes are registered in `public/index.php` with `$router->get(...)` and `$router->post(...)`.
- Handler format: `'ControllerName@methodName'`. Controller class: `App\Controllers\ControllerName`. For API routes the handler may be `'Api\ControllerName@methodName'` (class `App\Controllers\Api\ControllerName`).
- Path parameters: e.g. `/entity/view/{id}` → `EntityController::show(int $id)`.
- Controllers extend `Core\Controller`; use `$this->view('module/viewname', $data)`, `$this->redirect()`, `$this->json()`, `$this->requireAuth()`, `$this->requireAuthApi()` (API), `$this->requireCapability('capability_name')`, `$this->validateCsrf()`.

---

## 4. Database structure
This scaffold keeps DB schema minimal and generic. Your product-specific tables should live in future `migration_00x_*.php` files.

### 4.1 Core tables (`migration_000_initial.php`)
- **roles** – id, name (e.g. Administrator)
- **users** – id, username, email, password_hash, password_changed_at, display_name, role_id, timestamps
- **app_settings** – setting_key, setting_value, updated_at
- **role_capabilities** – id, role_id, capability (UNIQUE(role_id, capability))
- **migrations** – migration history table (MigrationRunner)

### 4.2 Platform tables (`migration_001_platform.php`)
- **user_list_columns** – per-user selected columns for list UIs
- **user_dashboard_config** – per-user dashboard widget/config JSON
- **notifications** – in-app notification persistence + clicked/opened state
- **audit_log** – generic entity activity history (entity_type/entity_id/action/changes)
- **api_tokens** – hashed Bearer tokens for REST API authentication
- **email_queue** – queued outbound emails (sent by cron)
- **user_password_history** – password history for reuse prevention
- **user_sessions** – active session tracking per user/device

### 4.3 Extending the schema
Add your own tables via new migrations (e.g. `migration_002_...`, `migration_003_...`) and then create:
- model(s) under `App/Models/`
- controllers + views
- capabilities in `App/Capabilities.php` and seed/assign role_capabilities as needed.

---

## 5. Migrations

- Format: PHP file returning `['name' => 'migration_XXX_description', 'up' => function (\PDO $db): void { ... }, 'down' => function (\PDO $db): void { ... }]`. The `down` callable is required for rollback.
- Run: `php cli/migrate.php`. Status: `php cli/migrate.php --status`.
- Rollback: `php cli/migrate.php --rollback` (undo last migration) or `php cli/migrate.php --rollback --steps=2` (undo last 2 migrations). Rollback runs in LIFO order (most recently applied first). Each migration must have a callable `down` to be rolled back.
- Migrations run in filename order; applied names stored in `migrations` table.
- List (as of this guide): 000 (initial), 001 (platform). Add more migrations as you introduce product-specific modules.
- **Cron:** Run `php cli/send_queued_emails.php` periodically (e.g. every 1–5 min) to send notification emails from `email_queue`.

---

## 6. Important conventions

- **Capabilities:** Defined in `App\Capabilities`. Menu visibility and controller access use `Auth::can('capability_name')`.
- **Notifications:** `App\NotificationService`
  - In-app history + dropdown uses `getForUser()` / `listForUser()`
  - Product-specific events should call scaffold helpers (extend NotificationService when you add modules)
  - Per-user toggles live in `App\UserNotificationSettings` (scaffold-only by default).
- **Email delivery:** queued in `email_queue` and sent by `php cli/send_queued_emails.php` (cron).
- **Audit log:** `App\AuditLog::record($entityType, $entityId, $action, $changes)`. Read via `App\AuditLog::for(...)` / `forPaginated(...)` and display via `App/Views/partials/history_sidebar.php`.
- **Login throttling:** `Core\LoginThrottle` reads settings from `App\Models\AppSettings::getSecurityConfig()` (login_throttle_enabled, login_throttle_max_attempts, login_throttle_lockout_minutes). Throttling can be enabled/disabled and tuned via Security Settings. When disabled, LoginThrottle is a no-op.
- **Password policy:** `App\PasswordPolicy` enforces admin-configured rules on new/changed passwords: minimum length, required character classes, optional expiry (`password_expiry_days`), and history (`password_history_limit`). UserController uses this when creating/updating users; Auth controllers enforce expiry on login (web and API).
- **Views:** Layout in `App/Views/layout/main.php`; `$currentPage` and `$pageTitle` for nav/title; `$content` for main body (views often use ob_start() and then require main.php).
- **List pages:** Use list_pagination.php and list_toolbar.php; pass listPagination, listBaseUrl, listExtraParams for filters (e.g. notifications page).
- **List columns and export:** `App\ListConfig` defines list columns (`getColumns`) and export columns (`getExportColumns`). Views pass `listColumns` + `listExportColumns`; controllers validate selected keys before calling `App\CsvExporter::stream(...)`.
- **User sessions:** `App\UserSession::onLogin()` registers the current session on login; `UserSession::touchOrEnforceForCurrent()` runs each request (updates last_activity_at; enforces max concurrent sessions if configured). Active sessions page: `/account/sessions` (SessionsController); users can log out other devices.
- **API auth:** `App\ApiToken::create()`, `validate()`, `revoke()`. Bearer token sent in `Authorization: Bearer <token>`. API login at `POST /api/auth/login` returns a token when email-based 2FA is disabled (or after 2FA flow if enabled).
- **2FA:** When Security Settings enable email 2FA, web login redirects to `/login/2fa`; user enters code sent by email; `AuthController@twoFactorVerify` completes login. API login can require 2FA depending on implementation.
- **Idle logout:** `Core\Auth::init()` checks `user_logout_after_minutes` (from app_settings); after inactivity, session is destroyed and user redirected to `/login?timeout=1`. Not applied to API (Bearer) requests.
- **DevClock:** `App\DevClock` provides optional simulated "current" time for development (System > Development); e.g. for testing scheduled behaviour without changing system time.
- **Product-specific workflows:** Not included in the scaffold by default. Add workflow logic in your module controllers/models and record key events in `audit_log`.
- **Serve routes:** not part of the scaffold by default. Add `/serve/*` endpoints when your product introduces file uploads.

---

## 7. Configuration notes

- **config/database-sample.php:** Copy to `config/database.php`; set host, dbname, username, password, charset. Comment there: production may use MariaDB – keep SQL compatible.
- **config/app-sample.php:** Optional; copy to `config/app.php` and set `base_url` for subfolder installs.
- **Document root:** Prefer pointing the web server at `public/`. If document root is project root, root `index.php` should forward to `public/index.php` and rewrite rules must be correct (e.g. `.htaccess` in public).

---

## 8. Default login

- **Username:** admin  
- **Password:** admin123  

Change after first login in production.
