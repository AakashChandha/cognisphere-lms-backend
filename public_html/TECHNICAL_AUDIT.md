# Cognisphere LMS — Complete Technical Audit

**Audit scope:** Codebase at `c:\Users\aakas\Downloads\public_html`  
**Method:** Static analysis only. No code was modified.  
**Auditor role:** Senior software architect / Laravel expert  

---

## 1. Project Overview

### What does this application do?

This is **Cognisphere LMS** — a multi-role Learning Management System for:

- **Admins:** user/group management, courses, content, batches, enrollments, fees, certificates
- **Learners:** enrolled courses, content consumption, quizzes, assessments, active recall, payments, certificates
- **Instructors:** course mapping, attendance, assessment review/approval workflows

Branding in views: **"LMS | Cogniphere"**.

### Laravel version

**Laravel 10.48.3** (locked in `composer.lock`; `composer.json` requires `^10.10`).

### PHP version

**PHP ^8.1** required (`composer.json`). Exact runtime version on Hostinger: **Unknown** (not determinable from codebase).

### Major dependencies and packages

| Package | Version constraint | Purpose |
|---------|-------------------|---------|
| `laravel/framework` | ^10.10 (v10.48.3) | Core framework |
| `laravel/sanctum` | ^3.3 | API token + SPA auth |
| `laravel/fortify` | ^1.21 | Auth scaffolding (installed) |
| `doctrine/dbal` | ^3.7 | DB schema tools |
| `guzzlehttp/guzzle` | ^7.2 | HTTP client |
| `razorpay/razorpay` | ^2.9.0 | Payment gateway |
| `laravel/tinker` | ^2.8 | REPL |

**Dev:** PHPUnit 10, Laravel Pint, Sail, Faker, Collision, Ignition.

**Frontend (npm):** Vite 5, Bootstrap 5.3, Sass, Axios, Lodash, CKEditor 5 Classic.

### Frontend technology

| Technology | Used? | Evidence |
|------------|-------|----------|
| **Blade** | Yes | Primary UI — `resources/views/` (~223 templates) |
| **Vue** | No | Not in `package.json` or views |
| **React** | No | Not in `package.json` or views |
| **jQuery** | Yes | Inline in Blade views + `public/plugins/` |
| **Bootstrap 5** | Yes | `package.json` + `public/plugins/bootstrap/` |
| **Vite** | Yes | `vite.config.js` — SCSS/JS bundling |
| **CKEditor 5** | Yes | Rich text content editing |
| **Chart.js / Apex** | Yes | Dashboard charts via `public/plugins/apex/` |
| **Admin theme** | Yes | Cork-style theme with ~105 demo pages under `resources/views/pages/` |

### Database engine

**MySQL** (default `DB_CONNECTION=mysql` in `config/database.php`).  
Production SQL dump (`DB/u448933818_lms.sql`) was generated on **MariaDB 10.11.10**.

---

## 2. Project Structure

### Important folders

| Folder | Purpose |
|--------|---------|
| `public/` | Web document root — `index.php`, static assets, `uploads/`, `plugins/` |
| `routes/` | `web.php` (211 routes), `api.php` (4 route declarations) |
| `app/Http/Controllers/` | 16 business controllers (~5,000+ lines total) |
| `app/Http/Controllers/API/` | `RegisterController`, `BaseController` — auth + JSON helpers |
| `app/Http/Middleware/` | CSRF, auth, **`SingleSession`** |
| `app/Models/` | 41 Eloquent models |
| `app/Mail/` | `VerificationEmail`, `WelcomeEmail`, `EnrollmentEmail`, `InvalidIdProofEmail` |
| `app/Actions/Fortify/` | Fortify user actions (installed, lightly used) |
| `app/View/Components/` | Blade layout/widget components (incl. RTL duplicates) |
| `app/helpers.php` | `layoutConfig()`, `getRouterValue()` |
| `resources/views/` | Blade templates — LMS + theme demos |
| `resources/scss/`, `resources/assets/js/` | Vite-compiled theme assets |
| `database/migrations/` | 35 migration files |
| `database/seeders/` | `UserGroupSeeder`, `SuperAdminTableSeeder`, `CountryStateSeeder` |
| `DB/` | Full production SQL dump |
| `config/` | Laravel configuration |
| `storage/` | Logs, cache, framework files |
| `vendor/` | Composer dependencies |
| `node_modules/` | npm dependencies |

