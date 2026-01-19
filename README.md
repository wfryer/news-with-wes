# News with Wes Fryer (v3.0)

A high-performance, PHP-powered RSS aggregator and static site generator that curates personal media feeds into a retro-futuristic, "Jetsons-inspired" dashboard. This project is live at **[news.wesfryer.com](https://news.wesfryer.com)**.

---

## 🚀 Overview

This project was built to solve the "context wall" issues of standard AI-assisted coding by moving from linear chat logs to a state-managed PHP architecture. It functions as a background engine that fetches content from multiple platforms, processes it for consistency, and outputs a single, lightweight static `index.html` file. This approach ensures the public-facing site loads instantly and places zero load on the server's database.

## ✨ Core Features

* **Multi-Platform Aggregation**: Fetches and combines items from Flipboard, WordPress, Mastodon, and multiple Substacks.
* **Wes' Substacks**: A unified feed category that consolidates six different Substack publications into one streamlined section, including Resist and Heal, EdTechSR, IndivisibleCLT, and others.
* **Smart Mastodon Parsing**: Unlike standard aggregators, this script decodes HTML entities to extract the *actual* article link shared in a Mastodon post rather than just linking back to the social media thread.
* **Rich Visuals**: Automatically fetches and caches OpenGraph (OG) preview images for every article for 7 days to create a modern, card-based layout.
* **Optimized Performance**:
    * **Per-Feed Limits**: Configured to fetch up to 20 items per source to ensure variety across all feeds.
    * **Deduplication**: Automatically detects and removes duplicate URLs across different feeds to prevent repeated content.
    * **Static Generation**: The script runs via cron job, meaning visitors see a flat HTML file with no server-side processing required per visit.
* **Jetsons Aesthetic**: Custom "bubble-retro" CSS styling featuring sky blue gradients and warm coral/orange accents to match the "News with Wes Fryer" banner.

## 🛠️ Tech Stack

* **Backend**: PHP 8.1+ (using cURL for robust external URL fetching).
* **Frontend**: HTML5, CSS3 (Flexbox/Grid), and vanilla JavaScript for client-side filtering.
* **Data Handling**: XML/RSS parsing and OpenGraph metadata extraction.
* **Deployment**: Cron job automation on a Liquid Web VPS.

## 📂 File Structure

```text
/public_html/news/
├── generate.php      # The main engine; handles fetching, caching, and generation.
├── index.html        # The generated static output (visible to users).
├── pinned.txt        # Simple text file to manage URLs of "pinned" featured posts.
├── banner.jpg        # Custom project header image.
├── error.log         # Custom log for monitoring cron job performance and errors.
└── cache/            # Directory for storing fetched images and feed data.
