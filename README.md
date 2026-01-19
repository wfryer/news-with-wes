# News with Wes Fryer (v3.0)

A high-performance, PHP-powered RSS aggregator and static site generator that curates personal media feeds into a retro-futuristic, "Jetsons-inspired" dashboard. This project is live at **[news.wesfryer.com](https://news.wesfryer.com)**.

---

## 🚀 Overview

This project functions as a background engine that fetches content from multiple platforms, processes it for consistency, and outputs a single, lightweight static `index.html` file. This approach ensures the public-facing site loads instantly and places zero load on server-side databases.

## ✨ Core Features

* **Multi-Platform Aggregation**: Fetches and combines items from Flipboard, WordPress, Mastodon, and multiple Substacks.
* **Wes' Substacks**: A unified feed category that consolidates several Substack publications—including Resist and Heal, EdTechSR, and IndivisibleCLT—into one streamlined section.
* **Smart Mastodon Parsing**: Decodes HTML entities to extract the *actual* article link shared in a Mastodon post rather than just linking back to the social media thread.
* **Rich Visuals**: Automatically fetches and caches OpenGraph (OG) preview images for articles for 7 days to create a modern, card-based layout.
* **Optimized Performance**:
    * **Per-Feed Limits**: Configured to fetch up to 20 items per source to ensure variety.
    * **Deduplication**: Automatically detects and removes duplicate URLs across different feeds.
    * **Static Generation**: The script runs via automation, meaning visitors see a flat HTML file with no server-side processing required per visit.
* **Jetsons Aesthetic**: Custom "bubble-retro" CSS styling featuring sky blue gradients and warm coral/orange accents to match the "News with Wes Fryer" banner.

## 🛠️ Tech Stack

* **Backend**: PHP 8.1+ using cURL for robust external URL fetching.
* **Frontend**: HTML5, CSS3, and vanilla JavaScript for client-side filtering.
* **Data Handling**: XML/RSS parsing and OpenGraph metadata extraction.

## 📂 File Structure

```text
/[project_directory]/
├── generate.php      # Main engine; handles fetching, caching, and generation.
├── index.html        # Generated static output (visible to users).
├── pinned.txt        # Simple text file to manage URLs of "pinned" featured posts.
├── banner.jpg        # Custom project header image.
├── error.log         # Log for monitoring performance and errors.
└── cache/            # Directory for storing fetched images and feed data.
⚙️ Installation & Setup
1. Upload Files
Upload the project files to your preferred server directory.

2. Configure Permissions
Ensure the server has write permissions for the directory so it can create the index.html and cache/ files.

3. Set Up Automation
To keep the feed fresh, set up a cron job to run every 30 minutes.

Frequency: 0,30 * * * *

Command Template: [path_to_php_binary] [path_to_generate.php] >> [path_to_error.log] 2>&1

🔧 Configuration
To add or modify feeds, edit the $feeds array in generate.php. The current configuration includes:

Flipboard: iReading by Wes.

Cook With Wes: WordPress feed with a custom fire emoji icon.

Wes' Substacks: 6 combined publications including Resist and Heal and EdTech Situation Room.

Federated Reader: Personal Mastodon channel.

Created as a "vibe coding" experiment between Wes Fryer and Claude.
