<div align="center">

# QrMe

**Digital business cards with QR codes for your team**

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Vue](https://img.shields.io/badge/Vue-3-4FC08D?logo=vue.js&logoColor=white)](https://vuejs.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-v4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![License](https://img.shields.io/badge/license-QrMe%20Custom-orange)](./LICENSE)

[**🌐 Live Demo**](https://qrme.cerasusdigital.pl) · [Report a bug](mailto:maciej.wisniewski@aol.com) · [Commercial license](mailto:maciej.wisniewski@aol.com)

</div>

---

## Table of Contents

- [About](#about)
- [Features](#features)
- [Demo](#demo)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [License](#license)
- [Author & Contact](#author--contact)

---

## About

**QrMe** is a digital business card management system for teams. Every employee gets a
unique, browser-accessible business card at a short URL, along with an individual QR
code that can be printed, placed on a badge, or added to company materials.

Built for companies that want to give clients and partners instant access to employee
contact details — without printing paper business cards.

**What makes QrMe stand out?**

- **One platform — full control.** The admin panel gives complete control over employee
  data, office locations, and QR code appearance.
- **Mobile-first business card.** Each employee's public page is optimised for mobile
  with an orange hero banner and overlapping avatar.
- **Scan tracking.** The scan counter counts only real QR code scans via a dedicated
  `/qr/` endpoint — manual link visits are not counted.
- **One-tap contact save.** The business card includes a vCard (`.vcf`) download button
  that adds the contact directly to the phone.
- **Bilingual interface.** Full Polish and English support with automatic browser language
  detection and a PL/EN toggle on every page.

---

## Features

| Area | Feature |
|---|---|
| **Employees** | Create, edit, delete (soft-delete), profile photo, bio, social media links |
| **QR Codes** | Automatic SVG generation, custom colour per employee or global, eye shape selector (square / round) |
| **Universal QR Codes** | Standalone QR codes pointing to any URL — quizzes, surveys, landing pages — with logo, colour, and scan tracking |
| **Scan tracking** | QR scan counter via `/qr/` and `/qr/link/` endpoints — manual URL visits are not counted |
| **vCard** | Download contact as `.vcf` (vCard 3.0) with full address, phone, and email |
| **Locations** | Manage company offices assignable to employees |
| **Global settings** | Company name, VAT ID, global QR colour — colour change only regenerates employees without a custom colour |
| **Auto email** | Generate employee email from first + last name (`m.smith@company.com`) |
| **Public business card** | Mobile-first, hero banner, avatar, Google Maps link, social media |
| **Authentication** | Laravel Fortify: login, password reset, email verification, 2FA |
| **i18n** | Polish / English, auto-detected from browser, PL/EN switcher |
| **Theme** | Orange colour scheme, dark / light mode |

---

## Demo

> 🌐 **[qrme.cerasusdigital.pl](https://qrme.cerasusdigital.pl)**

The demo runs a full production environment with sample employees and business cards.
You can:

- Scan any employee's QR code and view the public business card
- Tap "Add to contacts" to download the vCard
- Switch language (PL/EN) using the toggle in the top-right corner
- Browse the admin panel (login credentials available on request)

---

## Tech Stack

### Backend

| Tool | Version | Purpose |
|---|---|---|
| **PHP** | 8.3 | Backend language |
| **Laravel** | 13 | MVC framework, routing, Eloquent ORM |
| **Laravel Fortify** | 1.34 | Authentication (login, password reset, 2FA) |
| **Inertia.js** | 3.0 | Laravel–Vue bridge, SPA without a REST API |
| **Laravel Wayfinder** | 0.1 | Type-safe Laravel routes exported to TypeScript |
| **BaconQrCode** | 3.0 | SVG QR code generation — custom colour, eye shapes, logo embedding |
| **SQLite / MySQL** | — | Database |

### Frontend

| Tool | Version | Purpose |
|---|---|---|
| **Vue 3** | 3.5 | UI framework (Composition API, `<script setup>`) |
| **Inertia.js (JS)** | 3.0 | Laravel integration, SPA navigation |
| **Tailwind CSS** | v4 | Utility-first CSS with CSS variables |
| **Reka UI** | 2.6 | Headless UI components (dialogs, sidebar, dropdowns) |
| **vue-i18n** | 11.4 | PL/EN internationalisation |
| **Lucide Vue Next** | 0.468 | SVG icon library |
| **vue-sonner** | 2.0 | Toast notifications |
| **Vite** | 8.0 | Bundler and dev server with HMR |

### Developer Tools

| Tool | Purpose |
|---|---|
| **Pest PHP 4** | Testing (36 passed, 129 assertions) |
| **Laravel Pint** | PHP code formatting (PSR-12) |
| **ESLint + Prettier** | TypeScript / Vue linting and formatting |
| **vue-tsc** | TypeScript type checking in `.vue` files |
| **pnpm** | Node.js package manager |

---

## Architecture

```
Browser
    │
    ├── GET /p/{short_id}          → PublicCardController    → BusinessCard.vue
    ├── GET /qr/{short_id}         → QrScanController (+1 scan_count → redirect /p/)
    ├── GET /qr/link/{short_id}    → CustomQrScanController (+1 scan_count → redirect to target URL)
    ├── GET /p/{short_id}/vcard    → VCardService → .vcf download
    │
    └── /admin/*  (authentication required)
            ├── employees          → EmployeeController
            ├── locations          → LocationController
            ├── qrcodes            → CustomQrController      (universal QR codes)
            └── settings           → GlobalSettingsController
                                        └── QrCodeService (BaconQrCode SVG)
```

**Inertia.js** removes the need to build a separate REST API — every Laravel controller
response directly renders the corresponding Vue component.

**Wayfinder** auto-generates `resources/js/routes/index.ts` with type-safe routing
functions from Laravel routes. Renaming a route immediately surfaces as a TypeScript
error on the frontend.

---

## System Requirements

- **PHP** >= 8.3 with extensions: `gd`, `pdo`, `mbstring`, `openssl`
- **Composer** >= 2.0
- **Node.js** >= 20
- **pnpm** >= 9 (`npm install -g pnpm`)
- **MySQL** >= 8.0 or **SQLite** >= 3.35

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/qrme.git
cd qrme
```

### 2. Install PHP and Node.js dependencies

```bash
composer install
pnpm install
```

### 3. Environment variables

```bash
cp .env.example .env
```

Edit `.env` — see the [Configuration](#configuration) section for all options.

### 4. Application key and database

```bash
php artisan key:generate
php artisan migrate
```

### 5. Storage symlink

Photos and QR code PNGs are stored in `storage/app/public`. Create the public symlink:

```bash
php artisan storage:link
```

### 6. Start the application

```bash
# Terminal 1 — frontend dev server (HMR)
pnpm dev

# Terminal 2 — PHP backend
php artisan serve
```

Open `http://localhost:8000` in your browser.

**Production build:**

```bash
pnpm build
php artisan optimize
```

---

## Configuration

### Key `.env` variables

| Variable | Default | Description |
|---|---|---|
| `APP_NAME` | `QrMe` | Application name shown in the admin panel |
| `APP_URL` | `http://localhost:8000` | Base URL — **embedded directly into QR code PNGs** |
| `APP_DOMAIN` | `example.pl` | Domain used for auto-generating employee emails |
| `APP_LOCALE` | `pl` | Default language (`pl` / `en`) |
| `DB_CONNECTION` | `sqlite` | `sqlite` or `mysql` |
| `MAIL_MAILER` | `log` | Mail driver (`smtp`, `mailgun`, `ses`, `log`) |
| `FILESYSTEM_DISK` | `local` | `local` or `s3` for photo and QR storage |

> ⚠️ **Important:** `APP_URL` is written directly into each QR code SVG at generation
> time. Changing the URL after generation requires regenerating all QR codes.

### First-run checklist

1. Register an account or log in
2. Go to **Global Settings** → enter company name, VAT ID, and QR colour
3. Go to **Locations** → add your company offices
4. Go to **Employees** → add your first employee

---

## Usage

### Admin panel (`/admin/employees`)

**Employees:**
- Create with optional auto-generated email (`m.smith@company.com`)
- Profile photo upload (JPEG/PNG, max 2 MB)
- Social media links: Facebook, Instagram, LinkedIn, YouTube
- Each employee gets a unique `short_id` and a QR code SVG generated automatically
- **Per-employee QR colour** — override the global colour for a specific employee; leave blank to inherit the global setting
- **Eye shape** — choose square or round corner markers per employee

**Universal QR Codes (`/admin/qrcodes`):**
- Create standalone QR codes that redirect to any URL (surveys, landing pages, etc.)
- Upload a logo to embed in the centre of the QR code
- Custom colour and eye shape per code
- Dedicated scan tracking counter per code
- Download link for each generated SVG

**Settings → QR colour:**
- Native colour picker + hex input field (both stay in sync)
- Saving a new colour regenerates QR codes only for employees **without** a custom colour

### Public business card (`/p/{short_id}`)

Mobile-first page containing:
- Orange hero banner with profile avatar (initials as fallback)
- **"Add to contacts"** button → downloads `.vcf`
- Direct action buttons: email, phone, Google Maps
- Social media links
- PL/EN language switcher

### QR scan tracking

```
Employee QR scan  → /qr/{short_id}       → scan_count++ → redirect to /p/{short_id}
Universal QR scan → /qr/link/{short_id}  → scan_count++ → redirect to target URL
Manual link visit → /p/{short_id}        → counter does NOT increment
```

The scan counter is displayed next to each employee and each universal QR code in the admin panel.

---

## Testing

```bash
# Run all tests (parallel)
php artisan test --parallel

# Run with coverage report
php artisan test --coverage
```

```
Tests: 2 skipped, 36 passed (129 assertions)
```

---

## Project Structure

```
qrme/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/                  # EmployeeController, LocationController,
│   │   │                           # GlobalSettingsController, CustomQrController
│   │   ├── PublicCardController.php
│   │   ├── QrScanController.php    # Employee QR scan endpoint
│   │   └── CustomQrScanController.php  # Universal QR scan endpoint
│   ├── Models/                     # Employee, Location, Setting, User, CustomQr
│   └── Services/
│       ├── QrCodeService.php       # SVG via BaconQrCode — colour, eye shape, logo
│       ├── VCardService.php        # vCard 3.0 .vcf generation
│       └── EmailGeneratorService.php
├── database/migrations/            # 10 migrations
├── resources/
│   ├── css/app.css                 # Tailwind v4, CSS variables (orange scheme)
│   └── js/
│       ├── i18n/locales/           # pl.ts, en.ts
│       ├── components/
│       │   ├── admin/
│       │   │   ├── QrStylePicker.vue   # Reusable colour + eye shape designer
│       │   │   ├── EmployeeForm.vue
│       │   │   └── CustomQrForm.vue
│       │   └── ...                 # AppSidebar, LocaleSwitcher, shadcn/ui
│       ├── layouts/                # AppLayout, AuthSimpleLayout
│       └── pages/
│           ├── admin/
│           │   ├── employees/      # Create, Edit
│           │   ├── locations/
│           │   ├── qrcodes/        # Index, Create, Edit
│           │   └── settings/
│           ├── auth/               # Login, ForgotPassword, ResetPassword
│           └── BusinessCard.vue
├── routes/
│   ├── web.php                     # Public routes + /qr/ and /qr/link/ endpoints
│   └── admin.php                   # Auth-protected admin routes
└── lang/pl.json                    # Server-side flash messages (Polish)
```

---

## License

QrMe is released under the **QrMe Source Available License**.

### ✅ Permitted without permission

- Personal and non-commercial use
- Educational and academic purposes
- Internal use within an organisation that generates no revenue from the software itself
- Modifying the code for your own non-commercial needs
- Forking and submitting pull requests to this repository

### ❌ Requires written permission from the author

- Any **commercial use** (selling, SaaS, paid services, client deployments)
- Redistributing the software as your own product (open source or commercial)
- Removing or obscuring authorship information

Full license text: [LICENSE](./LICENSE)

**To obtain a commercial license, contact:**
📧 [maciej.wisniewski@aol.com](mailto:maciej.wisniewski@aol.com)

---

## Author & Contact

**Maciej Wiśniewski**
📧 [maciej.wisniewski@aol.com](mailto:maciej.wisniewski@aol.com)

---

<div align="center">
<sub>Built with ❤️ in Poland · Laravel 13 + Vue 3 · © 2026 Maciej Wiśniewski</sub>
</div>