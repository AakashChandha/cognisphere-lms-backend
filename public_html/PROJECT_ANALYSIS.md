# Cognisphere LMS — Project Analysis & Local Setup Guide

> **Project name:** Cognisphere LMS (branded as *LMS | Cogniphere*)  
> **Repository origin:** Geons-Logix-Private-Ltd / Cognisphere_LMS  
> **Stack:** Laravel 10 (PHP 8.1+) monolith — server-rendered UI, not a separate React/Vue SPA

---

## 1. What Is This Project?

This is a **Learning Management System (LMS)** for Cognisphere. It supports three primary user roles and covers the full training lifecycle:

| Role | `user_group_id` | Post-login route | Capabilities |
|------|-----------------|------------------|--------------|
| **Admin** | 1 | `/dashboard` | User/group management, courses, content, batches, enrollments, payments, certificates |
| **Learner** | 2 | `/learner` | Enrolled courses, quizzes, assessments, active recall, fees, certificates |
| **Instructor** | 3 | `/instructors` | Course mapping, attendance, assessment review/approval |

### Core feature modules

- **User management** — user groups, permissions, batches, sessions, user accounts
- **Course management** — categories → courses → lessons/units → content (text, video, audio, resources)
- **Enrollment & payments** — enrollments, fee summaries, Razorpay payment gateway
- **Learning activities** — quizzes, assessments, active recall exercises
- **Instructor tools** — attendance marking, assessment review workflow
- **Certificates** — learner certificate generation and approval
- **Dashboard analytics** — Chart.js-based course/learner stats

---

## 2. Architecture Overview

This is a **classic Laravel monolith**. There is no separate frontend API client app. The browser talks directly to Laravel routes; Laravel returns HTML (Blade views) or JSON for a few AJAX calls.

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         BROWSER (User)                                  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                    HTTP (GET/POST with CSRF token)
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  public/index.php  ←── Web server document root (Apache/Nginx/artisan)  │
│       │                                                                 │
│       ▼                                                                 │
│  bootstrap/app.php → Http Kernel → Middleware                           │
│       │                                                                 │
│       ▼                                                                 │
│  routes/web.php  (primary — 300+ routes)                                │
│  routes/api.php  (minimal — register/login only)                        │
│       │                                                                 │
│       ▼                                                                 │
│  Controllers (app/Http/Controllers/)                                    │
│       │                                                                 │
│       ├──► Eloquent Models (app/Models/)                                │
│       │         │                                                       │
│       │         ▼                                                       │
│       │    MySQL / MariaDB                                              │
│       │                                                                 │
│       └──► Blade Views (resources/views/)  →  HTML response             │
│                 +                                                       │
│            Vite assets (CSS/JS via @vite)                               │
└─────────────────────────────────────────────────────────────────────────┘
```

### Frontend vs backend — how they connect

| Layer | Technology | Location |
|-------|------------|----------|
| **UI templates** | Blade (`.blade.php`) | `resources/views/` |
| **Layout shell** | Blade component `<x-base-layout>` | `resources/views/components/base-layout.blade.php` |
| **Styling** | SCSS + Bootstrap 5 | `resources/scss/`, compiled by Vite |
| **JS** | jQuery, fetch, Chart.js, CKEditor | `resources/assets/js/`, inline in Blade |
| **Asset bundler** | Vite 5 | `vite.config.js`, `npm run dev` |
| **Static plugins** | Pre-built CSS/JS | `public/plugins/` |
| **Uploads** | Images, logos, course files | `public/uploads/` |
| **Backend** | Laravel controllers | `app/Http/Controllers/` |
| **ORM** | Eloquent | `app/Models/` (40+ models) |
| **Auth** | Laravel Sanctum + sessions | `auth:sanctum` middleware on protected routes |

**Important:** Most pages use **traditional form POST** (full page reload). Dynamic behavior (cascading dropdowns, quiz submission, status toggles) uses **jQuery `$.ajax()`** or **`fetch()`** against the same Laravel web routes — not a REST API.

---

## 3. Request Flow — From Login to Data on Screen

### Step 1: Entry point

```
User visits  http://localhost:8000/
       │
       ▼
routes/web.php  →  Route::get('/', ...) 
       │
       ▼
return view('admin.login')   →   resources/views/admin/login.blade.php
```

The login form posts to `POST /login` with `@csrf` token.

### Step 2: Authentication

```
POST /login
       │
       ▼