### Anomaly: duplicate nested trees

The repo contains **duplicate copies** of application code:

- `app/Http/Controllers/app/` (full nested Laravel tree)
- `app/Http/Controllers/database/`
- `app/Http/Controllers/resources/`
- `config_/` (duplicate config folder)

Laravel loads only the **top-level** `app/`, `routes/`, `database/`, `resources/`, `config/`. The nested copies are **deployment artifacts / accidental duplication** and create confusion.

### Entry points and request flow

```
HTTP Request
    │
    ▼
public/index.php
    │
    ▼
bootstrap/app.php → Http\Kernel
    │
    ├── Global middleware (TrustProxies, CORS, maintenance, trim, etc.)
    │
    ├── RouteServiceProvider
    │       ├── routes/api.php  → prefix /api, middleware "api"
    │       └── routes/web.php  → middleware "web" (session, CSRF)
    │
    ▼
Controller method
    │
    ├── Eloquent / DB::table()
    └── return view() | redirect() | response()->json()
```

**Primary entry:** `GET /` → `admin.login` Blade view.  
**Auth gate:** `middleware(['auth:sanctum', 'single.session'])` wraps ~200 routes.

---

## 3. Features

| # | Feature | Purpose | Main files | Status |
|---|---------|---------|------------|--------|
| 1 | **Authentication / Login** | Email/password login, role redirect | `RegisterController`, `routes/web.php`, `admin/login.blade.php` | **Complete** |
| 2 | **Single-session enforcement** | One active session per user | `SingleSession` middleware, `users.session_id` | **Complete** |
| 3 | **Admin dashboard** | Stats, charts, overview | `RegisterController::dashboard`, `ChartjsController`, `admin/dashboard.blade.php` | **Complete** |
| 4 | **Learner dashboard** | Course progress, assessments summary | `RegisterController::learner`, `learner/` views | **Complete** |
| 5 | **Instructor dashboard** | Instructor landing | `RegisterController` redirect, `InstructorController` | **Complete** |
| 6 | **User groups** | Admin/Learner/Instructor groups | `UserManagementController`, `UserGroup`, `UserGroupPermission` | **Complete** |
| 7 | **User accounts CRUD** | Create/edit/delete users | `UserManagementController`, `userManagement/` views | **Complete** |
| 8 | **Permissions UI** | Group/account permission flags | `UserManagementController`, `UserGroupPermission` | **Partial** — stored in DB; route-level enforcement not found |
| 9 | **Batches & sessions** | Training batch scheduling | `UserManagementController`, `Batch`, `Session` models | **Complete** |
| 10 | **Public registration** | Learner self-registration | `LearnerPaymentController::registration`, `UserManagementController::userregistration` | **Complete** |
| 11 | **Course categories** | Category CRUD | `CourseCategoryController`, `course/category.blade.php` | **Complete** |
| 12 | **Course management** | Course CRUD, status toggle | `CourseBasicInfoController`, `CourseBasicInfo` | **Complete** |
| 13 | **Lessons/units** | Lesson CRUD, cascading selectors | `CourseLessonBasicInfoController` | **Complete** |
| 14 | **Text content** | HTML/text lesson content | `ContentController`, `Content` model | **Complete** |
| 15 | **Video content** | Video upload & listing | `ContentController`, `VideoContent` | **Complete** |
| 16 | **Audio content** | Audio upload & listing | `ContentController`, `AudioContent` | **Complete** — model exists; no migration in `database/migrations/` |
| 17 | **Resources/downloads** | Downloadable files | `ResourceController`, `ResoureseModel` | **Complete** |
| 18 | **Course content viewer** | Learner content consumption + tracking | `ContentController`, `LearnerPaymentController`, `courseview.blade.php` | **Complete** |
| 19 | **Progress tracking** | Per-content completion | `CourseCompletedTracking`, `courses_tracks` | **Complete** |
| 20 | **Enrollment** | Admin + online enrollment | `LearnerPaymentController`, `Entrollment` | **Complete** |
| 21 | **Fee management** | Fee summaries, transactions | `LearnerPaymentController`, `Learnerfeessummary` | **Complete** |
| 22 | **Razorpay payments** | Online course payment | `PaymentController`, `Payment`, `LeadPayment` | **Partial** — `paymentCallback()` is empty; order creation commented out; `paymentupdate` marks paid without gateway verification |
| 23 | **Quizzes** | Quiz CRUD, learner quiz taking | `QuizController`, `QuizDetails`, `QuizQuestion` | **Complete** |
| 24 | **Assessments** | Assessment CRUD, settings, learner write, instructor review | `AssessmentController`, `InstructorController` | **Complete** |
| 25 | **Active recall** | Active recall exercises | `ActiveRecallController`, `ActiveRecallDetails` | **Partial** — `saveactiverecallWrite` and `activerecall_rewrite` routes are **commented out** in `web.php` |
| 26 | **Attendance** | Mark/view attendance | `AttendanceController`, `Attendance` | **Complete** |
| 27 | **Instructor mapping** | Map instructors to courses/batches | `InstructorController`, `MapCourse` / `course_batches` | **Complete** |
| 28 | **Certificates** | Generate, approve, learner view | `LearnerPaymentController`, `LearnerCertificate` | **Complete** |
| 29 | **Email notifications** | Welcome, verification, enrollment | `app/Mail/*`, controllers | **Partial** — mailables exist; full trigger coverage **Unknown** |
| 30 | **PDF/document viewer** | In-browser document viewing | `ContentController::docviewer`, `pdf-viewer` route | **Complete** |
| 31 | **Country/state/city** | Location reference data | `Country`, `State`, `City`, `CountryStateSeeder` | **Complete** |
| 32 | **Site configuration** | Site settings | `SiteConfiguration` model | **Unknown** — model/migration exist; UI usage not fully traced |
| 33 | **Fortify 2FA** | Two-factor auth | Fortify migration on `users` table | **Partial** — columns exist; login uses custom controller, 2FA usage **Unknown** |
| 34 | **REST API** | Mobile/external API | `routes/api.php` | **Partial** — only register/login/user endpoints |
| 35 | **Theme demo pages** | Cork admin template demos | `resources/views/pages/` (105 views) | **Dead code** — not wired to LMS routes |

