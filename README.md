# ILBC Management System

Installation, Loading, Billing & Closure Management System — a Laravel 11 / MySQL 8 / Bootstrap 5 ERP covering the full flow:

**Sales Entry → Billing Clearance → Reviewer Approval → Vendor Selection → Loading/Installation → Audit → Billing/Invoice → Closure**

with role-based permissions, multi-vendor SKU pricing, automatic cost comparison, cost snapshots, audit-trail logging, SLA tracking and reporting — built from the full 50-section master specification for this project.

This guide covers a **complete fresh install on cPanel**, both ways a cPanel account can be set up:

- **Path A — Terminal/SSH available** (cPanel's own *Terminal* icon counts — you don't need real SSH, just a command line on the account). Every command from an empty hosting account to a working login is listed below.
- **Path B — File Manager/FTP only, no command line at all.** The app ships its own guided web installer for exactly this case.

Skip to whichever path matches your host. If you're not sure, check cPanel for a **Terminal** icon (under *Advanced*) — if it's there, use Path A, it's faster.

---

## 0. What you need before you start

- A local machine with **PHP 8.2+** and **Composer** installed, to run one command in step 1. You do **not** need PHP/Composer installed *on the hosting account itself* for Path B; Path A needs PHP (but not Composer) on the account, which cPanel always provides.
- A cPanel hosting account with:
  - PHP 8.2 or newer selected (cPanel → **Select PHP Version** / **MultiPHP Manager**)
  - The PHP extensions: `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `curl`, `gd` or `imagick` (standard on every cPanel PHP build — nothing to install)
  - A domain or subdomain you can point at this app

---

## 1. Build `vendor/` locally (one-time, required for both paths)

Composer isn't guaranteed on the hosting account, so `vendor/` is built once on your own machine and uploaded with everything else.

```bash
composer install --no-dev --optimize-autoloader
```

Run this inside the project folder. It creates a `vendor/` directory (a few hundred MB) that **must** be uploaded along with the rest of the project in both paths below.

---

## 2. Upload the project

Using cPanel **File Manager** or an FTP/SFTP client:

1. **Root domain or its own subdomain (recommended):** point that domain/subdomain's *Document Root* at this project's `public/` folder — cPanel → **Domains** (or **Subdomains**) → set Document Root to something like `ilbc/public` relative to your account's home directory.
2. Everything **outside** `public/` (`app/`, `config/`, `database/`, `routes/`, `vendor/`, etc.) should live **one level above** the public web root, never directly reachable by URL. This is the standard, secure Laravel layout — don't extract the whole project straight into `public_html`.
3. Upload the entire project, including the `vendor/` folder you just built, to that location (zip it up locally, upload the single zip via File Manager, then use File Manager's **Extract** — much faster than uploading hundreds of files individually over FTP).
4. Set folder permissions so the web server can write to these (File Manager → right-click → **Permissions**):
   - `storage/` and everything under it → `755` (try `775` if the app still can't write — some hosts run PHP as a different user/group)
   - `bootstrap/cache/` → `755` (or `775`)

---

## Path A — Terminal/SSH available: full command list

Open cPanel **Terminal** (or SSH in), `cd` into the project's root folder (the one containing `artisan`, one level above `public/`), then run these in order.

### A1. Create the database

Via cPanel's **MySQL Databases** wizard (simplest — handles the cPanel account-name prefixing for you): create a database, create a database user with a strong password, add that user to the database with **All Privileges**. Note the three values it gives you: full database name, full username, and host (usually `localhost`).

If you'd rather do it from a MySQL command line (some hosts expose one via Terminal, or through phpMyAdmin's **SQL** tab):

```sql
CREATE DATABASE cpaneluser_ilbc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cpaneluser_ilbc'@'localhost' IDENTIFIED BY 'a-strong-password-here';
GRANT ALL PRIVILEGES ON cpaneluser_ilbc.* TO 'cpaneluser_ilbc'@'localhost';
FLUSH PRIVILEGES;
```

(cPanel MySQL accounts are conventionally prefixed with your cPanel username, e.g. `cpaneluser_ilbc` — adjust to whatever your host requires.)

### A2. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` (via Terminal's editor, or File Manager's code editor) and set:

```
APP_URL=https://yourdomain.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_ilbc
DB_USERNAME=cpaneluser_ilbc
DB_PASSWORD=a-strong-password-here
```

Leave `SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` as shipped (`file`/`file`/`sync`) unless you specifically set up a persistent queue worker — see A6.

### A3. Build the database schema and starting data

```bash
php artisan migrate --force
php artisan db:seed --force
```