RegisterController::login()   (app/Http/Controllers/API/RegisterController.php)
       │
       ├── Auth::attempt(email, password)
       ├── Creates Sanctum API token (mostly unused for web UI)
       ├── Stores session_id on user (single-session enforcement)
       └── Redirects by role:
              user_group_id=1  →  /dashboard
              user_group_id=2  →  /learner
              user_group_id=3  →  /instructors
```

### Step 3: Protected routes

All authenticated pages sit inside:

```php
Route::middleware(['auth:sanctum', 'single.session'])->group(function () { ... });
```

- **`auth:sanctum`** — ensures user is logged in (session + Sanctum)
- **`single.session`** — logs out if the same account signs in elsewhere (`app/Http/Middleware/SingleSession.php`)

### Step 4: Controller loads data

Example — Admin dashboard:

```
GET /dashboard
       │
       ▼
RegisterController::dashboard()
       │
       ├── UserGroup::where(...)
       ├── User::whereIn(...)
       ├── CourseCategory::where('status', 1)->get()
       ├── CourseBasicInfo::where('status', 1)->get()
       └── DB::table(...) joins for stats
       │
       ▼
return view('admin.dashboard', compact('companies', 'learnerUser', ...))
```

Example — Course categories list:

```
GET /course-category
       │
       ▼
CourseCategoryController::index()
       │
       ├── $categories = CourseCategory::all()
       └── return view('course.category', compact('categories'))
```

### Step 5: Blade renders HTML

Views extend `<x-base-layout>` which loads:
- Vite-compiled SCSS/JS (`@vite([...])`)
- Bootstrap from `public/plugins/`
- Sidebar/navigation based on user role
- Page content with `{{ $categories }}` etc.

### Step 6: AJAX (optional, same request cycle)

Example — learner course content viewer uses jQuery:

```
Browser  →  POST /GetCourseContent  (with CSRF)
                │
                ▼
         LearnerPaymentController::GetCourseContent()
                │
                ▼
         return JSON  →  JavaScript updates DOM
```

---

## 4. Directory Structure (Key Paths)

```
public_html/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # All business logic controllers
│   │   └── Middleware/           # SingleSession, auth, etc.
│   ├── Models/                   # Eloquent models (User, CourseBasicInfo, ...)
│   └── helpers.php               # layoutConfig(), getRouterValue()
├── bootstrap/app.php             # Laravel bootstrap
├── config/                       # App, database, sanctum, fortify configs
├── database/
│   ├── migrations/               # 35+ migration files
│   └── seeders/                  # UserGroup, SuperAdmin, CountryState
├── DB/
│   └── u448933818_lms.sql        # Full production DB dump (~5000 lines)
├── public/                       # Web root (index.php, assets, uploads)
│   ├── index.php                 # HTTP entry point
│   ├── plugins/                  # Bootstrap, charts, datatables, etc.
│   └── uploads/                  # Logos, user uploads
├── resources/
│   ├── views/                    # Blade templates (admin, learner, course, quiz, ...)
│   ├── scss/                     # Theme styles (light/dark)
│   ├── assets/js/                # Page-specific JavaScript
│   └── layouts/                  # Menu layout JS
├── routes/
│   ├── web.php                   # Main application routes
│   └── api.php                   # Minimal API (register/login)
├── storage/                      # Logs, cache, uploaded files
├── vendor/                       # Composer dependencies
├── .env                          # Environment config (DB, mail, Razorpay)
├── artisan                       # Laravel CLI
├── composer.json
├── package.json
└── vite.config.js
```

> **Note:** The repo contains duplicate nested copies under `app/Http/Controllers/app/` and `config_/`. These appear to be accidental deployment artifacts. Laravel uses the top-level `app/`, `config/`, `database/`, `resources/` paths only.

---

## 5. Database

### Engine
MySQL or MariaDB (dump was created on MariaDB 10.11).

### Setup options

#### Option A — Import full dump (recommended for local dev with real data)

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE cognisphere_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import
mysql -u root -p cognisphere_lms < DB/u448933818_lms.sql
```

Then set in `.env`:
```
DB_DATABASE=cognisphere_lms
DB_USERNAME=root
DB_PASSWORD=your_local_password
```

#### Option B — Fresh install via migrations + seeders

```bash
php artisan migrate
php artisan db:seed
```

This creates empty tables plus:
- 3 user groups (Admin, Learner, Instructor)
- Super admin: `admin@gmail.com` / `password`
- Country/state reference data

