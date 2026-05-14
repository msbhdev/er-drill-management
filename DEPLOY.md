# Deployment Guide

This guide is the **canonical reference** for deploying ER Drill Management to a Linux server. The staging environment on the office VirtualBox VM was provisioned with this exact playbook (see `scripts/deploy.sh` and `.github/workflows/deploy.yml` for the automated parts).

For local development, see [README.md](README.md). For the narrative walkthrough of the staging setup with screenshots and reasoning, see `ER Drill - Staging VM Setup.pdf` in the HSE Apps Development docs folder.

---

## Architecture at a glance

```
Internet ── (TLS) ── reverse proxy ─ http://127.0.0.1:80 ─ Nginx ─ PHP-FPM ─ Laravel
                                                                      │
                                                                      ├─ MySQL  (er_drill        — domain)
                                                                      ├─ MySQL  (hse_shared_auth — accounts, shared with other HSE apps)
                                                                      └─ Redis  (cache, sessions, queue)
```

| Component | Minimum |
|---|---|
| OS | Ubuntu 22.04 LTS or newer |
| PHP | 8.3+ with fpm, mysql, mbstring, xml, zip, gd, curl, bcmath, intl, redis |
| Database | MySQL 8.0+ (or MariaDB 10.6+) |
| Cache/queue | Redis 6+ |
| Web | Nginx 1.18+ |
| Node | 20+ (build-time only) |
| RAM | 2 GB (with 1 GB swap if building on the box); 4 GB+ for production |

---

## Pre-flight checklist

Before touching the server:

- [ ] DNS for the production hostname points at the server (or the reverse proxy in front of it).
- [ ] TLS strategy decided — Let's Encrypt direct, Cloudflare Tunnel, Tailscale Funnel, or an in-house load balancer terminating TLS.
- [ ] SMTP credentials for outbound mail (drill notifications). Logging to file is **not** acceptable in production.
- [ ] `User List.xlsx` workbook available for seeding shared-auth accounts, **or** an existing `hse_shared_auth` database to point at.
- [ ] Backup plan for the `er_drill` and `hse_shared_auth` databases plus the `storage/app/public/drills` attachment directory.
- [ ] GitHub repo deploy key prepared (read-only) for cloning, and a workflow runner plan (self-hosted on the server, or SSH from GitHub-hosted runners).

---

## 1. Provision the host

```bash
sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y \
  nginx mysql-server redis-server git unzip curl ca-certificates acl \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-zip php8.3-gd php8.3-curl php8.3-bcmath php8.3-intl \
  php8.3-redis php8.3-sqlite3 \
  composer nodejs npm
```

> On Ubuntu releases where PHP 8.3 isn't in the default repos, use Ondřej Surý's PPA (`ppa:ondrej/php`). On Ubuntu 26.04+ the default PHP version (8.5) also satisfies the `^8.3` constraint.

Add a `deploy` service user that owns the application files and runs both the queue worker and the deploy script. It belongs to `www-data` so PHP-FPM can read/write `storage/`:

```bash
sudo useradd -m -s /bin/bash -G www-data deploy
sudo install -d -o deploy -g deploy -m 700 /home/deploy/.ssh
```

If composer/npm will run on this box (single-server deploys), add 2 GB of swap when RAM is tight:

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile
echo "/swapfile none swap sw 0 0" | sudo tee -a /etc/fstab
```

---

## 2. Database

Generate a strong password and store it in your secret manager (do **not** check it into git):

```bash
DB_PASS=$(openssl rand -hex 16)

sudo mysql <<SQL
CREATE DATABASE er_drill        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE hse_shared_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'erdrill'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON er_drill.*        TO 'erdrill'@'localhost';
GRANT ALL PRIVILEGES ON hse_shared_auth.* TO 'erdrill'@'localhost';
FLUSH PRIVILEGES;
SQL
```

> **Production note on `hse_shared_auth`.** If the shared-auth database already exists on another host, do not recreate it. Point `AUTH_DB_HOST` / `AUTH_DB_DATABASE` at the shared instance and grant the `erdrill` user `SELECT, INSERT, UPDATE, DELETE` on its tables. Migrations in `database/migrations/2026_04_23_*` are idempotent (`hasTable` guards) and are safe to run against an existing shared DB.

---

## 3. Application

### 3.1 Clone

```bash
sudo install -d -o deploy -g www-data -m 2775 /var/www/er-drill
sudo -u deploy git clone git@github.com:msbhdev/er-drill-management.git /var/www/er-drill
sudo -u deploy git -C /var/www/er-drill checkout <production-branch>
```

### 3.2 `.env`

Write `/var/www/er-drill/.env` as `deploy:www-data` mode `640`:

```dotenv
APP_NAME="ER Drill Management"
APP_ENV=production
APP_KEY=                                  # set by `php artisan key:generate`
APP_DEBUG=false
APP_URL=https://erdrill.example.com
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=er_drill
DB_USERNAME=erdrill
DB_PASSWORD=<generated>

