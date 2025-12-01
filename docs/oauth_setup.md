# FlickrHub OAuth Configuration Guide

This guide explains how to configure OAuth for FlickrHub to enable communication with the Flickr API.

## Prerequisites

1.  A Flickr account.
2.  Access to the Flickr App Garden to create an API key.

## Step 1: Create a Flickr App

1.  Go to [Flickr App Garden](https://www.flickr.com/services/apps/create/).
2.  Click **Request an API Key**.
3.  Choose **Non-Commercial Key** (unless you have a commercial use case).
4.  Fill in the details:
    -   **App Name**: `FlickrHub` (or your preferred name)
    -   **Description**: `Internal tool for crawling and managing Flickr photos.`
5.  Submit the form. You will receive a **Key** and a **Secret**.

## Step 2: Configure Callback URL

1.  In your Flickr App settings, look for the **Authentication Flow** or **Edit Auth Flow** section.
2.  Set the **Callback URL** to:
    ```
    http://localhost:8080/api/flick/callback
    ```
    *Note: If you are running XCrawler on a different domain or port, adjust the URL accordingly. This URL must match the `FLICKR_CALLBACK_URL` in your XCrawler `.env` or `docker-compose.yml`.*

## Step 3: Configure FlickrHub

You need to provide the API Key and Secret to the FlickrHub service.

### Option A: Using `.env` file (Recommended)

1.  Open the `.env` file in the `flickrhub` directory (or the root if running combined).
2.  Add or update the following lines:
    ```env
    FLICKR_KEY=your_api_key_here
    FLICKR_SECRET=your_api_secret_here
    ```

### Option B: Using Docker Environment Variables

If you are using `docker-compose.yml`, you can set these variables directly in the `flickrhub` service definition (if you are running it as part of XCrawler's compose file) or in the separate FlickrHub compose file.

```yaml
environment:
  - FLICKR_KEY=your_api_key_here
  - FLICKR_SECRET=your_api_secret_here
```

## Step 4: Verify Connection

1.  Restart your FlickrHub service to apply changes.
2.  Open the XCrawler Dashboard.
3.  Try to start a crawl task. If OAuth is configured correctly, the request will be processed.
