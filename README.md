# News with Wes Fryer (v3.0)

A high-performance, PHP-powered RSS aggregator and static site generator that curates personal media feeds into a retro-futuristic, "Jetsons-inspired" dashboard. This project is live at **[news.wesfryer.com](https://news.wesfryer.com)**.

---

## 🚀 Overview

This project functions as a background engine that fetches content from multiple platforms, processes it for consistency, and outputs a single, lightweight static `index.html` file. By generating a flat file via cron job, the public-facing site loads instantly and places zero load on server-side databases.

## ✨ Core Features

* **Multi-Platform Aggregation**: Fetches and combines items from Flipboard, WordPress (Cook With Wes), Mastodon, and several Substack publications.
* **Unified Substack Category**: Consolidates multiple Substack feeds—including *Resist and Heal*, *EdTechSR*, *IndivisibleCLT*, *Confronting Whiteness*, *Wfryer*, and *Heal Our Culture*—into a single "Wes' Blogs" filter category.
* **Smart Mastodon Parsing**: Extracts the *actual* article link shared in a Mastodon post rather than just linking back to the social media thread.
* **Automated Visuals**: Automatically fetches and caches OpenGraph (OG) preview images for 7 days, providing a modern, card-based layout.
* **Jetsons Aesthetic**: Custom "bubble-retro" CSS styling featuring sky blue gradients and a dedicated orange accent for the Substack branding.
* **Optimized Performance**:
    * **Deduplication**: Automatically detects and removes duplicate URLs across different feeds.
    * **Per-Feed Limits**: Configured to fetch up to 20 items per source to maintain a fresh and diverse feed.
    * **Zero-Database Architecture**: Uses flat files and a background PHP generator for maximum security and speed.

## 🛠️ Tech Stack

* **Backend**: PHP 8.1+ (using cURL for robust external URL fetching).
* **Frontend**: HTML5, CSS3 (Flexbox/Grid), and vanilla JavaScript for client-side filtering.
* **Data Handling**: XML/RSS parsing and OpenGraph metadata extraction.
* **Automation**: Cron-driven task execution.

## ⚙️ Installation & Setup
1. Upload Files
Upload the project files to your server directory. Ensure the cache/ directory exists and is writable by the server.

2. Configure Permissions
The server must have write permissions for the project directory to generate the index.html and store cached images.

3. Set Up Automation
To keep the feed current, set up a cron job to run every 30 minutes.

Frequency: 0,30 * * * *

Command Template: [path_to_php_binary] /[path_to_project]/generate.php >> /[path_to_project]/error.log 2>&1

## 🔧 Configuration
To add or modify feeds, edit the $feeds array in generate.php. Each feed entry supports custom icons, colors, and unique slugs for the sidebar filtering system.

Current primary categories:

Flipboard: iReading by Wes.

Wes' Blogs: Unified filter for all Substack-based publications (includes orange branding).

Cook With Wes: WordPress-based cooking and recipe feed.

Federated Reader: Curated Mastodon social feed.


## 📂 File Structure

```text
/[project_directory]/
├── generate.php      # The main engine; handles fetching, caching, and generation.
├── index.html        # The generated static output (visible to users).
├── pinned.txt        # Text file to manage URLs of "pinned" featured posts.
├── banner.jpg        # Custom project header image.
├── error.log         # Log for monitoring performance and cron job errors.
└── cache/            # Directory for storing fetched images and feed data.