`--force` is required because `APP_ENV=production` in `.env.example`; Laravel otherwise asks for interactive confirmation, which a non-interactive deploy can't answer. This runs every migration (the full schema — requests, vendors, pricing, workflow, audit trail, everything) and seeds roles/permissions, the default workflow, SLA rules, approval-rule thresholds, and the Microsoft product catalog.

**Sample vendors/customer/walk-through request and illustrative per-role logins are never loaded by this command** — `db:seed` only loads real structural data by default, on purpose, so a production deploy can never end up with fake demo data and throwaway "password"-password logins in it. To load that sample data anyway (useful for a staging/demo install), do either of:

```bash
# Option 1: one extra command, demo data only, regardless of .env
php artisan db:seed --class=Database\\Seeders\\DemoDataSeeder --force

# Option 2: set this in .env first, then demo data loads as part of the normal db:seed above
ILBC_SEED_DEMO_DATA=true
```

Leave both alone for a completely clean production install.

### A4. Create your administrator account

There's no CLI command for this by design — every account, including the first one, goes through the same audited path as a real user record. Two options:

- **Simplest:** temporarily rename/move `routes/install.php`'s guard, or just leave the web installer available for this one step — visit `https://yourdomain.com/install/admin` directly (the requirements/database/migrate steps will already show as done) and create your Super Admin account through that form. It writes `storage/installed.lock` when finished, permanently 404-ing every `/install/*` route afterward.
- **Or**, run one artisan command via `tinker` if you'd rather stay entirely on the command line:

```bash
php artisan tinker --execute="
\$u = App\Models\User::create(['name' => 'Your Name', 'email' => 'you@yourdomain.com', 'password' => Illuminate\Support\Facades\Hash::make('a-strong-password'), 'is_active' => true]);
\$u->assignRole('Super Admin');
File::put(storage_path('installed.lock'), 'Installed at '.now());
"
```

Either way, **log in immediately after and change the password if you used a placeholder one.**

### A5. Link storage and cache the config

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`storage:link` makes uploaded files (order documents, loading screenshots, invoice PDFs) reachable at `public/storage`. The three `:cache` commands are optional but recommended for production — they precompile config/routes/views so every request skips re-parsing them. **Important:** if you change anything in `.env` or `config/*.php` after this, you must re-run `php artisan config:cache` (or `php artisan config:clear`) or the app will keep using the old cached values.

### A6. Set up the scheduler (cron)

Some background behavior (SLA overdue flagging, notifications) runs through Laravel's scheduler. Add one cPanel **Cron Job** (cPanel → **Cron Jobs** — no terminal needed for this part, though you're already there):

```
* * * * * /usr/local/bin/php /home/YOURUSER/path/to/project/artisan schedule:run >> /dev/null 2>&1
```

Ask your host for the exact PHP binary path if `/usr/local/bin/php` doesn't exist on your account — cPanel's Cron Jobs page usually offers the right path in a dropdown, or run `which php` in Terminal to find it.

### A7. Verify

```bash
php artisan about
```

Confirms the app is booting, which environment/drivers it's using, and that it can reach the database. Then visit `https://yourdomain.com/` in a browser and log in with the account from A4.

### Full command summary (Path A, copy-paste block)

```bash
cd /home/YOURUSER/path/to/project
cp .env.example .env
php artisan key:generate
# --- edit .env with your DB_* values now, then continue ---
php artisan migrate --force
php artisan db:seed --force
# optional demo data:
php artisan db:seed --class=Database\\Seeders\\DemoDataSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan about
```

Then create your admin account via `/install/admin` (A4) and add the cron entry (A6).

---

## Path B — File Manager/FTP only, no command line at all

The app ships a guided web installer for exactly this situation. You do **not** need to copy `.env.example` to `.env` yourself, generate a key, or run any command — visiting the site does it all for you.

