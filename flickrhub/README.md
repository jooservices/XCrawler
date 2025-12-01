# FlickrHub User Guide

Welcome to **FlickrHub**! This application allows you to easily manage and process Flickr requests. This guide is designed to help you set up and use the application, even if you have no technical background.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Running the Application](#running-the-application)
5. [Usage](#usage)
6. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before you begin, you need to have **Docker Desktop** installed on your computer. Docker allows the application to run in a self-contained environment without messing up your system settings.

- **Download Docker Desktop**: [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)
- Install it and make sure it is running (you should see a whale icon in your taskbar/menu bar).

---

## Installation

1.  **Download the Code**:
    - If you received this as a zip file, extract it to a folder on your computer.
    - If you are using Git, clone the repository:
      ```bash
      git clone https://github.com/jooservices/flickrhub.git
      cd flickrhub
      ```

2.  **Open Terminal**:
    - On Mac: Open "Terminal" (Command + Space, type "Terminal").
    - On Windows: Open "PowerShell" or "Command Prompt".
    - Navigate to the folder where you put the code:
      ```bash
      cd path/to/flickrhub
      ```

---

## Configuration

The application needs some secret keys to talk to Flickr.

1.  **Create the Environment File**:
    Copy the example configuration file to a new file named `.env`:
    ```bash
    cp .env.example .env
    ```
    *(On Windows, you might need to just copy and paste the file and rename it manually)*

2.  **Get Flickr API Keys**:
    - Go to [Flickr App Garden](https://www.flickr.com/services/apps/create/).
    - Request an API Key (choose "Non-Commercial" if just testing).
    - You will get a **Key** and a **Secret**.

3.  **Edit the `.env` File**:
    - Open the `.env` file in any text editor (Notepad, TextEdit, VS Code).
    - Scroll to the bottom and find these lines:
      ```ini
      FLICKR_KEY=
      FLICKR_SECRET=
      ```
    - Paste your keys there. It should look like this:
      ```ini
      FLICKR_KEY=your_long_api_key_here
      FLICKR_SECRET=your_secret_here
      ```
    - Save the file.

---

## Running the Application

Now we will start the application using Docker.

1.  **Start the System**:
    Run this command in your terminal (make sure you are inside the `flickrhub` folder):
    ```bash
    docker-compose up -d --build
    ```
    *This might take a few minutes the first time as it downloads necessary software.*

2.  **Install Dependencies**:
    Once the above command finishes, run:
    ```bash
    docker-compose exec app composer install
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate
    ```

3.  **Check if it's running**:
    Open your web browser and go to: [http://localhost:8000](http://localhost:8000)
    You should see the FlickrHub welcome page.

---

## Usage

### 1. Making a Request
To send a request to Flickr, you can use the API. Since there is no graphical interface for this yet, you can use a tool like **Postman** or your browser for simple checks.

**Endpoint**: `POST http://localhost:8000/api/flickr/request`

**Example Body (JSON)**:
```json
{
    "photo_id": "123456789",
    "action": "get_info"
}
```

### 2. Checking Status
The system processes requests in the background. You can check the logs to see if it's working:
```bash
docker-compose logs -f app
```
Press `Ctrl + C` to exit the logs.

---

## Troubleshooting

-   **"Port is already allocated" error**:
    This means another program is using port 8000 or 3306. Make sure to close other web servers or database applications.

-   **Database connection failed**:
    Wait a few seconds and try again. The database sometimes takes a moment to start up.

-   **Permission denied**:
    If you are on Linux/Mac and get permission errors, try running commands with `sudo` (e.g., `sudo docker-compose up`).

---

## 7. Using `flickr.favorites.getContext`

### Overview
The `flickr.favorites.getContext` method returns the previous and next favorite photos for a given photo in a user's favorites list.

### Endpoint Options
You can call this method in two ways:

#### A. Dedicated endpoint (recommended)
```
POST http://localhost:8000/api/flickr/favorites/getContext
```

#### B. Generic request endpoint
```
POST http://localhost:8000/api/flickr/request
```
with `method: "flickr.favorites.getContext"` in the JSON body.

### Request Payload (JSON)
```json
{
  "photo_id": "51123456789",
  "user_id": "12345678@N00",          // optional – whose favorites to query
  "per_page": 10,                     // optional – pagination
  "page": 1,                          // optional – pagination
  "callback_url": "https://example.com/flickr/callback"
}
```

### Response Flow
1. The API immediately returns a **202 Accepted** with a `request_id` and status `queued`.
2. When Flickr finishes processing, FlickrHub POSTs the result to the `callback_url` you supplied.

#### Example Immediate Response
```json
{
  "request_id": 57,
  "status": "queued"
}
```

#### Example Callback Payload
```json
{
  "prevphoto": { "id": "51123456780", "owner": "12345678@N00", "title": "Sunset" },
  "nextphoto": { "id": "51123456790", "owner": "12345678@N00", "title": "Mountain" },
  "total": 342,
  "page": 1,
  "perpage": 10,
  "photos": [ /* array of favorite photos */ ]
}
```

### Testing with cURL
```bash
curl -X POST http://localhost:8000/api/flickr/favorites/getContext \
  -H "Content-Type: application/json" \
  -d '{
        "photo_id": "51123456789",
        "callback_url": "https://example.com/flickr/callback"
      }'
```

### Adding the Endpoint (if you chose option A)
The endpoint is already defined in the OpenAPI spec (`openapi.yaml`). No additional code changes are required beyond the existing generic request handling.

### Troubleshooting
- **404 Not Found** – Ensure the route `/api/flickr/favorites/getContext` is registered (see `routes/api.php`).
- **422 Validation error** – Verify `photo_id` and `callback_url` are present and correctly formatted.
- **No callback received** – Check that your callback URL is reachable from the Docker container (use `curl` inside the container or expose via ngrok).

---

**Need more help?** Contact the support team or open an issue on GitHub.
