# Usage Guide

Guide to using XCrawler.

## Getting Started

### 1. Start FlickrHub

Ensure FlickrHub service is running. See [flickrhub/README.md](../flickrhub/README.md).

With Docker, FlickrHub is automatically started with `docker-compose up -d`.

### 2. Run Crawl Command

```bash
# Using Docker
docker-compose exec app php artisan flick:crawl 12345678@N00

# Or from URL
docker-compose exec app php artisan flick:crawl https://www.flickr.com/people/username/

# Or manually (if not using Docker)
php artisan flick:crawl 12345678@N00
```

### 3. Monitor Progress

The command runs continuously and processes tasks. You can:

- View logs in terminal
- Check dashboard: `http://localhost:8080/flickr/dashboard`
- View stats: `docker-compose exec app php artisan flick:stats`

## Use Cases

### Use Case 1: Crawl a User and Contacts

```bash
docker-compose exec app php artisan flick:crawl 12345678@N00
```

The system will:
1. Fetch user's contacts
2. Fetch user's photos
3. Fetch each contact's photos
4. Fetch contacts of contacts (if depth < max_depth)
5. Continue recursively...

### Use Case 2: Download Photos of a User

```bash
# Download all photos
docker-compose exec app php artisan flick:download 12345678@N00

# Download with limit
docker-compose exec app php artisan flick:download 12345678@N00 --limit=100
```

### Use Case 3: Monitor a User (Auto-download)

```bash
docker-compose exec app php artisan flick:monitor 12345678@N00
```

User will be marked `is_monitored = true`. When new photos are available, the system will automatically download them.

### Use Case 4: Retry Failed Tasks

```bash
# Retry all failed tasks
docker-compose exec app php artisan flick:retry --all

# Retry a specific task (via dashboard or database)
```

### Use Case 5: Like Photos

```bash
docker-compose exec app php artisan flick:like 12345678@N00
```

## Dashboard

Access dashboard: `http://localhost:8080/flickr/dashboard`

Dashboard displays:
- Stats: Total contacts, photos, tasks
- Tasks: Task list with status
- Contacts: Contact list
- Photos: Photo list

### Execute Commands from Dashboard

POST `/api/flick/commands`:

```json
{
  "command": "crawl",
  "params": {
    "url": "https://www.flickr.com/people/username/"
  }
}
```

## API Usage

### View Stats

```bash
curl http://localhost:8080/api/flick/stats
```

Response:
```json
{
  "contacts": {
    "total": 150,
    "monitored": 5
  },
  "photos": {
    "total": 5000,
    "downloaded": 3000,
    "missed": 0
  },
  "tasks": {
    "pending": 10,
    "processing": 2,
    "completed": 500,
    "failed": 5
  }
}
```

### View Tasks

```bash
curl "http://localhost:8080/api/flick/tasks?page=1&per_page=20"
```

### View Contacts

```bash
curl "http://localhost:8080/api/flick/contacts?page=1&per_page=50"
```

### View Photos

```bash
curl "http://localhost:8080/api/flick/photos?page=1&per_page=50"
```

See [API Documentation](api.md) for details.

## Workflow

### Crawl Workflow

```
1. User runs: flick:crawl {nsid}
   ↓
2. Create root task: FETCH_CONTACTS (depth=0)
   ↓
3. Worker loop:
   - Get pending task (highest priority)
   - Send request to FlickrHub
   - Mark task: queued_at_hub
   ↓
4. FlickrHub processes and sends callback
   ↓
5. FlickCallbackController processes:
   - FETCH_CONTACTS → Save contacts, create FETCH_PHOTOS tasks
   - FETCH_PHOTOS → Save photos, create FETCH_CONTACTS/FETCH_FAVES tasks
   - If monitored → Create DOWNLOAD_PHOTOS task
   ↓
6. Repeat from step 3
```

### Download Workflow

```
1. User runs: flick:download {nsid}
   ↓
2. DownloadService gets undownloaded photos
   ↓
3. For each photo:
   - Get best URL from sizes_json
   - Download to: media/{nsid}/photos/{flickr_id}_{secret}.{ext}
   - Update: is_downloaded = true
```

## Best Practices

### 1. Start with Small Depth

```bash
# In .env
FLICK_MAX_DEPTH=2
```

Then gradually increase if needed.

### 2. Monitor Storage

Downloading photos can consume a lot of space. Check regularly:

```bash
du -sh media/
```

### 3. Use Queue Workers

Instead of running crawl command directly, you can queue tasks:

```bash
# Docker - queue service is already running
# Manual
php artisan queue:work
```

### 4. Backup Database

Backup regularly:

```bash
# SQLite
cp database/database.sqlite database/backup_$(date +%Y%m%d).sqlite

# MySQL (Docker)
docker-compose exec mysql mysqldump -u xcrawler -pxcrawler xcrawler > backup_$(date +%Y%m%d).sql

# MySQL (Manual)
mysqldump -u root -p xcrawler > backup_$(date +%Y%m%d).sql
```

### 5. Cleanup Old Data

```bash
docker-compose exec app php artisan flick:cleanup --older-than=30days
```

## Troubleshooting

### Task Stuck at "queued_at_hub"

- Check if FlickrHub is running: `curl http://localhost:8000/api/health`
- Check if callback URL is accessible
- View logs: `docker-compose logs app` or `storage/logs/laravel.log`

### Download Failed

- Check disk space
- Check permissions: `chmod -R 775 media/`
- Check if URL is valid

### Memory Limit Exceeded

```bash
docker-compose exec app php -d memory_limit=512M artisan flick:crawl
```

### Too Many Tasks

If there are too many tasks, you can:
- Increase priority for important tasks
- Cleanup old completed tasks
- Reduce max_depth

## Advanced Usage

### Custom Priority

In code, you can set priority when creating tasks:

```php
FlickCrawlTask::create([
    'contact_nsid' => $nsid,
    'type' => 'FETCH_PHOTOS',
    'priority' => 100, // Higher = more priority
]);
```

### Custom Callback Handler

You can extend `FlickCallbackController` to handle custom logic.

### Batch Operations

Use API for batch operations:

```bash
# Crawl multiple users
for nsid in user1 user2 user3; do
  docker-compose exec app php artisan flick:crawl $nsid &
done
```

See [Commands Reference](commands.md) for the complete command list.