---

## 4. Database Analysis

### Tables from migrations (`database/migrations/` — 35 files)

`users`, `user_groups`, `user_group_permissions`, `user_account_permissions`, `user_account_basic_infos`, `password_reset_tokens`, `personal_access_tokens`, `failed_jobs`, `site_configurations`, `course_categories`, `course_basic_infos`, `course_lesson_basic_infos`, `contents`, `video_contents`, `resource_contents`, `batches`, `course_batches`, `sessions`, `enrollments`, `learner_fees_summaries`, `learner_fees_transactions`, `learner_certificates`, `certificate_details`, `course_completed_trackings`, `course_completions`, `courses_tracks`, `attendance`, `quiz_details`, `quiz_questions`, `assessment_details`, `assessment_questions`, `payments`, `countries`, `states`, `cities`

Plus Fortify: `two_factor_*` columns on `users`.

### Tables in production dump but NOT in migrations

From `DB/u448933818_lms.sql` (schema drift):

`activerecall_details`, `activerecall_questions`, `assessment_answers`, `assessment_resubmit`, `assessment_setting`, `audio_contents`, `corporate_enrollments`, `lead_payment`

**Implication:** `php artisan migrate` on a fresh DB will **not** reproduce production schema. The SQL dump is the source of truth for production.

### Entity relationships (core)

```
user_groups ──< users
users ──< user_account_basic_infos
users ──< enrollments >── course_basic_infos
course_categories ──< course_basic_infos ──< course_lesson_basic_infos
course_basic_infos ──< contents | video_contents | audio_contents
course_basic_infos ──< quiz_details ──< quiz_questions
course_basic_infos ──< assessment_details ──< assessment_questions
assessment_details ──< assessment_answers (production only)
users ──< course_completed_trackings
batches ──< course_batches (instructor mapping)
batches ──< enrollments
```

