# Architecture Documentation

Documentation about XCrawler architecture.

## Overview

XCrawler uses a 2-tier architecture:

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│  XCrawler   │────────▶│  FlickrHub   │────────▶│ Flickr API  │
│  (Crawler)  │         │   (Proxy)    │         │             │
└─────────────┘         └──────────────┘         └─────────────┘
       ▲                        │
       │                        │
       └──────── Callback ───────┘
```

### FlickrHub (Proxy Layer)

- Handles rate limiting
- Queue management
- OAuth handling
- Sends callback when completed

### XCrawler (Crawler Layer)

- Recursive crawl logic
- Data storage
- Task management
- Download service

## Module Structure

XCrawler uses [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules) to organize code.

### Module: Flick

```
Modules/Flick/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Http/
│   │   └── Controllers/  # HTTP controllers
│   ├── Models/           # Eloquent models
│   └── Services/         # Business logic
├── config/               # Module configuration
├── database/
│   └── migrations/      # Database migrations
└── routes/               # Module routes
```

## Data Models

### FlickContact

Stores Flickr user information.

```php
- id
- nsid (unique)           # Flickr user ID
- username
- realname
- location
- iconserver, iconfarm
- photos_count
- contacts_count
- crawl_status
- last_crawled_at
- is_monitored            # Auto-download flag
- profile_url
- timestamps
- deleted_at (soft delete)
```

### FlickPhoto

Stores photo information.

```php
- id
- flickr_id (unique)      # Flickr photo ID
- owner_nsid              # Foreign key to FlickContact
- title
- secret, server, farm
- is_primary
- has_comment
- sizes_json              # JSON containing URLs of different sizes
- is_downloaded
- local_path              # Path to downloaded file
- captured_at, posted_at
- timestamps
- deleted_at (soft delete)
```

### FlickCrawlTask

Manages crawl tasks.

```php
- id
- contact_nsid           # User to crawl
- type                   # FETCH_CONTACTS, FETCH_PHOTOS, FETCH_FAVES, etc.
- page                   # Pagination
- status                 # pending, processing, queued_at_hub, completed, failed
- hub_request_id          # Request ID from FlickrHub
- priority               # Higher = more priority
- depth                  # Depth in recursion
- payload                # JSON containing extra data
- retry_count
- max_retries
- last_error
- failed_at
- timestamps
```

## Services

### FlickrHubService

Communicates with FlickrHub API.

**Methods**:
- `request(string $method, array $params): ?array` - Send request to FlickrHub

**Flow**:
```
1. Prepare request payload
2. POST to {hub_url}/flickr/request
3. Return response with request_id
```

### DownloadService

Handles photo downloads.

**Methods**:
- `downloadUserPhotos(string $nsid, bool $force, $output, ?int $limit): int`

**Flow**:
```
1. Get undownloaded photos
2. For each photo:
   - Get best URL (original > large_2048 > large_1600 > ...)
   - Download to local
   - Update is_downloaded = true
```

### TelegramService

Sends notifications via Telegram.

**Methods**:
- `notify(string $message): void`

## Controllers

### FlickCallbackController

Receives callback from FlickrHub and processes results.

**Flow**:
```
1. Validate payload
2. Find task by hub_request_id
3. Process result by task type:
   - FETCH_CONTACTS → processContacts()
   - FETCH_PHOTOS → processPhotos()
   - FETCH_FAVES → processContacts()
   - RESOLVE_USER → processUserResolution()
4. Update task status = completed
5. Create new tasks if needed (recursion)
```

### DashboardController

API endpoints for dashboard.

**Endpoints**:
- `stats()` - Overview statistics
- `tasks()` - Task list
- `contacts()` - Contact list
- `photos()` - Photo list
- `execute()` - Execute commands

## Console Commands

### CrawlCommand

Main command to crawl.

**Flow**:
```
1. Parse input (NSID or URL)
2. If URL → Create RESOLVE_USER task
3. Create root FETCH_CONTACTS task
4. Worker loop:
   - Get pending task (priority desc, id asc)
   - Lock task (lockForUpdate)
   - Update status = processing
   - Prepare API call
   - Gửi đến FlickrHub
   - Update status = queued_at_hub
   - Sleep 1s (rate limit)
