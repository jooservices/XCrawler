# XCrawler

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**XCrawler** is a powerful Flickr API crawler and data management system built on Laravel 12. It supports recursive crawling of contacts, photos, and favorites with automatic download capabilities, retry mechanisms, and Telegram notifications.

## ✨ Key Features

- 🔄 **Recursive Crawling**: Automatically crawl contacts → photos → contacts of contacts with depth control
- 📥 **Auto-download**: Automatically download images to local storage for monitored contacts
- 🔁 **Retry Mechanism**: Automatically retry failed tasks with configurable retry count
- 📊 **Dashboard API**: API endpoints to view stats, tasks, contacts, and photos
- 🔔 **Telegram Notifications**: Get notified via Telegram bot when tasks complete
- 🎯 **Priority System**: Priority-based task processing system
- 🔗 **URL Resolution**: Support crawling from Flickr profile URLs (auto-resolve to NSID)
- 📦 **Modular Architecture**: Uses Laravel Modules for code organization
- 🐳 **Docker Ready**: One-command setup with Docker Compose

## 🏗️ Architecture

XCrawler uses a 2-tier architecture:

1. **FlickrHub** (Proxy Service): Handles rate limiting and queue management for Flickr API
2. **XCrawler** (Crawler Service): Flick module handles crawl logic, data storage, and task management

```
XCrawler → FlickrHub → Flickr API
    ↑           ↓
    └─── Callback ───┘
```

## 🚀 Quick Start with Docker (Recommended)

The easiest way to get started is using Docker Compose:

```bash
# Clone repository
git clone <repository-url>
cd XCrawler

# Copy environment file
cp .env.example .env

# Edit .env and add your configuration (see Configuration section)

# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Build frontend assets
docker-compose exec app npm run build

# Start crawling
docker-compose exec app php artisan flick:crawl 12345678@N00
```

That's it! The application is now running at `http://localhost:8080`

### Docker Services

- **app**: Laravel application (port 8080)
- **flickrhub**: FlickrHub proxy service (port 8000)
- **mysql**: Database (port 3306)
- **redis**: Cache and queue (port 6379)

## 📋 System Requirements

### For Docker (Recommended)
- Docker Desktop or Docker Engine
- Docker Compose 2.0+

### For Manual Installation
- PHP 8.2+
- Composer
- Node.js 18+ & npm
- SQLite (or MySQL/PostgreSQL)
- FlickrHub service running (see [flickrhub/README.md](flickrhub/README.md))

## ⚙️ Configuration

Add the following environment variables to `.env`:

```env
# FlickrHub Configuration
FLICKR_HUB_URL=http://flickrhub:8000
FLICKR_CALLBACK_URL=http://app/api/flick/callback

# Crawl Settings
FLICK_MAX_DEPTH=3

# Telegram Notifications (optional)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

# Database (for Docker)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=xcrawler
DB_USERNAME=xcrawler
DB_PASSWORD=xcrawler
```

See [docs/configuration.md](docs/configuration.md) for detailed configuration.

## 📖 Usage

### Start Crawling

```bash
# Using Docker
docker-compose exec app php artisan flick:crawl 12345678@N00

# Or from URL
docker-compose exec app php artisan flick:crawl https://www.flickr.com/people/username/

# Or manually (if not using Docker)
php artisan flick:crawl 12345678@N00
```

### Other Commands

```bash
# Download photos
docker-compose exec app php artisan flick:download {nsid} [--limit=100]

# Retry failed tasks
docker-compose exec app php artisan flick:retry [--all]

# View stats
docker-compose exec app php artisan flick:stats

# Monitor contact
docker-compose exec app php artisan flick:monitor {nsid}

# Like photos
docker-compose exec app php artisan flick:like {nsid}
```

See [docs/commands.md](docs/commands.md) for the complete command list.

## 📡 API Endpoints

### Dashboard API

- `GET /api/flick/stats` - Overview statistics
- `GET /api/flick/tasks` - Task list
- `GET /api/flick/contacts` - Contact list
- `GET /api/flick/photos` - Photo list
- `POST /api/flick/commands` - Execute commands

See [docs/api.md](docs/api.md) for detailed API documentation.

## 📁 Project Structure

```
XCrawler/
├── app/                    # Application core
├── Modules/
│   └── Flick/              # Flick module
│       ├── app/
│       │   ├── Console/    # Artisan commands
│       │   ├── Http/       # Controllers
│       │   ├── Models/     # Eloquent models
│       │   └── Services/   # Business logic
│       ├── database/       # Migrations
│       └── routes/         # Module routes
├── flickrhub/              # FlickrHub API spec
├── docs/                   # Documentation
├── Dockerfile              # Docker image definition
└── docker-compose.yml      # Docker Compose configuration
```

See [docs/architecture.md](docs/architecture.md) for more details on architecture.

## 📚 Documentation

- [Installation Guide](docs/installation.md) - Detailed installation instructions
- [Configuration](docs/configuration.md) - System configuration
- [Usage Guide](docs/usage.md) - Usage guide
- [Architecture](docs/architecture.md) - System architecture
- [API Documentation](docs/api.md) - API endpoints
- [Commands Reference](docs/commands.md) - Command list

## 🐳 Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands
docker-compose exec app php artisan {command}

# Access shell
docker-compose exec app bash

# Rebuild containers
docker-compose up -d --build

# Stop and remove volumes
docker-compose down -v
```

## 🔧 Development

### With Docker

```bash
# Start development environment
docker-compose up -d

# Run tests
docker-compose exec app composer run test

# Code formatting
docker-compose exec app ./vendor/bin/pint
```

### Without Docker

```bash
# Run development server
composer run dev

# Run tests
composer run test

# Code formatting
./vendor/bin/pint
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- Module system by [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules)
- Flickr API integration via FlickrHub

---

**Made with ❤️ by JOOservices**