AUTH_DB_CONNECTION=mysql
AUTH_DB_HOST=<shared-auth host>
AUTH_DB_DATABASE=hse_shared_auth
AUTH_DB_USERNAME=erdrill
AUTH_DB_PASSWORD=<shared-auth password>

SESSION_DRIVER=redis
SESSION_LIFETIME=480
SESSION_SECURE_COOKIE=true                # because we serve over HTTPS

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

MAIL_MAILER=smtp                          # use a real mailer in production
MAIL_HOST=<smtp host>
MAIL_PORT=587
MAIL_USERNAME=<smtp user>
MAIL_PASSWORD=<smtp pass>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@erdrill.example.com"
MAIL_FROM_NAME="ER Drill Management"

ER_DRILL_DEFAULT_TIMEZONE=Asia/Kuala_Lumpur
ER_DRILL_AUTH_APP_CODE=er_drill
ER_DRILL_USER_LIST_PATH=/var/www/er-drill/storage/app/UserList.xlsx
ER_DRILL_DEFAULT_RIG_LOCATION=Malaysia
```

> **Reverse-proxy trust is wired in code (`bootstrap/app.php`), not via `.env`.** Trust is hard-coded to `127.0.0.1` / `::1` because `env()` returns `null` inside `bootstrap/app.php` after `php artisan config:cache`. If your production reverse proxy runs on a different host, edit `bootstrap/app.php` to list those proxy IPs explicitly — do not try to use an env var here.

### 3.3 Storage directories (gitignored)

```bash
cd /var/www/er-drill
sudo -u deploy mkdir -p storage/app/public storage/app/private \
  storage/framework/cache/data storage/framework/sessions \
  storage/framework/views storage/framework/testing
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R g+rwxs storage bootstrap/cache
```

### 3.4 First install + migrate + seed

```bash
cd /var/www/er-drill
sudo -u deploy composer install --no-dev --optimize-autoloader --no-interaction
sudo -u deploy npm ci && sudo -u deploy npm run build

sudo -u deploy php artisan key:generate --force
sudo -u deploy php artisan migrate --force
sudo -u deploy php artisan storage:link
sudo -u deploy php artisan config:cache
sudo -u deploy php artisan route:cache
sudo -u deploy php artisan view:cache
```

> Only seed shared-auth on a **fresh** `hse_shared_auth` database. Skip on an existing shared instance to avoid touching live accounts. To seed:
>
> ```bash
> # copy User List.xlsx to /var/www/er-drill/storage/app/UserList.xlsx first
> sudo -u deploy php artisan db:seed --force
> ```

---

## 4. Nginx vhost

`/etc/nginx/sites-available/er-drill`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name erdrill.example.com;
    root /var/www/er-drill/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    client_max_body_size 50M;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/er-drill /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

TLS termination:

- **Let's Encrypt direct:** `sudo apt-get install certbot python3-certbot-nginx && sudo certbot --nginx -d erdrill.example.com`
- **Behind Cloudflare/LB:** keep Nginx on plain HTTP; the proxy adds `X-Forwarded-Proto: https`. Make sure the proxy IPs are listed in `bootstrap/app.php`.

---

## 5. Queue worker & scheduler (systemd)

`/etc/systemd/system/er-drill-queue.service`:

```ini
[Unit]
Description=ER Drill Queue Worker
After=network.target mysql.service redis-server.service

[Service]
Type=simple
User=deploy
Group=www-data
WorkingDirectory=/var/www/er-drill
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

`/etc/systemd/system/er-drill-schedule.service` + `.timer`:

```ini
# .service
[Unit]
Description=ER Drill Scheduler
[Service]
Type=oneshot
User=deploy
Group=www-data
WorkingDirectory=/var/www/er-drill
ExecStart=/usr/bin/php artisan schedule:run

# .timer
[Unit]
Description=Run ER Drill Scheduler every minute
[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
AccuracySec=1s
[Install]
WantedBy=timers.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now er-drill-queue.service er-drill-schedule.timer
```

Allow `deploy` to restart these without a password (needed by `scripts/deploy.sh`):

