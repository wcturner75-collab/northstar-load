# Northstar Load

Visual FiveM loading-screen builder for **Northstar Load**.

Stack: PHP 8.2+, MySQL 8 / MariaDB, PDO, sessions, vanilla JS, ZipArchive.

## Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `json`, `mbstring`, `fileinfo`, `gd`, `zip`
- MySQL 8+ or MariaDB 10.5+
- Apache/Nginx with document root pointed at `public/`

## Quick start (local)

```bash
# 1. Configure
cp config/config.example.php config/config.php
# edit DB credentials

# 2. Create database & import schema
mysql -u northstar -p northstar_load < database/schema.sql

# 3. Seed templates
php database/seeds/templates.php

# 4. Run PHP built-in server (dev only)
php -S 127.0.0.1:8080 -t public public/router.php
```

Open http://127.0.0.1:8080 — register, create a project, design, generate ZIP.

## Production layout

Point the web server **only** at `public/`.

Never expose `app/`, `config/`, `storage/`, or `templates/`.

### DirectAdmin + LiteSpeed (subdomain)

1. Upload the project into the subdomain folder (e.g. `domains/…/public_html/load/` or the subdomain’s own `public_html`).
2. **Best:** Subdomain → Document Root Override → path ending in `/public`.
3. **Or:** leave docroot on the project folder and use the root `.htaccess` (rewrites into `public/`).
4. Permissions: folders `755`, files `644`, owned by your DA user. `storage/uploads`, `storage/builds`, `storage/logs` must be writable (`775` if needed).
5. Create MySQL DB, import `database/schema.sql`, copy `config/config.example.php` → `config/config.php`.
6. Set `app.url` and `hosting.load_base_url` to `https://load.yourdomain.tld`.
7. Cloudflare: DNS only (grey cloud) while testing; purge cache after fixes. A LiteSpeed “403 Forbidden” page is from the origin, not Cloudflare.

Empty docroot / wrong path / no `index.php` → classic LiteSpeed 403.

Example Nginx:

```nginx
root /var/www/northstar/public;
index index.php;
location / {
  try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
  include fastcgi_params;
  fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## Architecture

```
Website → Auth/Session → Builder (JSON state)
       → Autosave API → MySQL projects.config_json
       → Generate API → ResourceGenerator
            → publish_token on project
            → thin ZIP (fxmanifest → hosted URL + client.lua)
       → download.php?build=TOKEN (auth + ownership)
Players → https://load.northstarscripts.us/load?t=TOKEN
       → /api/load/config.php + /api/load/media.php
```

Set `hosting.load_base_url` in `config/config.php` (production: `https://load.northstarscripts.us`).

## Cron

Expire old ZIPs (projects remain):

```bash
php scripts/expire_builds.php
```

## Management & health

- Staff console: `/manage/` (roles `manager` / `admin` on `users.role`)
- First admin: `UPDATE users SET role = 'admin' WHERE email = 'you@example.com';`
- Broken DB / missing tables → redirect to `/system-status.php`
- Builder media fields use a popup picker (no typed Media IDs)

## Security notes

- Passwords: `password_hash` / `password_verify`
- CSRF on mutating requests
- Ownership checks on projects, media, builds
- Resource names: `^[a-zA-Z0-9_-]{1,64}$`
- Uploads: extension + MIME + magic bytes; no PHP/HTML/SVG/JS
- Downloads use random 64-char tokens, not sequential IDs

## Entitlements

Plans `free` / `standard` / `pro` live in `entitlements` and `config.php`.  
Payment providers are intentionally not wired in this phase.
