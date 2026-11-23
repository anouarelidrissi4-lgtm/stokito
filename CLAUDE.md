# Stokito — project rules (read first)

Stokito (https://stokito.com, formerly StockPilot / stockpilot.cloud) is a free online stock, quote, delivery-note and invoicing app for small businesses. Laravel 13 + Livewire 3 / Volt + Tailwind + Alpine. SEO focus: France first, then other French-speaking countries.

## How changes reach production
- `main` is production. A cron job on the VPS pulls `main` every minute and runs `deploy.sh` (composer, migrations, npm build, caches). Merging into `main` = live within ~1 minute.
- Work on a branch and open a pull request; the owner merges it from his phone.
- Never commit `.env`, keys, passwords, database dumps or customer data.

## Data rules (critical)
- Project `1` is the real paying client. Never write migrations, seeders or scripts that change or delete its rows.
- Project `2` is the public demo, rebuilt every night by `php artisan demo:reset` (login `demo@stockpilot.cloud`, keep that e-mail).
- Migrations must be additive and safe to run with `--force` on live data.
- Stock movements: a BL marked "Livré" and a direct invoice take stock out (`App\Support\StockSync`); documents created before 2026-11-22 are never touched.

## Translations
- Fallback locale is `en`. Every new `__('…')` French string must be added to `lang/fr.json` (identity), `lang/en.json` and `lang/ar.json`, otherwise the French site shows English.

## Look and content
- Industrial "dépôt" style: ink `#111`, safety yellow `#ffcc00`, concrete `#e6e3dc`, paper `#f4f2ec`; Barlow / Barlow Condensed / IBM Plex Mono; square corners, 2px black borders, solid offset shadows. Shared classes in `resources/css/app.css` (`.ind-h`, `.ind-mono`, `.ind-hazard`, `.ind-bc`).
- Keep pages light: CSS/SVG animations, no video or GIF files.
- Never invent reviews, ratings, customer counts or "we tested" claims. Do not name competitors with unverified prices.
- Country presets live in `App\Support\Countries` (currency, VAT, fiscal ID labels, amount in words).

---

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