### Important entities

- **`users`** — auth + `user_group_id` + `session_id`
- **`course_basic_infos`** — central course entity
- **`enrollments`** — learner ↔ course ↔ batch link
- **`course_completed_trackings`** — learning progress
- **`assessment_*` / `quiz_*`** — evaluation modules
- **`lead_payment` / `payments`** — payment state

### Design problems

| Issue | Detail |
|-------|--------|
| **Schema drift** | Production has 9+ tables not in migrations |
| **Naming inconsistency** | `Entrollment` model (typo), `ResoureseModel`, `AssessmentQuetion`, migration file `mappingcourse` creates `course_batches` |
| **Duplicate model files** | `VideoContent31_03_26.php`, `ContentController31_03_26.php` |
| **Business logic in models** | e.g. `CourseBasicInfo::calculateCompletionPercentage()` runs queries in model |
| **Weak FK coverage** | Some production tables lack migration-defined foreign keys |
| **`users.role` vs `user_group_id`** | Both exist; login uses `user_group_id` only |
| **GET routes for destructive actions** | e.g. `delete-course-category/{id}`, `deleteuseraccount/{id}` |
| **Permissions not normalized** | Boolean flags per action; no policy layer |

---

## 5. API Analysis

### Route counts

| File | Count |
|------|-------|
| `routes/web.php` | **211** `Route::` declarations |
| `routes/api.php` | **4** `Route::` declarations |

### REST API routes (`/api/*`)

| Method | Path | Controller | Auth |
|--------|------|------------|------|
| POST | `/api/register` | `RegisterController::register` | None |
| POST | `/api/login` | `RegisterController::login` | None |
| POST | `/api/users` | `RegisterController::login` | None |
| GET | `/api/user` | Closure | `auth:sanctum` |

**Note:** `RegisterController::login` returns **redirects** for web and JSON for API — dual behavior in one method.

### Web routes grouped by module

| Module | Approx. routes | Pattern |
|--------|----------------|---------|
| Auth / public | ~8 | Views + `POST /login` |
| User management | ~25 | CRUD + permissions + batches/sessions |
| Course categories | 5 | CRUD |
| Courses | 6 | CRUD + status |
| Lessons | 8 | CRUD + AJAX helpers |
| Content (text/video/audio) | ~20 | CRUD + learner AJAX |
| Enrollment & fees | ~20 | CRUD + AJAX |
| Payments | 5 | Razorpay + manual update |
| Instructor | ~20 | Mapping, assessment review |
| Attendance | 4 | Search + mark |
| Learner portal | ~15 | Courses, profile, payments |
| Quiz | ~15 | CRUD + take quiz |
| Assessment | ~18 | CRUD + write + settings |
| Active recall | ~12 | CRUD (2 learner routes disabled) |
| Resources | 4 | Upload + download |
| Dashboard/charts | 2 | HTML + JSON chart data |
| Utility (unsafe) | 2 | `/link_storage`, `/clear-cache` — **no auth** |

### JSON vs HTML on web routes

- **~90%** return Blade views or redirects
- **~10%** return JSON (cascading dropdowns, quiz/assessment AJAX, chart data, payment store/failure)

There is **no** formal REST API versioning, no API resource transformers, no OpenAPI spec.

### Authentication method

| Context | Method |
|---------|--------|
| Web routes | Session (via `web` middleware group) + `auth:sanctum` |
| API routes | Sanctum bearer tokens |
| CSRF | Required on web POST |
| Fortify | Installed; **login not routed through Fortify views** — custom `RegisterController` used |

---

## 6. Authentication & Authorization

### Login flow

1. `GET /` or `/login` → `admin.login` Blade form
2. `POST /login` → `RegisterController::login`
3. `Auth::attempt(email, password)`
4. Sanctum token created (stored; web UI primarily uses session)
5. Previous `session_id` destroyed (single session)
6. New `session_id` saved on user record
7. Redirect by `user_group_id`:
   - `1` → `/dashboard`
   - `2` → `/learner`
   - `3` → `/instructors`
   - else → `/learner`

`$request->session()->regenerate()` is **commented out**.

### Roles

