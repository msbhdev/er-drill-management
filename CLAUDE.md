# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 13 + Livewire 3 + Volt + Tailwind app for offshore Emergency Response drill tracking. PHP 8.3, MySQL, DomPDF, PhpSpreadsheet. Auth scaffolded from Laravel Breeze.

## Commands

```bash
composer install && npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Run app (server + queue + pail logs + vite)
composer dev
# or individually:
php artisan serve
npm run dev

# Tests (configured in phpunit.xml — uses MySQL `er_drill_test` + `hse_shared_auth_test`)
composer test
php artisan test --filter=DrillWorkflowTest
php artisan test tests/Feature/DrillWorkflowTest.php

# Lint
vendor/bin/pint
```

Seeded login: any of the `*@erdrill.local` accounts in [README.md](README.md), password `password`.

## Architecture

### Two-database split (shared auth)

This app uses **two MySQL connections** — see [config/database.php](config/database.php):

- Default connection (`mysql`) — application/domain tables (drills, attachments, history, master data).
- `auth` connection (`AUTH_DB_*` env vars, defaults to `hse_shared_auth`) — a **shared** auth database used across multiple HSE apps. Contains `accounts`, `account_app_access`, `role_assignee_schedules`, `rigs`.

[app/Models/User.php](app/Models/User.php) sets `$connection = 'auth'` and `$table = 'accounts'`. App access is gated by rows in `account_app_access` matching `config('er_drill.auth_app_code')` (default `er_drill`). Always filter user queries with the `withAppAccess()` scope, otherwise you will pick up accounts from sibling HSE apps.

The User model also exposes virtual `role` / `rig_id` accessors that map to the underlying `role_code` / `rig_code` columns — keep that translation in mind when querying.

[app/Services/UserListSyncService.php](app/Services/UserListSyncService.php) imports accounts from a shared Excel workbook (`ER_DRILL_USER_LIST_PATH`).

### Workflow state machine

Drill lifecycle: `draft → submitted → (returned_by_be | verified) → (returned_by_oim | approved) → closed`. All transitions go through [app/Services/DrillWorkflowService.php](app/Services/DrillWorkflowService.php), which:

- Validates the source status before transitioning.
- Wraps each transition in a DB transaction.
- Stamps timestamps (`submitted_at`, `verified_at`, `approved_at`, `closed_at`).
- Writes a row to `drill_workflow_history` capturing actor account, person name (resolved via `RoleAssigneeSchedule`), role, rig, from/to status, and comments.
- Fires notifications: `DrillSubmittedNotification` → BE, `DrillVerifiedNotification` → OIM, `DrillApprovedNotification` → STO/BE/RM.
- On draft save, snapshots the assigned STO/BE/OIM users for the rig via `assignRigRoleAccounts()` so historical drills retain who was responsible at the time.

Status codes are referenced by string (`'draft'`, `'submitted'`, etc.) — the canonical list lives in [config/er_drill.php](config/er_drill.php) and is enforced by `DrillStatus` rows seeded in the database. The `DrillWorkflowAction` enum in [app/Enums/](app/Enums/) names the transitions.

### Routing & access control

Routes are in [routes/web.php](routes/web.php). Two custom middleware aliases are registered in [bootstrap/app.php](bootstrap/app.php):

- `password.changed` — forces password change on first login (`must_change_password` flag).
- `role:RM,Management,Administrator` — gates routes by `role_code`.

UI is Livewire-driven: `App\Livewire\Drills\IndexPage`, `EditorPage`, `Reports\IndexPage`, `Admin\SettingsPage`, `DashboardPage`. Two non-Livewire controllers handle file streams: `DrillAttachmentDownloadController` and `ReportExportController` (Excel via PhpSpreadsheet, PDF via DomPDF rendering [resources/views/reports/drill-print.blade.php](resources/views/reports/drill-print.blade.php)).

Per-rig data isolation is enforced via `User::canAccessRig()` — Administrator/Management see everything, other roles are scoped to their `rig_id`.

### Roles

`STO` (creates/edits), `BE` (verifies), `OIM` (approves/closes), `RM` (rig reporting), `Management` (read-only cross-rig), `Administrator`. See [app/Enums/UserRole.php](app/Enums/UserRole.php).

### Attachments

Stored on the `public` disk under `storage/app/public/drills/...`. Downloads stream through [app/Http/Controllers/DrillAttachmentDownloadController.php](app/Http/Controllers/DrillAttachmentDownloadController.php) which enforces drill access policy — do not link to public URLs directly.

### Timezones

Each rig has a timezone; user display time uses `User::preferredTimezone()` (rig timezone, falling back to `er_drill.default_timezone`, default `Asia/Kuala_Lumpur`). Use `User::formatDateTime()` for display formatting.

## Conventions

- Migrations are the source of truth for schema (no separate schema file).
- Mail in local dev: keep `MAIL_MAILER=log` unless testing SMTP.
- Test DB connection is `mysql` for both default and `auth` (see [phpunit.xml](phpunit.xml)) — the test runner expects both `er_drill_test` and `hse_shared_auth_test` databases to exist.
