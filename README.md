# Candidate Portal

A modern recruitment management platform for sourcing international candidates. Built with Laravel 11 + Vue 3 + Vuetify 3.

---

## Prerequisites

| Tool | Version |
|------|---------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| MySQL | 8+ |

---

## Backend Setup

```bash
cd backend

# 1. Install dependencies
composer install

# 2. Create .env from example
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. (If needed) Edit .env to match your DB:
#    DB_DATABASE=candidate_portal
#    DB_USERNAME=root
#    DB_PASSWORD=root

# 5. Create the database (run in MySQL):
#    CREATE DATABASE candidate_portal;

# 6. Run migrations and seed data
php artisan migrate --seed

# 7. Create storage symlink
php artisan storage:link
```

---

## Frontend Setup

```bash
cd frontend

# Install dependencies
npm install
```

---

## Running the Application

**Terminal 1 — Laravel API:**
```bash
cd backend
php artisan serve
# Runs at: http://localhost:8000
```

**Terminal 2 — Vue frontend:**
```bash
cd frontend
npm run dev
# Runs at: http://localhost:5173
```

Open **http://localhost:5173** in your browser.

---

## Default Login Credentials

| Email | Password | Role | Company |
|-------|----------|------|---------|
| admin@company.de | admin123 | Admin | Recruitment GmbH |
| client1@mueller.de | client123 | Client | Müller GmbH |
| client2@schmidt.de | client123 | Client | Schmidt AG |
| client3@hospital.nl | client123 | Client | Amsterdam Hospital |

---

## Features

### Admin
- **Dashboard** — Statistics, charts, recent requests
- **Candidates** — Full CRUD with image upload, status management
- **Clients** — User management with auto-generated passwords
- **Requests** — Manage all requests with inline status/note editing
- **Reports** — Candidate list, statistics charts, candidate profiles

### Client
- **Candidates** — Browse Active candidates with advanced filters
- **Request Actions** — Reserve, More Info, Interview requests
- **My Requests** — Track own request history
- **Reports** — Candidate list, statistics, profile exposés

---

## Adding Real Candidates

1. Log in as admin (admin@company.de)
2. Go to **Candidates** in the sidebar
3. Click **+ Add Candidate**
4. Fill in all fields, upload a photo (JPG/PNG, max 2MB)
5. Add additional languages dynamically
6. Click **Save Candidate**

---

## Creating New Client Accounts

1. Log in as admin
2. Go to **Clients** in the sidebar
3. Click **+ Add Client**
4. Fill in: Name, Email, Company Name, Country
5. The system generates a secure password — **copy it immediately** (shown once)
6. Share credentials with the client securely

---

## Troubleshooting

### CORS errors
Ensure `config/cors.php` has `allowed_origins: ['*']` and Laravel is running on port 8000.

### 401 Unauthorized
- Tokens are stored in `localStorage`. Clear it and log in again.
- Check Sanctum is installed: `composer require laravel/sanctum`
- Verify `.env` has `SANCTUM_STATEFUL_DOMAINS=localhost:5173`

### Storage / images not loading
Run `php artisan storage:link` from the backend directory.
Images must be in `storage/app/public/candidates/`.

### Migration errors
- Ensure database `candidate_portal` exists in MySQL
- Run `php artisan migrate:fresh --seed` to reset completely
- Check `.env` DB credentials match your MySQL setup

### Vite build errors
```bash
rm -rf node_modules package-lock.json
npm install
npm run dev
```