```

### DownloadCommand

Download ảnh của một user.

### RetryCommand

Retry các task failed.

### CleanupCommand

Dọn dẹp dữ liệu cũ.

### MonitorCommand

Đánh dấu user để monitor (auto-download).

### LikeCommand

Like photos của một user.

### StatsCommand

Hiển thị thống kê.

## Recursion Logic

### Depth Control

```
Depth 0: Root user
  ↓
Depth 1: Contacts của root
  ↓
Depth 2: Contacts của contacts
  ↓
Depth 3: Contacts của contacts của contacts
```

### Task Creation Flow

```
FETCH_CONTACTS (depth=0)
  ↓
  Process contacts
  ↓
  Tạo FETCH_PHOTOS (depth=1) cho mỗi contact
    ↓
    Process photos
    ↓
    Tạo FETCH_CONTACTS (depth=1) và FETCH_FAVES (depth=1)
      ↓
      (Nếu depth < max_depth)
```

### Priority System

- Root tasks: priority = 100
- Direct contacts: priority = 10
- Recursive contacts: priority = 5
- Download tasks: priority = 90 (high)

## Database Schema

### Indexes

```sql
-- flick_contacts
CREATE INDEX idx_profile_url ON flick_contacts(profile_url);

-- flick_photos
CREATE INDEX idx_owner_nsid ON flick_photos(owner_nsid);

-- flick_crawl_tasks
CREATE INDEX idx_status_priority ON flick_crawl_tasks(status, priority);
```

### Relationships

```
FlickContact (1) ──< (N) FlickPhoto
FlickContact (1) ──< (N) FlickCrawlTask
```

## API Flow

### Request Flow

```
Client → XCrawler API
  ↓
Controller → Service
  ↓
Service → FlickrHub
  ↓
FlickrHub → Flickr API
  ↓
Flickr API → FlickrHub
  ↓
FlickrHub → XCrawler Callback
  ↓
CallbackController → Process Result
  ↓
Update Database
```

### Callback Flow

```
FlickrHub POST /api/flick/callback
  ↓
FlickCallbackController::__invoke()
  ↓
Validate payload
  ↓
Find task by hub_request_id
  ↓
Process result (processContacts, processPhotos, etc.)
  ↓
Create new tasks (recursion)
  ↓
Update task status
```

## Error Handling

### Retry Mechanism

Tasks có thể retry:
- `retry_count`: Số lần đã retry
- `max_retries`: Số lần retry tối đa
- `last_error`: Lỗi cuối cùng
- `failed_at`: Thời điểm failed

### Status Flow

```
pending → processing → queued_at_hub → completed
                              ↓
                           failed
                              ↓
                          (retry)
```

## Performance Considerations

### Database Queries

- Sử dụng eager loading: `with('contact')`
- Indexes trên các cột thường query
- Pagination cho large datasets

### Memory Management

- Process từng batch nhỏ
- Unset variables sau khi dùng
- Sử dụng generators nếu có thể

### Rate Limiting

- Sleep 1s giữa các requests
- FlickrHub xử lý rate limiting
- Queue system để tránh overload

## Security

### Callback Endpoint

- Không có authentication (cần network security)
- Validate request_id
- Log tất cả callbacks

### API Keys

- Lưu trong .env
- Không commit vào git
- Rotate keys định kỳ

## Extensibility

### Thêm Task Type mới

1. Thêm case trong `CrawlCommand`
2. Thêm process method trong `FlickCallbackController`
3. Update migration nếu cần

### Thêm Service mới

1. Tạo class trong `app/Services/`
2. Register trong ServiceProvider nếu cần
3. Inject vào controllers/commands

### Thêm Command mới

1. Tạo class trong `app/Console/`
2. Register trong `module.json` hoặc auto-discover
3. Implement `handle()` method