### Main tables (40+)

| Table | Purpose |
|-------|---------|
| `users` | Login accounts linked to `user_groups` |
| `user_groups` | Role definitions (Admin, Learner, Instructor) |
| `user_group_permissions` | CRUD permissions per group |
| `user_account_basic_infos` | Extended user profile |
| `course_categories` | Course taxonomy |
| `course_basic_infos` | Course master records |
| `course_lesson_basic_infos` | Units/lessons within courses |
| `contents` | Text/HTML lesson content |
| `video_contents` / `audio_contents` | Media content |
| `resource_contents` | Downloadable resources |
| `batches` / `course_batches` | Batch scheduling |
| `sessions` | Training sessions |
| `enrollments` | Learner ↔ course enrollment |
| `course_completed_trackings` | Progress tracking |
| `quiz_details` / `quiz_questions` | Quizzes |
| `assessment_details` / `assessment_questions` / `assessment_answers` | Assessments |
| `activerecall_details` / `activerecall_questions` | Active recall module |
| `attendance` | Attendance records |
| `learner_fees_summaries` / `learner_fees_transactions` | Fee management |
| `lead_payment` / `payments` | Payment records |
| `learner_certificates` / `certificate_details` | Certificates |
| `countries` / `states` / `cities` | Location reference |
| `personal_access_tokens` | Sanctum API tokens |

---

## 6. Prerequisites to Run Locally (Windows)

Your machine currently does **not** have PHP in PATH. Install the following:

| Requirement | Version | Notes |
|-------------|---------|-------|
| **PHP** | 8.1+ | Extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` |
| **Composer** | 2.x | PHP dependency manager |
| **MySQL / MariaDB** | 8.x / 10.x | Database server |
| **Node.js** | 18+ | For Vite asset compilation |
| **npm** | 9+ | Comes with Node |

### Recommended Windows stacks (pick one)

- **[Laragon](https://laragon.org/)** — easiest for Laravel (PHP + MySQL + Apache in one)
- **XAMPP** — Apache + MySQL + PHP
- **WAMP** — Windows Apache MySQL PHP

---

## 7. Local Setup — Step by Step

### 7.1 Clone / open project

You already have the project at:
```
c:\Users\aakas\Downloads\public_html
```

### 7.2 Install PHP dependencies

```bash
cd c:\Users\aakas\Downloads\public_html
composer install
```

(`vendor/` already exists; run again if needed.)

### 7.3 Configure environment

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env` for local:

```env
APP_NAME=LmsCognisphere
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cognisphere_lms
DB_USERNAME=root
DB_PASSWORD=

# Optional — only needed for payment testing
RAZORPAY_KEY_ID=your_test_key
RAZORPAY_KEY_SECRET=your_test_secret
```

> **Security warning:** The bundled `.env` contains production database credentials, mail passwords, and live Razorpay keys. **Change all of these for local use** and never commit secrets to git.

### 7.4 Database setup

**Recommended — import dump:**

```bash
mysql -u root -p -e "CREATE DATABASE cognisphere_lms;"
mysql -u root -p cognisphere_lms < DB/u448933818_lms.sql
```

**Or fresh migrations:**

```bash
php artisan migrate
php artisan db:seed
```

### 7.5 Storage link (for file uploads)

```bash
php artisan storage:link
```

Or visit `http://localhost:8000/link_storage` once while the server is running.

### 7.6 Install & build frontend assets

```bash
npm install
npm run dev
```

Keep `npm run dev` running in a separate terminal during development (Vite hot reload).

For production-style assets:
```bash
npm run build
```

### 7.7 Start Laravel

```bash
php artisan serve
```

Open: **http://localhost:8000**

### 7.8 Login credentials

| Source | Email | Password |
|--------|-------|----------|
| SQL dump (user id 1) | `admin@gmail.com` | Unknown — bcrypt hash in DB; reset with `php artisan tinker` |
| Seeder (`SuperAdminTableSeeder`) | `admin@gmail.com` | `password` |
| README (outdated) | `superadmin@gmail.com` | `@Kpm7908` |

**Reset admin password locally:**

```bash
php artisan tinker
>>> \App\Models\User::where('email','admin@gmail.com')->update(['password' => bcrypt('password')]);
```

Other users exist in the SQL dump (learners, instructors) if you import it.

---

## 8. Web Server Configuration

### Development (artisan serve)

Document root is handled automatically. No extra config needed.