| `user_group_id` | Role (from seeder) |
|-----------------|-------------------|
| 1 | Admin |
| 2 | Learner |
| 3 | Instructor |

Additional groups (e.g. "Company") referenced in dashboard code — **data-dependent**.

### Permissions

- `user_group_permissions` — CRUD flags per group
- `user_account_permissions` — per-user overrides
- Managed via `UserManagementController`

**Authorization enforcement:** No Laravel Policies, no `Gate::`, no `authorize()`, no permission middleware found in `app/`. Any authenticated user in the middleware group can access **all** protected routes regardless of role or permission flags. Role separation is **UI/navigation only** (post-login redirect), not route-level RBAC.

### Security mechanisms present

- CSRF on web forms
- Password hashing (bcrypt / `hashed` cast)
- Sanctum tokens
- `SingleSession` middleware
- Fortify rate limiting on login (5/min) — applies to Fortify routes; custom login path **may not use this**
- HTTPS: **Unknown** (deployment config)

---

## 7. Code Quality

### Overall rating: **3.5 / 10**

| Issue | Evidence |
|-------|----------|
| **Duplicate code** | Entire nested `app/Http/Controllers/app/` tree; `ContentController` vs `ContentController31_03_26`; RTL component duplicates; `PaymentController::storeOLD` |
| **Large controllers** | `LearnerPaymentController` (~768 lines), `UserManagementController` (~718), `InstructorController` (~712), `ContentController` (~677) |
| **Business logic in controllers** | Pervasive — DB queries, validation, mail, file upload, payment logic all in controllers |
| **Missing validation** | Many methods use raw `$request->input()` without `$request->validate()`; inconsistent `Validator::make` usage |
| **Poor naming** | `Entrollment`, `ResoureseModel`, `AssessmentQuetion`, `attendaceInstructor`, `serachentrollment`, `Transction` |
| **Dead code** | 105 theme demo views; commented routes; `storeOLD`; duplicate controllers/models with date suffixes |
| **No service layer** | No `app/Services/` directory |
| **No Form Requests** | None found in `app/` |
| **No Policies** | `AuthServiceProvider::$policies` is empty |
| **No application tests** | Only `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` (scaffold) |
| **Raw SQL** | Extensive `DB::table()` joins alongside Eloquent |
| **Debug artifacts** | Commented `dd()`, `log::info` in login |

---

## 8. Architecture

### Pattern

**Monolithic MVC** — Laravel's default, but **incomplete MVC**:

- **Models:** Thin-to-medium Eloquent models (some business logic)
- **Views:** Blade templates with inline JS
- **Controllers:** Fat — act as model, service, and presenter

### Layering

| Layer | Present? |
|-------|----------|
| Controllers | Yes |
| Models (Eloquent) | Yes |
| Views (Blade) | Yes |
| Services | **No** |
| Repositories | **No** |
| DTOs / API Resources | **No** |
| Events / Listeners | Minimal |
| Jobs / Queues | `QUEUE_CONNECTION=sync` in `.env.example`; no app jobs found |
| Policies | **No** |

### Coupling

**Tightly coupled.** Controllers directly query DB, return views, send mail, handle uploads. Views contain AJAX URLs hardcoded to web routes. No API boundary.

### Scalability

| Factor | Assessment |
|--------|------------|
| Horizontal scaling | Poor — file sessions, local uploads in `public/uploads/` |
| Database | Single MySQL; N+1 and loop queries in controllers (e.g. `calculateCompletionPercentage`) |
| Caching | Not used in application code |
| Queue | Sync only |
| CDN | Static assets served from app server |

**Would scale vertically on Hostinger shared/VPS for moderate load; would struggle at high concurrency without refactoring.**

---

## 9. Frontend

### How it is built

- **Server-rendered Blade** pages extending `<x-base-layout>`
- **Vite** compiles SCSS + per-page JS
- **Bootstrap 5** for layout/components
- **jQuery / fetch** for partial interactivity
- **CKEditor 5** for rich text
- **Static plugins** in `public/plugins/` (apex, datatables, sweetalert2, flatpickr, filepond, fullcalendar, etc.)

### JS libraries (confirmed)

