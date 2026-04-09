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

## Notes

- The Laravel migrations are the source of truth for the current application schema.
- Attachment files are stored on the `public` disk in `storage/app/public/drills/...`.
- Workflow emails use Laravel notifications, so configure SMTP or keep `MAIL_MAILER=log` during local development.