```bash
sudo tee /etc/sudoers.d/50-deploy-services >/dev/null <<EOF
deploy ALL=(root) NOPASSWD: /bin/systemctl restart er-drill-queue.service, /bin/systemctl restart php8.3-fpm
EOF
sudo chmod 440 /etc/sudoers.d/50-deploy-services
```

---

## 6. CI/CD (subsequent deploys)

`.github/workflows/deploy.yml` is already in the repo. It triggers on push to `staging` and runs `scripts/deploy.sh` on a self-hosted runner labelled `er-drill-staging`. To add production:

1. Duplicate the workflow as `deploy-prod.yml`, trigger on push to `main` (or a `release/*` tag), runner label `er-drill-prod`.
2. Install the runner on the production server the same way — see the staging runbook PDF, §10.
3. Optionally add an `environment: production` block to require manual approval in the GitHub UI.

`scripts/deploy.sh` is environment-agnostic; it operates on `$APP_DIR` (default `/var/www/er-drill`) and the branch named in `$DEPLOY_BRANCH` (default `staging`). For production, set `DEPLOY_BRANCH=main` in the workflow `env`.

### Manual deploy

```bash
sudo -u deploy bash /var/www/er-drill/scripts/deploy.sh
```

The script puts the app into maintenance mode, force-resets the working tree to `origin/$DEPLOY_BRANCH`, reinstalls dependencies, runs migrations, rebuilds caches, restarts the queue, and clears maintenance mode on exit.

---

## 7. Production hardening

Items that are deferred on staging but mandatory in production:

- [ ] **Disable password SSH login** once key-only access is verified: set `PasswordAuthentication no` in `/etc/ssh/sshd_config`.
- [ ] **Firewall** (`ufw allow 22,80,443/tcp` and deny the rest).
- [ ] **Fail2ban** for SSH brute-force protection.
- [ ] **Automated backups** — at minimum: nightly `mysqldump` of both databases, daily snapshot of `storage/app/public/drills`. Keep ≥ 7 days off-host.
- [ ] **Log rotation** for `storage/logs/laravel.log` (Laravel rotates daily when `LOG_CHANNEL=daily`; otherwise add a logrotate entry).
- [ ] **MySQL not on `0.0.0.0`** — leave `bind-address = 127.0.0.1`. Only expose it across the network if a separate app server requires it, and then use a private network.
- [ ] **Redis bind-address** + `requirepass` if Redis is reachable from anywhere other than `127.0.0.1`.
- [ ] **Monitoring** — at minimum, an external uptime check on `/up` (Laravel health endpoint, already routed).
- [ ] **Mail deliverability** — SPF / DKIM / DMARC for the `MAIL_FROM_ADDRESS` domain. Drill notifications will end up in spam without it.
- [ ] **Revoke any temporary `NOPASSWD` sudo entries** created during initial provisioning.

---

## 8. Rollback

`scripts/deploy.sh` does `git reset --hard origin/$BRANCH`, so rolling back is "deploy an earlier commit":

```bash
# from the operator's machine
git push origin <known-good-sha>:main --force-with-lease   # production
# or revert the offending commit
git revert <bad-sha> && git push origin main
```

The deploy workflow re-runs automatically. If the rollback also requires reverting a migration, drop into the server and run `php artisan migrate:rollback --step=N` **before** triggering the deploy — `deploy.sh` only runs `migrate`, never `rollback`.

Database restore (from nightly dump):

```bash
sudo -u deploy php artisan down
mysql er_drill < /backups/er_drill-2026-05-13.sql
sudo -u deploy php artisan up
```

---

## 9. Common issues

| Symptom | Likely cause | Fix |
|---|---|---|
| Page renders unstyled; console shows `Mixed Content … http://.../build/` | `trustProxies` not active — Laravel thinks request is HTTP | Verify `bootstrap/app.php` lists the proxy IP, run `php artisan config:cache` |
| "Please provide a valid cache path" during migrate | Missing `storage/framework/*` dirs (gitignored) | Re-run §3.3 |
| Seeder logs "User list workbook not found" | `UserList.xlsx` not at `ER_DRILL_USER_LIST_PATH` | Place the workbook, re-run `db:seed` |
| `419 Page Expired` on form submission | `SESSION_SECURE_COOKIE=true` set but request arriving as HTTP | Add proxy IP to `bootstrap/app.php`, or set `SESSION_SECURE_COOKIE=false` if intentionally serving over HTTP |
| Queue worker eats memory over time | Long-running PHP process | Already mitigated by `--max-time=3600`; systemd `Restart=always` cycles it |
