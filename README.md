# Scraping Microservice

This is a Laravel 12.x microservice for scraping OnlyFans profiles, persisting them in a database, and delivering results via webhooks.  
It uses **Redis** as the queue driver and **Laravel Horizon** for supervising jobs.

---

## 🔧 Requirements
- Docker + Laravel Sail
- Redis (ships with Sail)
- Horizon (installed as dependency)
- Scout (installed as dependency)

---

## ⚙️ Environment

Update `.env` with:

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
SCOUT_DRIVER=database

---

## Starting the Project

Start containers:
sail up -d

Install dependencies:
sail composer install

Run migrations:
sail artisan migrate

---

## Queues

Start Horizon (recommended):
sail artisan horizon

Or run workers manually:
sail artisan queue:work –queue=scraping,rescraping,webhooks

---

## Rescraping & Scheduler

Run rescrape manually:

sail artisan profiles:rescrape --tier=high
sail artisan profiles:rescrape --tier=normal

Run the scheduler loop:

sail artisan schedule:work

---

## Horizon Dashboard

Monitor queues & workers in the browser:  
[http://localhost/horizon](http://localhost/horizon)

---

## Scout

Import data into the search index:

sail artisan scout:import “App\Models\Profile”

---

## Summary
- Redis = **queue driver**
- Horizon = **monitoring & supervision**
- Workers must be started (`horizon` or `queue:work`) after containers are up
- Scheduler (`schedule:work`) must run to trigger background rescraping

## ⚖️ Trade-offs & Limitations

- **No bulk scraping endpoint**  
  Current API supports scraping **one profile per request**. Bulk submission could be added but was considered out of scope.

- **Minimal error handling in scraper**  
  The fake `ProfileScraper` always returns data.

- **Webhook retries**  

- **Tests**  
  Automated unit/feature tests were not included due to time. Manual testing was done, but coverage should be added.

- **Logging**  
  Basic logging is in place (info/warning/error).

- **Rate limiting**  
  Throttling was applied only on the `scrapes` endpoint.

---


# Scraper Microservice — API Endpoints

Auth handled via **Sanctum** (token-based).  
Write routes are rate-limited with `throttle:10,1` (~10 requests/min).

---

## Authentication
- `POST /api/login`  
  → Issue Sanctum token.

- `GET /api/user` *(auth)*  
  → Get current user.

- `POST /api/logout` *(auth)*  
  → Revoke token.

---

## Scraping Flow (Asynchronous)

- `POST /api/v1/scrapes` *(auth, throttled)*  
  Queue a scrape request for an OnlyFans profile.  
  **Body example:**
  ```json
  {
    "of_username": "alice",
    "webhook_url": "https://webhook.site/your-test-url"
  }

Headers:
Idempotency-Key: <uuid> → prevents duplicate requests.

Response:
{
    "message": "Processing scraping request",
    "payload": {
        "of_username": "username",
        "webhook_url": "https://webhook.site/e9569f86-0c29-4b34-87d6-b286050ab213"
    },
    "scrape_id": 38,
    "status": "queued",
    "status_url": "http://localhost/api/v1/scrapes/38"
}

- `GET /api/v1/scrapes/{id}` (auth)
Fetch status of a scrape request (queued, running, succeeded, failed).

⸻

## Profiles
-	`GET /api/v1/profiles`
Paginated list of scraped profiles.
-	`GET /api/v1/profiles/{handle}`
Fetch a single profile by username/handle.

⸻

## Search
-	`GET /api/v1/search?q=alice`
Full-text search (via Laravel Scout) across username, name, bio.

⸻

## Background Jobs (Scheduler)
•	Profiles ≥ 100k likes → re-scraped every 24h
•	Profiles < 100k likes → re-scraped every 72h

Implemented via Laravel Scheduler → dispatches profiles:rescrape commands.

⸻

🧪 Testing Webhooks

Use https://webhook.site to quickly verify delivery.

Summary
•	✅ Auth with Sanctum
•	✅ Scraping endpoint (async with Horizon & Redis)
•	✅ Profile list & detail
•	✅ Full-text search with Scout
•	✅ Scheduled rescraping jobs (24h / 72h)
•	✅ Webhook delivery supported


