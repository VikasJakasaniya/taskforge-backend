# 🚀 TaskForge Deployment Guide (Apache Server)

This document explains what are required to deploy the TaskForge application using:

- Backend: Laravel 12 (Apache + PHP-FPM)
- Frontend: Vue 3 + Vite (Static Build)
- Queue: Redis + Horizon
- WebSockets: Laravel Reverb
- Database: MySQL

---

# 🏗️ Production Architecture

User → Apache →
- / (Vue App - Static Files)
- /api (Laravel Backend)

Queue Processing → Supervisor
WebSockets → Reverb
Cache & Queue → Redis
Database → MySQL

---

# 🖥️ Server Requirements

- Ubuntu 22.04 / 24.04
- Apache 2.4+
- PHP 8.2+
- MySQL 8+
- Node.js ^22
- Composer 2.x
- Redis
- Supervisor

---

# 🔷 1. Install Required Packages

```bash
sudo apt update
sudo apt install apache2 mysql-server redis-server supervisor unzip curl git
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-bcmath php8.2-zip

# 🔷 1. Supervisor Setup

sudo nano /etc/supervisor/conf.d/taskforge-worker.conf

[program:taskforge-worker]
command=php /var/www/taskforge-backend/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/taskforge-backend/storage/logs/worker.log

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start taskforge-worker
```
