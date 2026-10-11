# Kisii University — Student Results Lookup System

A Laravel 11 web application for looking up student academic results by admission number, academic year, and semester. Includes a full authenticated admin panel.

---

## Tech Stack

- **PHP** 8.2+
- **Laravel** 11
- **MySQL** 8+
- **Bootstrap** 5.3 (CDN)
- **Laravel Blade** templates

---

## Setup Instructions

### 1. Install dependencies

```bash
cd student-results-lookup
composer install
npm install && npm run build
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_results
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Create the database

In MySQL:

```sql
CREATE DATABASE student_results CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Seed test data

```bash
php artisan db:seed
```

This creates:
- **6 fictional students** with realistic admission numbers
- **12 courses** across CS, Engineering, Mathematics, etc.
- **35+ result records** spanning Year 1–4, both semesters, all grade bands (A–F)
- **1 admin user**: `admin@university.ac.ke` / `Admin@1234`

### 6. Start the development server

```bash
php artisan serve
```

Visit: http://localhost:8000

---

## URL Structure

| URL | Description |
|-----|-------------|
| `/` | Redirects to `/results` |
| `/results` | Student lookup page (public) |
| `/results?admission_number=IN16%2F00088%2F24&academic_year=Year+2&semester=Semester+1` | Results page |
| `/login` | Admin login |
| `/admin/dashboard` | Admin dashboard |
| `/admin/students` | Manage students |
| `/admin/courses` | Manage courses |
| `/admin/results` | Manage results |

---

## Test Data — Admission Numbers

Use these to test the lookup form:

| Admission Number | Student | Year/Semester with Results |
|------------------|---------|---------------------------|
| `IN16/00088/24` | Amelia Wanjiku Fictitious | Year 1 Sem 1, Year 1 Sem 2, Year 2 Sem 1 |
| `CS18/00145/24` | Brian Otieno Testname | Year 1 Sem 1, Year 1 Sem 2 |
| `ENG20/00312/24` | Catherine Mwangi Sampledata | Year 3 Sem 1, Year 3 Sem 2 |
| `LAW19/00077/24` | David Kipchoge Placeholder | Year 4 Sem 1, Year 4 Sem 2 |
| `MED21/00201/24` | Esther Achieng Demouser | Year 1 Sem 1 |
| `IT17/00099/24` | Felix Kamau Fictional | Year 3 Sem 1, Year 3 Sem 2 |

---

## Admin Login

- **URL**: http://localhost:8000/login
- **Email**: `admin@university.ac.ke`
- **Password**: `Admin@1234`

---

## Security Features

- Rate limiting: 10 requests/minute/IP on `/results` (returns HTTP 429)
- All queries use Eloquent — no raw SQL
- No database IDs exposed in public URLs
- Admin panel fully protected by `auth` middleware
- Read-only public interface — no modify/delete controls on lookup page
- Server-side validation on all inputs

---

## Grade Scale

| Marks | Grade |
|-------|-------|
| 70–100 | A |
| 60–69 | B |
| 50–59 | C |
| 40–49 | D |
| 0–39 | F |

---

## Running Tests

```bash
php artisan test
```

---

## Route List

```bash
php artisan route:list
```

---

## Fresh Reseed

```bash
php artisan migrate:fresh --seed
```
