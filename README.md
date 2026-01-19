# News with Wes Fryer - RSS Feed Aggregator

A static site generator that aggregates RSS feeds from Flipboard, Substack, and Mastodon into a beautiful Jetsons-themed webpage.

## Files Included

```
news/
├── generate.php      # Main PHP script that fetches feeds and generates HTML
├── pinned.txt        # List of URLs to pin to the top (edit this!)
├── banner.jpg        # Your banner image (upload your own)
├── index.html        # Generated static page (created by generate.php)
├── cache/            # Feed cache directory (created automatically)
└── README.md         # This file
```

## Quick Setup

### 1. Upload Files to Your VPS

Upload all files to your web directory. For `wesfryer.com/news/`:

```bash
# SSH into your Liquid Web VPS
ssh your-username@your-server-ip

# Create the directory
mkdir -p /var/www/wesfryer.com/public_html/news

# Upload files via SFTP/SCP, or use rsync:
rsync -avz ./news/ user@server:/var/www/wesfryer.com/public_html/news/
```

### 2. Upload Your Banner Image

Upload `banner.jpg` to the same directory. Make sure the filename matches what's in `generate.php` (or edit the config).

### 3. Set Permissions

```bash
# Make cache directory writable
chmod 755 /var/www/wesfryer.com/public_html/news/
chmod 644 /var/www/wesfryer.com/public_html/news/*.php
chmod 644 /var/www/wesfryer.com/public_html/news/*.txt
chmod 644 /var/www/wesfryer.com/public_html/news/*.jpg

# Create cache directory with write permissions
mkdir -p /var/www/wesfryer.com/public_html/news/cache
chmod 755 /var/www/wesfryer.com/public_html/news/cache
```

### 4. Test the Generator

Run the script manually first to make sure it works:

```bash
cd /var/www/wesfryer.com/public_html/news
php generate.php
```

You should see output like:
```
News with Wes - RSS Feed Generator
===================================
Started: 2026-01-18 20:30:00

Fetching: Flipboard (https://flipboard.com/@wfryer/ireading-by-wes-20i475olz.rss)
  Found 15 items
Fetching: Substack (https://resistandheal.substack.com/feed)
  Found 10 items
Fetching: Mastodon (https://mastodon.social/@federatedreader.rss)
  Found 8 items

Total items collected: 33
...
Successfully generated: /var/www/wesfryer.com/public_html/news/index.html
```

### 5. Set Up Cron Job (Every 30 Minutes)

Edit your crontab:

```bash
crontab -e
```

Add this line (adjust the path to match your setup):

```cron
*/30 * * * * /usr/bin/php /var/www/wesfryer.com/public_html/news/generate.php >> /var/www/wesfryer.com/public_html/news/cache/cron.log 2>&1
```

**Breaking down the cron syntax:**
- `*/30` = Every 30 minutes
- `* * * *` = Every hour, every day, every month, every day of week
- `/usr/bin/php` = Path to PHP (run `which php` to find yours)
- `>> .../cron.log 2>&1` = Append output to log file for debugging

Verify your crontab was saved:

```bash
crontab -l
```

### 6. Visit Your Site

Open `https://wesfryer.com/news/` in your browser!

---

## Configuration

Edit the `$config` array at the top of `generate.php` to customize:

```php
$config = [
    'feeds' => [
        // Add, remove, or modify RSS feeds here
    ],
    'max_items' => 30,           // Total items to display
    'cache_duration' => 1800,    // Cache feeds for 30 minutes (1800 seconds)
    'site_title' => 'News with Wes Fryer',
    'site_url' => 'https://wesfryer.com/news/',
    'banner_image' => 'banner.jpg'
];
```

### Changing the Mastodon Tag Filter

By default, only Mastodon posts with `#news` are included. To change this, edit the `filter_tag` in the feeds config:

```php
[
    'url' => 'https://mastodon.social/@federatedreader.rss',
    'name' => 'Mastodon',
    'slug' => 'mastodon',
    'color' => '#6364ff',
    'icon' => '🐘',
    'filter_tag' => 'news'  // Change to another tag or remove this line for all posts
]
```

---

## Pinning Posts

To pin important posts to the top of the feed:

1. Open `pinned.txt` in a text editor
2. Add the full URL of the post on a new line
3. Save the file
4. Wait for the next cron run, or run `php generate.php` manually

Example `pinned.txt`:
```
# My pinned posts
https://resistandheal.substack.com/p/important-announcement
https://mastodon.social/@federatedreader/112345678901234567
```

Pinned posts appear at the top with a 📌 badge.

---

## Troubleshooting

### "Could not fetch feed" errors

- Check if the feed URL is accessible: `curl -I <feed-url>`
- Make sure your server can make outbound HTTP requests
- Check if `allow_url_fopen` is enabled in PHP: `php -i | grep allow_url_fopen`

### Permissions errors

```bash
# Make sure the web user can write to cache
chown -R www-data:www-data /var/www/wesfryer.com/public_html/news/cache
chmod 755 /var/www/wesfryer.com/public_html/news/cache
```

### Cron not running

Check cron logs:
```bash
grep CRON /var/log/syslog
```

Check your cron log:
```bash
tail -f /var/www/wesfryer.com/public_html/news/cache/cron.log
```

### Find your PHP path

```bash
which php
# Usually: /usr/bin/php or /usr/local/bin/php
```

---

## Moving to news.wesfryer.com

When you're ready to use a subdomain:

1. Add DNS record: `news.wesfryer.com` → your VPS IP (A record)
2. Set up the virtual host in Apache/Nginx
3. Update `$config['site_url']` in `generate.php`
4. Move files or update document root

---

## Credits

- Built with Claude (Anthropic) for Wesley Fryer
- Design inspired by The Jetsons
- Powered by open web RSS feeds

## License

MIT License - Feel free to modify and share!