jQuery (via plugins/global), Axios (npm), Lodash, CKEditor 5, Chart.js/ApexCharts, Bootstrap JS, SweetAlert2, DataTables, Flatpickr, FilePond, FullCalendar, and others in `public/plugins/`.

### CSS framework

**Bootstrap 5.3** + custom SCSS theme (light/dark, multiple menu layouts).

### React replacement feasibility (keeping Laravel backend)

**Practical — but only after building a Laravel API layer.**

| Factor | Assessment |
|--------|------------|
| Current API coverage | ~2% of functionality |
| AJAX patterns already exist | Yes — ~10% of interactions |
| Auth | Would need Sanctum SPA cookie auth or JWT |
| File uploads | Need API endpoints + storage strategy |
| Effort to add API | 3–5 person-months before React work begins |
| React frontend build | 4–6 person-months for feature parity |

**Verdict:** Replacing frontend with React while keeping Laravel is **feasible and the lowest-risk modernization path**, but it is **not** a quick swap — it requires a substantial API layer first.

---

## 10. Backend

### Business logic organization

All logic lives in **16 controller classes** under `app/Http/Controllers/`, with supporting **41 Eloquent models** and occasional **raw `DB::table()` queries**. Mail classes handle email formatting only.

No domain services, no command bus, no event-driven workflows.

### Rewriting in FastAPI or Node.js

| Factor | Difficulty |
|--------|------------|
| Lines of controller logic | ~5,000+ to port |
| Route surface | 211 web + JSON endpoints to reimplement |
| Schema drift | Must reverse-engineer from SQL dump |
| File uploads | Multiple content types |
| Razorpay | Payment capture + webhooks |
| Assessment workflow | Multi-step instructor review — complex state |
| Hidden business rules | In controller conditionals — easy to miss |

**Difficulty: High.** This is a full backend rewrite, not a framework swap.

### Migration effort estimate

| Target | Effort |
|--------|--------|
| Node.js (NestJS/Express) + Prisma | **9–12 person-months** |
| FastAPI + SQLAlchemy | **9–12 person-months** |
| Laravel API layer only (keep PHP) | **3–5 person-months** |

---

## 11. Reusability Classification

| Component | Verdict | Reason |
|-----------|---------|--------|
| MySQL schema / data | **Keep** (refactor) | Works in production; fix naming, add missing migrations |
| Eloquent models | **Refactor** | Useful structure; fix typos, move logic out |
| Controllers | **Refactor** | Logic is valuable but must be split into services |
| Blade LMS views (~112) | **Rewrite** (if going React) or **Keep** (if staying Laravel) |
| Theme demo pages (105) | **Rewrite** → delete | Not used |
| `routes/web.php` | **Refactor** | Split by module; add route groups + middleware per role |
| `routes/api.php` | **Rewrite** | Needs full REST surface |
| Auth (`RegisterController`) | **Refactor** | Consolidate with Fortify or dedicated AuthService |
| Permissions system | **Refactor** | Add Policies + middleware enforcement |
| `SingleSession` middleware | **Keep** | Works; could move to Redis sessions at scale |
| Mail classes | **Keep** | Minor updates |
| Razorpay integration | **Refactor** | Complete callback; remove `storeOLD`; verify signatures |
| Vite/SCSS theme | **Keep** or discard | Keep if staying Blade; discard if React + component library |
| `public/plugins/` | **Keep** (Blade) / **Rewrite** (React) | |
| Nested duplicate `app/Http/Controllers/app/` | **Rewrite** → delete | Accidental duplication |
| `ContentController31_03_26` | **Rewrite** → delete | Dead duplicate |
| Seeders | **Keep** | Useful for dev |
| SQL dump `DB/` | **Keep** | Production reference |
| Tests | **Rewrite** | Scaffold only |
| Fortify setup | **Refactor** | Either use fully or remove |

---

## 12. Risks

### Technical risks

| Risk | Severity |
|------|----------|
| Schema drift (migrations ≠ production) | **High** |
| No automated tests | **High** |
| Fat controllers — regression on any change | **High** |
| Duplicate code trees causing wrong-file edits | **Medium** |
| Sync queue — mail failures block requests | **Medium** |
| Local file storage — not cloud-portable | **Medium** |

### Security concerns

