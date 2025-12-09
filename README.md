# Bitgap Task Management API

A secure, role-based backend system built with **Laravel 12**, **PostgreSQL**, **Redis**, and **Docker** — developed strictly according to the **Bitgap Back-End Challenge** specifications.

> ✅ **API-only** — No views, no frontend, no real-time features.  
> ✅ **Role-based access** (`admin` / `user`)  
> ✅ **Redis caching**, **input validation**, **logging**, **Dockerized**

---

## 🚀 Quick Setup (For Reviewers)

Follow these steps to run and test the project **in under 2 minutes**.

### Prerequisites
- [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/) installed

### Run the Application
```bash
# 1. Clone the repository
git clone https://github.com/your-username/bitgap-task-management.git
cd bitgap-task-management

# 2. Start containers
docker-compose up -d

# 3. Install dependencies & run migrations (includes admin seeder)
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

### Authentication Flow

All protected endpoints require a Bearer Token in the header:

```
Authorization: Bearer <your_access_token>
```

### Register a User
POST /api/register
Content-Type: application/json
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "123456",
  "password_confirmation": "123456"
}
```

### Login (Get Token)
POST /api/login
Content-Type: application/json
```json
{
  "email": "test@example.com",
  "password": "123456"
}

//Response includes access_token.
```

### Logout (Invalidate Token)

POST /api/logout

Authorization: Bearer <token>

### Task Management 
<table style="border:1px solid;">
    <thead >
        <tr>
            <th style="border:1px solid;text-align:center">Endpoint</th>
            <th style="border:1px solid;text-align:center">Method</th>
            <th style="border:1px solid;text-align:center">Endpoint</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border:1px solid;text-align:center">/api/tasks</td>
            <td style="border:1px solid;text-align:center">GET</td>
            <td style="border:1px solid;text-align:center">List tasks (supports ?status=pending&search=bug)</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">/api/tasks</td>
            <td style="border:1px solid;text-align:center">POST</td>
            <td style="border:1px solid;text-align:center">Create task (title, due_date required)</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">/api/tasks/{id}</td>
            <td style="border:1px solid;text-align:center">GET</td>
            <td style="border:1px solid;text-align:center">View task</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">/api/tasks/{id}</td>
            <td style="border:1px solid;text-align:center">PUT</td>
            <td style="border:1px solid;text-align:center">Update task (status, assigned_to, etc.)</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">/api/tasks/{id}</td>
            <td style="border:1px solid;text-align:center">DELETE</td>
            <td style="border:1px solid;text-align:center">Delete task (only owner or admin)</td>
        </tr>
    </tbody>
</table>

#### Example: Create a Task
```json
{
  "title": "Fix API",
  "description": "Handle 404 errors",
  "due_date": "2025-12-31",
  "assigned_to": 2  // optional (user ID)
}
```
* Filtering: GET /api/tasks?status=pending
* Search: GET /api/tasks?search=fix
* Sorting: Tasks ordered by id DESC (newest first)

### User Management (Admin Only)

GET /api/users

Authorization: Bearer <admin_token>

### Security & Compliance
<table style="border:1px solid;">
    <thead >
        <tr>
            <th style="border:1px solid;text-align:center">Requirement</th>
            <th style="border:1px solid;text-align:center">Implementation</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border:1px solid;text-align:center">User Authentication</td>
            <td style="border:1px solid;text-align:center">Laravel Sanctum + Bearer Token</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Role-Based Access</td>
            <td style="border:1px solid;text-align:center">admin vs user; middleware enforces permissions</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Task Ownership</td>
            <td style="border:1px solid;text-align:center">Users only access their own or assigned tasks</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Password Hashing</td>
            <td style="border:1px solid;text-align:center">Hash facade used for all passwords</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Input Validation</td>
            <td style="border:1px solid;text-align:center">All endpoints validate inputs (e.g., status in [pending,completed])</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">XSS Prevention</td>
            <td style="border:1px solid;text-align:center">No HTML output (API-only); inputs validated</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Logging</td>
            <td style="border:1px solid;text-align:center">laravel.log tracks task updated and task deleted</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Caching</td>
            <td style="border:1px solid;text-align:center">Redis caches task lists per user; cache cleared on write</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Database</td>
            <td style="border:1px solid;text-align:center">PostgreSQL with proper relationships (creator_id, assigned_to)</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">Docker</td>
            <td style="border:1px solid;text-align:center">docker-compose.yml includes Laravel, PostgreSQL, Redis</td>
        </tr>
    </tbody>
</table>

### 🐳 Docker Services

<table style="border:1px solid;">
    <thead >
        <tr>
            <th style="border:1px solid;text-align:center">Service</th>
            <th style="border:1px solid;text-align:center">Tech</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border:1px solid;text-align:center">app</td>
            <td style="border:1px solid;text-align:center">Laravel 12 (PHP 8.2)
</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">db</td>
            <td style="border:1px solid;text-align:center">PostgreSQL 15</td>
        </tr>
        <tr>
            <td style="border:1px solid;text-align:center">redis</td>
            <td style="border:1px solid;text-align:center">Redis 7 (caching)</td>
        </tr>
    </tbody>
</table>

### 📝 Notes for Reviewer
* No redirects: Missing tokens return 401 {"message": "Unauthenticated."} — never 500 or login redirect.
* Strict authorization: A regular user cannot see or delete another user’s tasks.
* Redis cache is automatically cleared when tasks are created, updated, or deleted.
* All sensitive operations are logged in storage/logs/laravel.log.
* PostgreSQL schema uses foreign keys and proper relationships.