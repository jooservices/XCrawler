# Installation Guide

Complete guide to install XCrawler from scratch.

## System Requirements

### Required Software

- **PHP 8.2+** with extensions:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PCRE
  - PDO
  - Tokenizer
  - XML
- **Composer** 2.x
- **Node.js** 18+ and **npm**
- **SQLite** (or MySQL/PostgreSQL)
- **FlickrHub service** running (see [flickrhub/README.md](../flickrhub/README.md))

## Installation Methods

### Method 1: Docker (Recommended)

The easiest way to get started:

```bash
# Clone repository
git clone <repository-url>
cd XCrawler

# Copy environment file
cp .env.example .env

# Edit .env and configure (see Configuration section)

# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Build frontend assets
docker-compose exec app npm run build
```

That's it! The application is now running at `http://localhost:8080`

### Method 2: Manual Installation

#### Step 1: Clone Repository

```bash
git clone <repository-url>
cd XCrawler
```

#### Step 2: Install PHP Dependencies

```bash
composer install
```

#### Step 3: Install Node.js Dependencies

```bash
npm install
```

#### Step 4: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 5: Configure Database

**Option 1: SQLite (Default)**

SQLite is pre-configured. Just ensure the `database/database.sqlite` file exists:

```bash
touch database/database.sqlite
```

**Option 2: MySQL/PostgreSQL**

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xcrawler
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Step 6: Run Migrations

```bash
php artisan migrate
```

#### Step 7: Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

#### Step 8: Configure FlickrHub

Ensure FlickrHub service is running. See [flickrhub/README.md](../flickrhub/README.md) for setup instructions.

Update `.env`:

```env
FLICKR_HUB_URL=http://localhost:8000
FLICKR_CALLBACK_URL=http://host.docker.internal/api/flick/callback
```

**Note**: If running on Docker, use `host.docker.internal`. If running locally, use `http://localhost:8000`.

#### Step 9: (Optional) Configure Telegram

If you want to receive notifications via Telegram:

1. Create a bot with [@BotFather](https://t.me/botfather)
2. Get the bot token
3. Get your chat ID (you can use [@userinfobot](https://t.me/userinfobot))
4. Add to `.env`:

```env
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
```

#### Step 10: Verify Installation

```bash
# Check version
php artisan --version

# Check modules
php artisan module:list

# Run stats command
php artisan flick:stats
```

## Docker Setup Details

### Services Included

- **app**: Laravel application (PHP-FPM)
- **nginx**: Web server
- **mysql**: Database server
- **redis**: Cache and queue
- **flickrhub**: FlickrHub proxy service
- **queue**: Queue worker

### Docker Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop services
docker-compose down

# Rebuild containers
docker-compose up -d --build

# Execute commands
docker-compose exec app php artisan {command}

# Access shell
docker-compose exec app bash
```

### Ports

- **8080**: Web application
- **8000**: FlickrHub service
- **3306**: MySQL
- **6379**: Redis

## Troubleshooting

### "Class not found" Error

```bash
composer dump-autoload
php artisan optimize:clear
```

### "Module not found" Error

```bash
php artisan module:enable Flick
```

### FlickrHub Connection Error

- Check if FlickrHub is running: `curl http://localhost:8000/api/health`
- Check `FLICKR_HUB_URL` in `.env`
- Check firewall/network settings

### Permission Errors

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# If using SQLite
chmod 664 database/database.sqlite
```

### Docker Issues

```bash
# Check container status
docker-compose ps

# View logs
docker-compose logs app

# Restart services
docker-compose restart

# Remove and recreate
docker-compose down -v
docker-compose up -d --build
```

## Next Steps

After installation, see:

- [Configuration Guide](configuration.md) - Detailed configuration
- [Usage Guide](usage.md) - Get started using
- [Commands Reference](commands.md) - Command list
