# Commands Reference

Complete list of XCrawler Artisan commands.

## flick:crawl

Start recursive crawling of contacts and photos from a user.

```bash
# Docker
docker-compose exec app php artisan flick:crawl {nsid?}

# Manual
php artisan flick:crawl {nsid?}
```

**Arguments**:
- `nsid` (optional) - User NSID or Flickr profile URL. If not provided, crawl current user (me).

**Examples**:
```bash
# Crawl from NSID
docker-compose exec app php artisan flick:crawl 12345678@N00

# Crawl from URL
docker-compose exec app php artisan flick:crawl https://www.flickr.com/people/username/

# Crawl current user
docker-compose exec app php artisan flick:crawl
```

**Behavior**:
- Creates root task `FETCH_CONTACTS` with depth=0
- If input is URL, creates `RESOLVE_USER` task first
- Runs worker loop to process tasks
- Automatically creates new tasks when results arrive (recursion)

**Options**:
- No options

**Exit**:
- Press `Ctrl+C` to stop gracefully

## flick:download

Download photos of a user to local storage.

```bash
# Docker
docker-compose exec app php artisan flick:download {nsid} [--limit=LIMIT] [--force]

# Manual
php artisan flick:download {nsid} [--limit=LIMIT] [--force]
```

**Arguments**:
- `nsid` (required) - User NSID

**Options**:
- `--limit=LIMIT` (optional) - Limit number of photos to download
- `--force` (optional) - Force re-download even if already downloaded

**Examples**:
```bash
# Download tất cả ảnh
php artisan flick:download 12345678@N00

# Download giới hạn 100 ảnh
php artisan flick:download 12345678@N00 --limit=100

# Force download lại
php artisan flick:download 12345678@N00 --force
```

**Output Path**:
```
media/{nsid}/photos/{flickr_id}_{secret}.{ext}
```

## flick:retry

Retry failed tasks.

```bash
# Docker
docker-compose exec app php artisan flick:retry [--all] [--task-id=ID]

# Manual
php artisan flick:retry [--all] [--task-id=ID]
```

**Options**:
- `--all` - Retry all failed tasks
- `--task-id=ID` - Retry a specific task

**Examples**:
```bash
# Retry tất cả
php artisan flick:retry --all

# Retry một task
php artisan flick:retry --task-id=123
```

**Behavior**:
- Chỉ retry tasks có `retry_count < max_retries`
- Reset status về `pending`
- Tăng `retry_count`

## flick:cleanup

Dọn dẹp dữ liệu cũ.

```bash
php artisan flick:cleanup [--older-than=DAYS]
```

**Options**:
- `--older-than=DAYS` (optional) - Xóa tasks/contacts/photos cũ hơn N days. Mặc định: 30.

**Examples**:
```bash
# Cleanup tasks cũ hơn 30 days
php artisan flick:cleanup

# Cleanup tasks cũ hơn 7 days
php artisan flick:cleanup --older-than=7
```

**Behavior**:
- Xóa completed tasks cũ
- Xóa soft-deleted contacts/photos cũ
- Giữ lại monitored contacts

## flick:monitor

Đánh dấu một user để monitor (auto-download).

```bash
php artisan flick:monitor {nsid}
```

**Arguments**:
- `nsid` (required) - NSID của user

**Examples**:
```bash
php artisan flick:monitor 12345678@N00
```

**Behavior**:
- Set `is_monitored = true` cho contact
- Tự động tạo `DOWNLOAD_PHOTOS` task khi có photos mới

## flick:like

Like photos của một user.

```bash
php artisan flick:like {nsid} [--limit=LIMIT]
```

**Arguments**:
- `nsid` (required) - NSID của user

**Options**:
- `--limit=LIMIT` (optional) - Giới hạn số lượng ảnh like

**Examples**:
```bash
# Like tất cả ảnh
php artisan flick:like 12345678@N00

# Like giới hạn 10 ảnh
php artisan flick:like 12345678@N00 --limit=10
```

**Note**: Cần OAuth token để like photos.

## flick:refresh

Refresh data của một user.

```bash
php artisan flick:refresh {nsid}
```

**Arguments**:
- `nsid` (required) - NSID của user

**Examples**:
```bash
php artisan flick:refresh 12345678@N00
```

**Behavior**:
- Xóa photos cũ của user
- Tạo task `FETCH_PHOTOS` mới

## flick:stats

Hiển thị thống kê tổng quan.

```bash
php artisan flick:stats
```

**Output**:
```
Contacts:
  Total: 150
  Monitored: 5

Photos:
  Total: 5000
  Downloaded: 3000
  Not downloaded: 2000

Tasks:
  Pending: 10
  Processing: 2
  Completed: 500
  Failed: 5
```

## flick:test-mock

Test với mock data (development only).

```bash
php artisan flick:test-mock
```

**Behavior**:
- Tạo mock contacts và photos
- Dùng để test UI/API mà không cần crawl thật

## Common Options

Các options chung cho tất cả commands:

### Verbose Output

```bash
php artisan flick:crawl -v
php artisan flick:crawl -vv
php artisan flick:crawl -vvv
```

### Quiet Mode

```bash
php artisan flick:crawl -q
```

### Help

```bash
php artisan flick:crawl --help
```

## Command Aliases

Có thể tạo aliases trong shell:

```bash
# ~/.zshrc hoặc ~/.bashrc
alias flick-crawl='php artisan flick:crawl'
alias flick-download='php artisan flick:download'
alias flick-stats='php artisan flick:stats'
```

## Running Commands in Background

### Using nohup

```bash
nohup php artisan flick:crawl 12345678@N00 > crawl.log 2>&1 &
```

### Using screen

```bash
screen -S crawl
php artisan flick:crawl 12345678@N00
# Press Ctrl+A then D to detach
```

### Using tmux

```bash
tmux new -s crawl
php artisan flick:crawl 12345678@N00
# Press Ctrl+B then D to detach
```

## Scheduling Commands

Thêm vào `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Retry failed tasks mỗi giờ
    $schedule->command('flick:retry --all')
        ->hourly();
    
    // Cleanup mỗi ngày
    $schedule->command('flick:cleanup')
        ->daily();
    
    // Stats mỗi 6 giờ
    $schedule->command('flick:stats')
        ->everySixHours();
}
```

Sau đó chạy scheduler:

```bash
php artisan schedule:work
```

Hoặc với cron:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Error Handling

### Exit Codes

- `0` - Success
- `1` - General error
- `2` - Invalid arguments

### Logging

Tất cả commands log vào `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log
```

## Best Practices

1. **Kiểm tra stats trước khi crawl lớn**:
   ```bash
   php artisan flick:stats
   ```

2. **Bắt đầu với depth nhỏ**:
   ```env
   FLICK_MAX_DEPTH=2
   ```

3. **Monitor storage khi download**:
   ```bash
   du -sh media/
   ```

4. **Retry failed tasks định kỳ**:
   ```bash
   php artisan flick:retry --all
   ```

5. **Cleanup old data định kỳ**:
   ```bash
   php artisan flick:cleanup
   ```

## Troubleshooting

### Command không chạy

```bash
# Kiểm tra module enabled
php artisan module:list

# Clear cache
php artisan optimize:clear
```

### Command bị stuck

- Kiểm tra FlickrHub có đang chạy không
- Kiểm tra database connection
- Xem logs: `tail -f storage/logs/laravel.log`

### Memory limit

```bash
php -d memory_limit=512M artisan flick:crawl
```

