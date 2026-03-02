# TaskForge - Complete Implementation Summary

## 📋 Project Overview

TaskForge is a full-stack task management application built with Laravel (backend) and Vue.js (frontend), featuring: This is the Laravel(backend)

- **OTP-based Authentication**: Secure, passwordless email authentication
- **Task Management**: Full CRUD operations with advanced filtering and sorting
- **CSV Import**: Asynchronous bulk import with queue processing (handles 10,000+ rows)
- **Real-time Updates**: WebSocket-powered live progress tracking using Laravel reverb
- **Professional UI**: Clean, responsive design with Tailwind CSS

## 🏗️ Architecture

### Backend (Laravel)
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Sanctum (token-based)
- **Queue System**: Laravel Horizon (Redis-backed recommended)
- **WebSockets**: Laravel Reverb (Pusher protocol compatible)
- **Database**: MySQL with proper indexing
- **Job Processing**: Chunked processing for scalability

## 🚀 Quick Start Guide

### Prerequisites
```bash
# Required
PHP 8.2+, Composer, MySQL 8.0+, Node.js 22+, Composer 2.x

# Recommended
Redis (for production queues)
Supervisor (for production workers)
```

### Backend Setup (5 minutes)

```bash
# 1. Clone & Install
git clone https://github.com/VikasJakasaniya/task-forge-laravel-backend.git
cd taskforge-backend
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 2.2 Configure `.env`: Add or replace the following variables
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost,127.0.0.1

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskforge
DB_USERNAME=
DB_PASSWORD=

QUEUE_CONNECTION=redis

BROADCAST_CONNECTION=reverb
BROADCAST_DRIVER=reverb
REVERB_APP_ID=taskforge
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

OTP_EXPIRY_MINUTES=5
OTP_LENGTH=6

OTP_REQUEST_LIMIT=3
OTP_VERIFY_LIMIT=5

IMPORT_DEMO_MODE=false
IMPORT_CHUNK_SIZE=500
IMPORT_DEMO_DELAY=1

# 3. Database Setup
Create a database named 'taskforge' in mysql/phpMyAdmin

# Edit .env:
# - Set DB credentials
# - Configure mail
# - Set FRONTEND_URL=http://localhost:5173

# 4. Run migrations
php artisan migrate

# 5. Install broadcasting
php artisan install:broadcasting

# 6. Start services (need 3 terminals)
php artisan serve           # Terminal 1
php artisan horizon         # Terminal 2
php artisan reverb:start    # Terminal 3
```

### Access Points
- **Backend API**: http://localhost:8000/api
- **Horizon Dashboard**: http://localhost:8000/horizon

## Troubleshooting
```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

### Running Tests
Please check the TEST_COMMANDS.md file for more information to run test cases.
```

## 💡 Frontend Usage Example Flow

1. **User Registration/Login**:
   ```
   User → Enter email → Receive OTP → Enter OTP → Dashboard
   ```

2. **Create Task**:
   ```
   Dashboard → Click "New Task" → Fill form → Submit → See in list
   ```

3. **Filter Tasks**:
   ```
   Task List → Select filters → Results update automatically
   ```

4. **Import Tasks**:
   ```
   Go to Imports → Upload CSV → Watch progress bar update live
   ```

5. **Real-time Experience**:
   ```
   Upload CSV → Jobs process in background → Progress updates without refresh
   → Completion toast notification
   ```
