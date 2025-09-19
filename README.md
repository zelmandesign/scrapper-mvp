# Scraping Microservice

This project is a Laravel 12.x microservice that handles scraping, profiles, and webhooks.  
It uses Redis as the queue driver and Laravel Horizon for monitoring jobs.

---

## Requirements
- Docker + Laravel Sail
- Redis (ships with Sail)
- Horizon (installed as a dependency)

---

## Environment

Update `.env`:

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

---

## Starting the project

Start containers:
./vendor/bin/sail up -d

Install dependencies:
./vendor/bin/sail composer install

Run migrations:
./vendor/bin/sail artisan migrate

---

## Queues

Start Horizon (recommended):
./vendor/bin/sail artisan horizon

Or run workers manually:
./vendor/bin/sail artisan queue:work redis –queue=scraping,webhooks

---

## Horizon Dashboard

Monitor jobs in the browser:

http://localhost/horizon

---

✅ Summary
- Redis is the **queue driver**
- Horizon is used to **supervise and monitor jobs**
- Use `sail artisan horizon` to start workers after bringing containers up
