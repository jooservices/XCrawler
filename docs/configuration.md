# Configuration Guide

Guide to configure XCrawler.

## Environment Variables

### Application Settings

```env
APP_NAME=XCrawler
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8080
```

### Database Configuration

#### SQLite (Default)

```env
DB_CONNECTION=sqlite
```

#### MySQL (Docker)

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=xcrawler
DB_USERNAME=xcrawler
DB_PASSWORD=xcrawler
```

#### MySQL (Manual)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xcrawler
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=xcrawler
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### FlickrHub Configuration

```env
# FlickrHub service URL
# For Docker: use service name (flickrhub)
# For manual: use localhost
FLICKR_HUB_URL=http://flickrhub:8000

# Callback URL for FlickrHub to send results
# For Docker: use service name (app)
# For manual: use localhost or actual IP
FLICKR_CALLBACK_URL=http://app/api/flick/callback
```

**Note**: `FLICKR_CALLBACK_URL` must be accessible from the FlickrHub container/service.

### Crawl Settings

```env
# Maximum recursion depth (default: 3)
FLICK_MAX_DEPTH=3
```

**Depth Explanation**:
- Depth 0: Root user
- Depth 1: Contacts of root user
- Depth 2: Contacts of contacts
- Depth 3: Contacts of contacts of contacts

### Telegram Notifications (Optional)

```env
# Bot token from @BotFather
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz

# Chat ID to receive notifications
TELEGRAM_CHAT_ID=123456789
```

**How to get Chat ID**:
1. Send a message to the bot
2. Visit: `https://api.telegram.org/bot<TOKEN>/getUpdates`
3. Find `chat.id` in the response

### Queue Configuration

```env
# For Docker: use redis
QUEUE_CONNECTION=redis

# For manual: use database
QUEUE_CONNECTION=database
```

To run queue worker:

```bash
# Docker
docker-compose exec queue php artisan queue:work

# Manual
php artisan queue:work
```

### Cache Configuration

```env
# For Docker: use redis
CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379

# For manual: use database
CACHE_STORE=database
```

## Module Configuration

Module config file: `Modules/Flick/config/config.php`

```php
return [
    'name' => 'Flick',
    'hub_url' => env('FLICKR_HUB_URL', 'http://localhost:8000'),
    'callback_url' => env('FLICKR_CALLBACK_URL', 'http://host.docker.internal/api/flick/callback'),
    'max_depth' => env('FLICK_MAX_DEPTH', 3),
    'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'telegram_chat_id' => env('TELEGRAM_CHAT_ID'),
];
```

## Storage Configuration

### Download Path

Photos are downloaded to: `base_path('media/{nsid}/photos')`

Example: `/path/to/XCrawler/media/12345678@N00/photos/`

### Filesystem Configuration

File: `config/filesystems.php`

Default uses `local` disk. Can configure S3 or other cloud storage.

## Performance Tuning

### Database Indexes

Indexes are automatically created in migrations:
- `flick_contacts.profile_url`
- `flick_photos.owner_nsid`
- `flick_crawl_tasks.status, priority`

### Queue Workers

To process multiple tasks simultaneously:

```bash
# Docker - already running in queue service
# Manual - run multiple workers
php artisan queue:work --tries=3 &
php artisan queue:work --tries=3 &
php artisan queue:work --tries=3 &
```

### Memory Limits

If crawling large amounts of data, you may need to increase memory limit:

```ini
# php.ini
memory_limit = 512M
```

Or in command:

```bash
php -d memory_limit=512M artisan flick:crawl
```

## Security Considerations

### Callback URL Security

Callback endpoint `/api/flick/callback` has no authentication. Ensure:

1. Only FlickrHub can access this endpoint
2. Use firewall/network rules
3. Or add IP whitelist in middleware

### API Keys

Do not commit `.env` file to git. Ensure `.env` is in `.gitignore`.

### Database Security

- Use strong passwords for production
- Limit database user permissions
- Enable SSL for database connections (production)

## Production Configuration

### Environment

```env
APP_ENV=production
APP_DEBUG=false
```

### Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### Queue

Use supervisor or systemd to run queue workers automatically.

For Docker, the queue service is already configured.

## Testing Configuration

To test with mock data:

```bash
# Docker
docker-compose exec app php artisan flick:test-mock

# Manual
php artisan flick:test-mock
```

See [Usage Guide](usage.md) for more details.