### Apache (XAMPP/Laragon)

Point virtual host document root to:
```
c:\Users\aakas\Downloads\public_html\public
```

The root `.htaccess` redirects requests to `public/`:
```apache
RewriteRule ^(.*)$ public/$1 [L]
```

### Required Apache modules
- `mod_rewrite` enabled

---

## 9. Key Controllers & Routes Map

| Controller | Responsibility | Example routes |
|------------|----------------|----------------|
| `RegisterController` | Login, logout, dashboards | `/login`, `/dashboard`, `/learner` |
| `UserManagementController` | Users, groups, batches, sessions | `/usergroup`, `/Batch`, `/createuseraccount` |
| `CourseCategoryController` | Course categories CRUD | `/course-category` |
| `CourseBasicInfoController` | Courses CRUD | `/course-basic-info`, `/view-course-basic-info` |
| `CourseLessonBasicInfoController` | Lessons/units | `/course-lesson-basic-info` |
| `ContentController` | Text/video/audio content | `/content`, `/video-content`, `/list-video` |
| `LearnerPaymentController` | Enrollments, fees, learner portal | `/mycourses`, `/Enrollment`, `/payfee` |
| `QuizController` | Quiz CRUD and taking | `/addquiz`, `/myquiz`, `/quizwrite/{id}` |
| `AssessmentController` | Assessments | `/addassessment`, `/my_assessment` |
| `ActiveRecallController` | Active recall exercises | `/addactiverecall`, `/my_activerecall` |
| `InstructorController` | Instructor portal | `/instructors`, `/mapping`, `/assessmentReview` |
| `AttendanceController` | Attendance | `/attendaceInstructor`, `/markattendance` |
| `PaymentController` | Razorpay payments | `/payment`, `/payment/callback` |
| `ChartjsController` | Dashboard charts | `/DashboardCourseCharts` |
| `ResourceController` | Downloadable resources | `/listresources`, `/download/{id}` |

---

## 10. Third-Party Integrations

| Service | Package / Config | Usage |
|---------|------------------|-------|
| **Laravel Sanctum** | `laravel/sanctum` | API token auth (minimal API use) |
| **Laravel Fortify** | `laravel/fortify` | Auth scaffolding (installed) |
| **Razorpay** | `razorpay/razorpay` | Course fee payments (`RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET` in `.env`) |
| **SMTP Mail** | Laravel Mail | Verification emails, notifications |
| **CKEditor 5** | npm package | Rich text content editing |
| **Chart.js** | public plugins | Dashboard analytics |

---

## 11. Common Issues & Fixes

| Problem | Fix |
|---------|-----|
| `php` not recognized | Install PHP via Laragon/XAMPP; add PHP to system PATH |
| 500 error after setup | `php artisan config:clear && php artisan cache:clear` |
| CSS/JS not loading | Run `npm run dev` or `npm run build`; check `@vite` directives |
| Images/uploads 404 | `php artisan storage:link`; verify `public/uploads/` exists |
| DB connection refused | Start MySQL; verify `.env` DB credentials |
| Logged out immediately | `single.session` middleware — clear `session_id` in users table or use one browser |
| Session errors | `php artisan session:table` if using database sessions; ensure `storage/` is writable |
| Migration vs dump mismatch | Prefer SQL dump for this project — migrations may not include all production tables |

---

## 12. Development Workflow Summary

```
Terminal 1:  php artisan serve          →  http://localhost:8000
Terminal 2:  npm run dev                →  Vite dev server (hot reload CSS/JS)
MySQL:       running with imported DB
```

**Typical change flow:**
1. Edit controller logic in `app/Http/Controllers/`
2. Edit Blade view in `resources/views/`
3. Edit styles in `resources/scss/` (Vite recompiles automatically)
4. Refresh browser

---

## 13. API Routes (Secondary)

`routes/api.php` exposes only:

```
POST /api/register
POST /api/login
GET  /api/user  (auth:sanctum)
```

The web application does **not** depend on these for normal browser usage. The main app uses `routes/web.php` exclusively.

---

## 14. Quick Reference — User Role Routing

```
Login (POST /login)
    │
    ├─ user_group_id = 1 (Admin)      → /dashboard
    ├─ user_group_id = 2 (Learner)    → /learner
    ├─ user_group_id = 3 (Instructor) → /instructors
    └─ default                        → /learner
```

---

*Generated: June 26, 2026 — for local setup of Cognisphere LMS*