| Concern | Evidence |
|---------|----------|
| **Unauthenticated utility routes** | `GET /link_storage`, `GET /clear-cache` run Artisan commands |
| **No route-level RBAC** | Any logged-in user can hit any protected route |
| **GET for delete operations** | CSRF not applicable; crawlers can trigger deletes |
| **Secrets in `.env`** | Production DB, mail, Razorpay keys present in repo `.env` |
| **Incomplete payment verification** | `paymentCallback()` empty; `paymentupdate` marks paid manually |
| **Session regeneration disabled** | Commented out in login |
| **File uploads to `public/uploads/`** | Extension validation **Unknown** per endpoint |
| **Live Razorpay keys in `.env`** | `rzp_live_*` observed |

### Deployment concerns

| Concern | Detail |
|---------|--------|
| Hostinger shared hosting | PHP only; Node.js not supported on shared plans |
| Document root | Must point to `public/` |
| `storage/` and `bootstrap/cache/` permissions | Must be writable |
| Vite build | Requires `npm run build` before deploy (or dev server locally) |
| SQL dump vs migrations | Deploy must use dump or sync migrations manually |

---

## 13. Recommendation

### **A. Continue with Laravel**

**Why:**

1. **Production system** — live on Hostinger with real users and data; rewrite risk is high.
2. **Laravel 10 / PHP 8.1** — modern enough; no framework obsolescence forcing migration.
3. **Database is sound** — schema has issues but is **keepable**; option C (full DB rewrite) adds risk with no clear benefit.
4. **No API layer exists** — option B (rewrite backend + frontend) duplicates years of embedded business logic.
5. **Hostinger compatibility** — Laravel runs natively; Node.js would require infrastructure change.
6. **Cost/benefit** — refactoring within Laravel (services, policies, API module-by-module, optional React later) delivers modernization at **fraction** of rewrite cost.

**Recommended phased plan within option A:**

1. Delete duplicate nested trees and dead files
2. Add migrations for missing production tables
3. Introduce Policies + role middleware
4. Extract Service classes from fat controllers
5. Secure/remove public Artisan routes
6. Add Laravel API (Sanctum SPA) module by module
7. Optionally add React for learner portal only (highest traffic)

**Option B** only if there is a **mandate** to leave PHP and budget for 9–12 person-months.  
**Option C** is **not recommended** — the database works and holds production history.

---

## 14. Estimated Complexity

| Metric | Value |
|--------|-------|
| **Project size** | **Large** |
| **Functional modules** | **~16–18** |
| **Web routes** | **211** |
| **Controllers** | **16** (active) |
| **Models** | **41** |
| **DB tables (production)** | **~44** |
| **Blade views (LMS)** | **~112** (+ 105 theme demos) |
| **Rewrite effort (full stack)** | **9–12 person-months** (2-person team: ~5–7 months) |
| **Laravel API + React (learner only)** | **4–6 person-months** |
| **Laravel refactor only** | **2–4 person-months** |

---

## Executive Summary

| Dimension | Score | Notes |
|-----------|-------|-------|
| **Overall architecture** | **4 / 10** | Monolithic fat-controller pattern; no service/API layer; duplicate trees |
| **Maintainability** | **3 / 10** | No tests, schema drift, naming issues, 700-line controllers |
| **Security** | **4 / 10** | No RBAC enforcement, public Artisan routes, GET deletes, payment gaps |
| **Scalability** | **4 / 10** | Sync queue, local files, query patterns won't scale horizontally |
| **UX readiness** | **5 / 10** | Functional Blade UI; dated patterns; not SPA-ready; 105 unused demo pages |
| **Confidence recommending full rewrite** | **Low–Medium (35%)** | Business logic is embedded and untested; production DB/schema drift increases rewrite failure risk |

### Bottom line

This is a **working production LMS** built as a **classic Laravel monolith**. Laravel acts as **both backend and frontend server** — it does **not** meaningfully expose a REST API today. Continuing with Laravel and modernizing incrementally (API layer → optional React) is the **architecturally sound, lowest-risk path**. A full React + Node.js/FastAPI rewrite is **9–12 person-months** of work and should only be undertaken with clear business justification and dedicated QA.
