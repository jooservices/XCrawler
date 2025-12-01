# API Documentation

XCrawler API documentation.

## Base URL

```
http://localhost:8080/api
```

## Authentication

Currently, the API does not require authentication for public endpoints. Endpoints with `auth:sanctum` middleware will require a token.

## Endpoints

### Dashboard API

#### GET /flick/stats

Get overview statistics.

**Response**:
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

#### GET /flick/tasks

Get task list.

**Query Parameters**:
- `page` (int, default: 1) - Page number
- `per_page` (int, default: 20) - Items per page

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "type": "FETCH_CONTACTS",
      "status": "completed",
      "contact": "username",
      "updated_at": "2 hours ago",
      "retry_count": 0
    }
  ],
  "current_page": 1,
  "last_page": 10,
  "total": 200
}
```

#### GET /flick/contacts

Get contact list.

**Query Parameters**:
- `page` (int, default: 1)
- `per_page` (int, default: 50)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "nsid": "12345678@N00",
      "username": "username",
      "realname": "Real Name",
      "location": "Location",
      "photos_count": 100,
      "contacts_count": 50,
      "is_monitored": false,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "total": 250
}
```

#### GET /flick/photos

Lấy danh sách photos.

**Query Parameters**:
- `page` (int, default: 1)
- `per_page` (int, default: 50)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "flickr_id": "51123456789",
      "owner": {
        "id": 1,
        "nsid": "12345678@N00",
        "username": "username"
      },
      "title": "Photo Title",
      "is_downloaded": true,
      "local_path": "media/12345678@N00/photos/51123456789_secret.jpg",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 100,
  "total": 5000
}
```

#### POST /flick/commands

Execute commands từ API.

**Request Body**:
```json
{
  "command": "crawl",
  "params": {
    "url": "https://www.flickr.com/people/username/"
  }
}
```

**Commands**:
- `crawl` - Bắt đầu crawl
  - `params.url` (string, optional) - NSID hoặc URL
- `download` - Download ảnh
  - `params.nsid` (string, required) - NSID
  - `params.limit` (int, optional) - Giới hạn số lượng
- `retry` - Retry failed tasks
  - `params.all` (bool, optional) - Retry tất cả
- `cleanup` - Cleanup old data
  - `params.older_than` (string, optional) - Ví dụ: "30days"

**Response**:
```json
{
  "success": true,
  "message": "Command executed successfully",
  "output": "Command output..."
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Command failed: error message"
}
```

### Callback API

#### POST /flick/callback

Endpoint để FlickrHub gửi callback (không có authentication).

**Request Body**:
```json
{
  "request_id": 123,
  "status": "completed",
  "result": {
    "contacts": {
      "contact": [...]
    }
  },
  "data": {...},  // Alternative to result
  "error": null
}
```

**Response**:
```json
{
  "success": true,
  "message": "Processed",
  "task_id": 456
}
```

### Protected API (v1)

Các endpoints này yêu cầu `auth:sanctum` middleware.

#### GET /v1/flicks

Lấy danh sách flicks (resource).

#### POST /v1/flicks

Tạo flick mới.

#### GET /v1/flicks/{id}

Lấy flick theo ID.

#### PUT /v1/flicks/{id}

Update flick.

#### DELETE /v1/flicks/{id}

Xóa flick.

## Error Responses

### 400 Bad Request

```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "field": ["Error message"]
  }
}
```

### 404 Not Found

```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 500 Internal Server Error

```json
{
  "success": false,
  "message": "Internal server error"
}
```

## Examples

### cURL Examples

#### Get Stats

```bash
curl http://localhost/api/flick/stats
```

#### Get Tasks

```bash
curl "http://localhost/api/flick/tasks?page=1&per_page=20"
```

#### Execute Crawl Command

```bash
curl -X POST http://localhost/api/flick/commands \
  -H "Content-Type: application/json" \
  -d '{
    "command": "crawl",
    "params": {
      "url": "https://www.flickr.com/people/username/"
    }
  }'
```

#### Execute Download Command

```bash
curl -X POST http://localhost/api/flick/commands \
  -H "Content-Type: application/json" \
  -d '{
    "command": "download",
    "params": {
      "nsid": "12345678@N00",
      "limit": 100
    }
  }'
```

### JavaScript Examples

#### Fetch Stats

```javascript
fetch('http://localhost/api/flick/stats')
  .then(res => res.json())
  .then(data => console.log(data));
```

#### Execute Command

```javascript
fetch('http://localhost/api/flick/commands', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    command: 'crawl',
    params: {
      url: 'https://www.flickr.com/people/username/'
    }
  })
})
  .then(res => res.json())
  .then(data => console.log(data));
```

### PHP Examples

#### Using Laravel HTTP Client

```php
use Illuminate\Support\Facades\Http;

// Get stats
$response = Http::get('http://localhost/api/flick/stats');
$stats = $response->json();

// Execute command
$response = Http::post('http://localhost/api/flick/commands', [
    'command' => 'crawl',
    'params' => [
        'url' => 'https://www.flickr.com/people/username/'
    ]
]);
$result = $response->json();
```

## Rate Limiting

Hiện tại không có rate limiting trên API endpoints. Nên implement rate limiting cho production.

## CORS

Nếu cần access từ frontend khác domain, cấu hình CORS trong `config/cors.php`.

## Webhooks

Callback endpoint `/api/flick/callback` có thể được sử dụng như webhook từ FlickrHub.

## Testing

### Test với Postman

1. Import collection (nếu có)
2. Set base URL: `http://localhost/api`
3. Test các endpoints

### Test với PHPUnit

```php
public function test_stats_endpoint()
{
    $response = $this->getJson('/api/flick/stats');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'contacts',
            'photos',
            'tasks'
        ]);
}
```

