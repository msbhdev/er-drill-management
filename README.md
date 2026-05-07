# ER Drill Management

ER Drill Management is a Laravel + Livewire + Tailwind CSS web application for offshore emergency response drill tracking. It supports shared role accounts per rig, workflow approvals, reporting, attachments, and email notifications.

## Roles

- `STO`: create, edit, save draft, submit, and resubmit drills for the assigned rig
- `BE`: verify or return submitted drills for the assigned rig
- `OIM`: approve or return verified drills, then close approved drills for the assigned rig
- `RM`: rig-level reporting and export access
- `Management`: read-only reporting across all rigs
- `Administrator`: full access plus master data and account management

## Core Features

- Shared role accounts per rig with admin-controlled activation and password reset
- Draft -> submit -> verify -> approve -> close workflow with audit history
- Drill timeline events, follow-up actions, and local attachment storage
- Email notifications on submission, verification, and approval
- Rig-scoped drill queue plus management reporting dashboards
- Excel and PDF exports for reports and drill print views
- Admin screens for users, rigs, drill types, event types, and workflow statuses

## Local Setup

1. Copy `.env.example` to `.env`
2. Update MySQL and mail settings in `.env`
3. Install dependencies

```bash
composer install
npm install
```

4. Generate the app key

```bash
php artisan key:generate
```

5. Run migrations and seeders

```bash
php artisan migrate --seed
```

6. Create the storage link for attachments

```bash
php artisan storage:link
```

7. Start the app

```bash
php artisan serve
npm run dev
```

## Seeded Accounts

All seeded accounts use password `password`.

- `admin@erdrill.local`
- `management@erdrill.local`
- `sto.alpha@erdrill.local`
- `be.alpha@erdrill.local`
- `oim.alpha@erdrill.local`
- `rm.alpha@erdrill.local`
- `sto.bravo@erdrill.local`
- `be.bravo@erdrill.local`
- `oim.bravo@erdrill.local`
- `rm.bravo@erdrill.local`

## Git Workflow

- Develop locally on feature branches
- Commit changes locally
- Push branches to GitHub
- Merge through pull requests into `main`

## Production Deployment

This application can be deployed to an Ubuntu server with a standard Laravel stack.

### Server Requirements

- Ubuntu server with SSH access and a user that can run deployment commands
- Nginx or Apache, with the web root pointed to the Laravel `public/` directory
- PHP 8.3 or newer with common Laravel extensions:
  - `php-fpm`
  - `php-cli`
  - `php-mysql`
  - `php-xml`
  - `php-mbstring`
  - `php-curl`
  - `php-zip`
  - `php-gd`
  - `php-bcmath`
  - `php-intl`
- Composer
- Node.js and npm for building Vite assets
- MySQL 8 or compatible MariaDB
- Supervisor for the Laravel queue worker
- Certbot or another SSL solution for HTTPS

### Required Production Details

Before deployment, confirm:

- Server SSH host, user, authentication method, and whether the user has `sudo`
- Production domain or IP address
- MySQL database credentials for the main application database
- MySQL database credentials for the shared auth database
- SMTP/mail settings
- Production file path for `ER_DRILL_USER_LIST_PATH`
- Backup plan for the database and uploaded attachment files

### Production Environment

Production should use a dedicated `.env` file with values similar to:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=Asia/Kuala_Lumpur
ER_DRILL_DEFAULT_TIMEZONE=Asia/Kuala_Lumpur

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=er_drill_management
DB_USERNAME=
DB_PASSWORD=

AUTH_DB_CONNECTION=mysql
AUTH_DB_HOST=127.0.0.1
AUTH_DB_PORT=3306
AUTH_DB_DATABASE=hse_shared_auth
AUTH_DB_USERNAME=
AUTH_DB_PASSWORD=
AUTH_PASSWORD_RESET_CONNECTION=auth

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

### Deployment Commands

A typical production deployment runs:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The queue worker should be managed by Supervisor:

```bash
php artisan queue:work
```

If scheduled tasks are added, configure the Laravel scheduler through cron:

```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

## CI/CD

CI/CD can be configured with GitHub Actions.

Recommended workflow:

- Run CI on pull requests:
  - Install Composer dependencies
  - Install npm dependencies
  - Build frontend assets
  - Run the Laravel test suite
- Deploy on push or merge to `main`:
  - SSH into the Ubuntu server
  - Pull the latest code
  - Install production dependencies
  - Build assets or upload prebuilt assets
  - Run migrations with `--force`
  - Rebuild Laravel caches
  - Restart PHP-FPM and the queue worker

Recommended GitHub secrets:

- `DEPLOY_HOST`
- `DEPLOY_USER`
- `DEPLOY_SSH_KEY`
- `DEPLOY_PATH`
- `APP_ENV_FILE`

## Notes

- The Laravel migrations are the source of truth for the current application schema.
- Attachment files are stored on the `public` disk in `storage/app/public/drills/...`.
- Workflow emails use Laravel notifications, so configure SMTP or keep `MAIL_MAILER=log` during local development.