1. Visit `https://yourdomain.com/` — you'll be redirected to `/install`.
2. **Step 1 — Requirements Check.** Confirms PHP version, required extensions, that `storage/`, `bootstrap/cache/`, and `.env` are writable, and that `vendor/` was uploaded. Fix anything marked FAIL and reload before continuing.
3. **Step 2 — Database Setup.** Enter the database host (usually `localhost`), port (`3306`), database name, username, and password from cPanel → **MySQL Databases** (create these there first, exactly as described in A1 above, if you haven't already). This is tested with a real connection attempt before anything is saved, and writes `.env` for you, including a freshly generated, unique `APP_KEY`.
4. **Step 3 — Create Tables & Starting Data.** Runs every migration and seeds roles/permissions, the default workflow, SLA rules, approval-rule thresholds, and the Microsoft product catalog. Leave **"seed demo data"** checked the first time if you want sample vendors/a customer/a walk-through request; uncheck it for a completely clean production install.
5. **Step 4 — Create Your Administrator Account.** This account gets the **Super Admin** role, which bypasses every permission check. It's the only login you need to get started — use it afterward to add your real team under **Administration → User & Role Management** and assign each person the correct role.

Once step 4 finishes, the installer writes `storage/installed.lock` and **every `/install/*` URL 404s permanently** — it can't be run again by anyone who finds the link later.

**Storage / uploaded files:** the installer creates the `public/storage` symlink automatically (falling back to a plain copy if your host disables PHP's `symlink()`, which a few restrictive hosts do). If uploads 404 after install, check that `public/storage` exists and points at (or contains a copy of) `storage/app/public`.

**Scheduled tasks:** add the same cPanel Cron Job as A6 above — Cron Jobs are configured entirely through the cPanel UI, so this step needs no command line either way.

---

## 3. Everyday configuration (no code changes or commands needed, either path)

Everything below is editable from inside the app once you're logged in as an admin:

- **System Settings** — company name/logo/address, currency (defaults to BDT/৳), timezone (defaults to Asia/Dhaka), date format (defaults to DD-MM-YYYY).
- **Workflow & Settings** — SLA hours per stage, conditional approval-rule thresholds (minimum margin %, high-value sales approval amount, etc.).
- **User & Role Management** — unlimited custom roles beyond the shipped defaults (Super Admin, Administrator, Management, Sales, Billing, Reviewer, Loader, Auditor, Closer, Finance, View Only); a user can hold multiple roles.
- **Vendor Management → Vendor Product Price** — SKU-wise vendor pricing with full history (never overwritten).
- **Vendors → (edit a vendor) → Provisioning Automation** — off by default (`manual` provider, fully manual Loading). Enable Microsoft Partner Center or Crayon CloudIQ here later and add credentials to switch that vendor's Loading step to live API provisioning without any code or command-line change; every vendor without configured credentials keeps working exactly as before.

## 4. Project structure

```
app/
 ├─ Models/            Eloquent models — one per table in the Section 37 schema
 ├─ Http/Controllers/  Thin controllers; business logic lives in Services
 ├─ Http/Middleware/   InstallGuard (locks the installer after first run)
 ├─ Policies/          Object-level authorization (RequestPolicy, VendorPolicy)
 ├─ Services/          CostCalculationService, MarginCalculationService, VendorPriceService,
 │                     WorkflowService, ApprovalService, LoadingService, AuditService,
 │                     BillingService, ClosureService, NotificationService, SLAService,
 │                     AuditLogService, and the Provisioning/* automation layer
 ├─ Contracts/         VendorProvisioningServiceInterface (Partner Center/Crayon-ready)
 └─ Notifications/     WorkflowNotification (database channel; email/SMS/Teams-ready)
database/
 ├─ migrations/        Full normalized schema, in dependency order
 └─ seeders/           RolesAndPermissionsSeeder, MasterDataSeeder,
                        MicrosoftProductCatalogSeeder, DemoDataSeeder
resources/views/       Bootstrap 5 Blade views, one folder per module
routes/
 ├─ web.php            The authenticated application
 └─ install.php        The no-command-line installer (Path B)
```

## 5. Troubleshooting

These are real issues found and fixed during development — listed here in case you're deploying from an older copy of this zip, or you hit something similar:

- **Crash on the very first page load, before you even see the installer.** Caused by no `.env` file existing yet and Laravel's default session/cookie-encryption middleware needing a real `APP_KEY`. Fixed in `public/index.php`, which now auto-provisions `.env` (from `.env.example`) and a real `APP_KEY` the moment the app is first hit, before Laravel's own bootstrapping runs. If you're on an older zip, replace `public/index.php` with the current one.
- **`SQLSTATE[42000]... Identifier name '...' is too long` during migration.** MySQL caps identifier names at 64 characters; one compound index name in `database/migrations/2026_01_01_000150_create_vendor_provisioning_tables.php` exceeded that. Fixed by giving it an explicit short name. If this happens on a custom migration you've added, give the long index/constraint an explicit name as the second argument to `$table->index([...], 'short_name')`.
- **`Field 'required_role' doesn't have a default value` while seeding.** `database/seeders/MasterDataSeeder.php` now supplies a role (Management or Finance, per Section 17) for every default approval rule.
- **Plain `php artisan db:seed` used to always load demo data (fake vendors/customer/request and the "password"-password logins in section 7 below), with no way to opt out from the command line.** `database/seeders/DatabaseSeeder.php` now only loads `DemoDataSeeder` when `ILBC_SEED_DEMO_DATA=true` is set in `.env` — see A3 above. The web installer's checkbox (Path B) was never affected by this; only the direct CLI command was.
- **A migration fails partway through, then re-running says "table already exists."** MySQL commits each `CREATE TABLE`/`ALTER TABLE` immediately — it can't be rolled back like a normal transaction — so a migration that fails partway leaves whatever tables it already created behind, without Laravel marking the migration as run. On a fresh/dev database with no real data, the simplest recovery is `php artisan migrate:fresh --seed` (Path A) or re-entering Step 2/3 of the web installer after fixing the underlying issue (Path B), since both drop and rebuild cleanly. **Never run `migrate:fresh` against a database with real production data** — fix the specific failing migration and use `php artisan migrate` (which only runs pending migrations) instead.
- **A seeder fails partway through, then re-running seems to skip everything.** This is expected and safe — every seeder here uses `firstOrCreate`/`updateOrCreate`, so re-running `php artisan db:seed --force` after fixing the cause simply fills in whatever didn't get created the first time, without duplicating what already succeeded.
- **`class "Database\Seeders\DemoDataSeeder" not found`** when running the demo-data command in A3 — double-check the backslash escaping if you're pasting into a shell that treats `\` specially (some Windows terminals do); `bash`/cPanel Terminal accepts the command exactly as written above.

### Updating an already-live site from an older copy of this zip

This round (Sept 2026) changed the Reviewer, Loading and Closure screens and added three new migrations. **If you already have this app running on a live server, uploading these updated files is not enough by itself — you also need to run the new migrations**, or you will get "column/table not found" errors:

- `database/migrations/2026_01_01_000160_...` — adds `loading_source_vendor_id` to `reviewer_approvals`.
- `database/migrations/2026_01_01_000161_...` — adds collection fields to `billing_records`.
- `database/migrations/2026_01_01_000162_...` — creates `closure_checklists`.

Upload the whole project (or at minimum every file listed under "What changed" below) over your existing install, then run **`php artisan migrate --force`** (Path A) — this only runs the three new/pending migrations, it does not touch your existing data. There is no web-installer step for this; if you only have File Manager/FTP (Path B), you need one-time Terminal/SSH access (even a temporary cPanel "Terminal" session) just to run this one command, since the no-SSH installer only runs once, at first install.

**What changed:**
- **Reviewer Approval → Decision panel**: "Loading Source" is now a real dropdown of your Vendor list (which vendor will perform the loading/installation), not the old fixed "Direct CSP / Distributor" text choice. "Tenant / Account" was removed from this screen — it's no longer needed at approval time.
- **Loading / Installation**: the queue is now one row per request (Request No., Customer, Product/SKU, Qty, Vendor, Approval Date, Status), with a **Review** action. Review opens a customer-wise, order-wise screen listing every item in that request, where you assign the **Tenant / Account** for each item (vendor and cost stay locked from Reviewer approval, per the cost-snapshot rule) before opening each item to finish loading details, documents and its checklist. Once every item is marked completed, the request still moves to Audit automatically, same as before.
- **Closure**: closing a request is no longer just a button — the Closure Team must now fill in **Collection Details** (status, amount, date, payment reference, payment method, outstanding amount) and tick a full **Closure Checklist** (Billing Verification + Collection Verification, 12 items) before the Close button will work.

## 6. Known limitations / what to verify before going live

1. Review `app/Services/ApprovalService.php` and the seeded `approval_rules` thresholds against your actual business rules before relying on them for real approvals — `required_role` is currently stored per rule but not yet enforced as *who specifically* must clear a triggered approval (any user with reviewer-approve permission can today).
2. If you plan to enable live vendor provisioning (Partner Center/Crayon) later, treat `app/Services/Provisioning/*` as a scaffold: the interface, factory, and manual provider are complete and in use today, but the live providers throw `ProvisioningNotConfiguredException` until real API credentials and tenant details are wired in.
3. Click through one full request end-to-end (Sales Entry → Billing Clearance → Reviewer → Loading → Audit → Billing → Closure) using the demo data, or your own, and confirm every stage transition, snapshot, and permission gate behaves as expected on your actual server before relying on it for real transactions.

## 7. Default sample logins (only if "seed demo data" was loaded)

| Role | Email | Password |
|---|---|---|
| Sales | sales@ilbc.local | password |
| Billing | billing@ilbc.local | password |
| Reviewer | reviewer@ilbc.local | password |
| Loader | loader@ilbc.local | password |
| Auditor | auditor@ilbc.local | password |
| Closer | closer@ilbc.local | password |
| Management | management@ilbc.local | password |
| Finance | finance@ilbc.local | password |

**Change or remove these before going live.** Your real administrator login is the one you created in A4 (Path A) or installer step 4 (Path B), not this table.
