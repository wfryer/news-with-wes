<?php
//
// News with Wes Fryer - RSS Feed Aggregator
// 
// This script fetches RSS feeds, combines them, and generates a static HTML page.
// Run via cron every 30 minutes (see README for cron syntax)
//
// @author Claude (Anthropic) for Wesley Fryer
// @version 3.0
//

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// ============================================================================
// CONFIGURATION - Edit these settings as needed
// ============================================================================

$config = [
    // RSS Feeds to aggregate
    'feeds' => [
        [
            'url' => 'https://flipboard.com/@wfryer/ireading-by-wes-20i475olz.rss',
            'name' => 'Flipboard',
            'slug' => 'flipboard',
            'color' => '#e12828',
            'icon' => '🎴',
            'link' => 'https://flipboard.com/@wfryer/ireading-by-wes-20i475olz'
        ],
        // Wes' Substacks - all use slug 'substack' so they group together
        [
            'url' => 'https://resistandheal.substack.com/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://edtechsr.substack.com/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://indivisibleclt.substack.com/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://confrontingwhiteness.substack.com/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://wfryer.substack.com/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://healourculture.substack.com/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        // WordPress blogs
        [
            'url' => 'https://www.speedofcreativity.org/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://learningsigns.speedofcreativity.org/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://pocketshare.speedofcreativity.org/feed',
            'name' => "Wes' Blogs",
            'slug' => 'substack',
            'color' => '#ff6719',
            'icon' => '✍️',
            'link' => 'https://wesfryer.com/substack-newsletters/'
        ],
        [
            'url' => 'https://mastodon.social/@federatedreader.rss',
            'name' => '@FederatedReader',
            'slug' => 'mastodon',
            'color' => '#6364ff',
            'icon' => '📡',
            'link' => 'https://mastodon.social/@federatedreader'
        ],
        [
            'url' => 'https://food.wesfryer.com/feed/',
            'name' => '@CookWithWes',
            'slug' => 'cookwithwes',
            'color' => '#e85d04',
            'icon' => '🔥',
            'link' => 'https://food.wesfryer.com'
        ]
    ],
    
    // Output settings
    'max_items_per_feed' => 20,  // Take up to 20 items from each feed
    'output_file' => __DIR__ . '/index.html',
    'cache_dir' => __DIR__ . '/cache',
    'cache_duration' => 1800, // 30 minutes in seconds
    
    // Pinned posts file (one URL per line)
    'pinned_file' => __DIR__ . '/pinned.txt',
    
    // Site settings
    'site_title' => 'News with Wes Fryer',
    'site_url' => 'https://wesfryer.com/news/',
    'banner_image' => 'banner.jpg'
];

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Log a message with timestamp
 */
function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[{$timestamp}] {$message}");
    echo "{$message}\n";
}

/**
 * Fetch URL content with caching (using cURL)
 */
function fetch_url($url, $cache_dir, $cache_duration) {
    $cache_file = $cache_dir . '/' . md5($url) . '.cache';
    
    // Ensure cache directory exists
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
        log_message("Created cache directory: {$cache_dir}");
    }
    
    // Check cache
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        return file_get_contents($cache_file);
    }
    
    // Fetch fresh content using cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'NewsWithWes/3.0 RSS Aggregator',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($content !== false && $http_code === 200) {
        file_put_contents($cache_file, $content);
        return $content;
    }
    
    // Log fetch errors
    if ($error) {
        log_message("ERROR fetching {$url}: {$error}");
    } else {
        log_message("ERROR fetching {$url}: HTTP {$http_code}");
    }
    
    // If fetch failed, return false
    return false;
}

/**
 * Parse RSS/Atom feed XML
 */
function parse_feed($xml_content, $feed_config) {
    $items = [];
    
    if (empty($xml_content)) {
        log_message("WARNING: Empty content for feed {$feed_config['name']}");
        return $items;
    }
    
    // Suppress XML errors
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml_content);
    
    if ($xml === false) {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            log_message("XML ERROR in {$feed_config['name']}: {$error->message}");
        }
        libxml_clear_errors();
        return $items;
    }
    
    // Determine feed type and get items
    $entries = [];
    
    // RSS 2.0
    if (isset($xml->channel->item)) {
        $entries = $xml->channel->item;
    }
    // RSS 1.0 / RDF
    elseif (isset($xml->item)) {
        $entries = $xml->item;
    }
    // Atom
    elseif (isset($xml->entry)) {
        $entries = $xml->entry;
    }
    
    foreach ($entries as $entry) {
        $item = parse_entry($entry, $feed_config);
        
        if ($item !== null) {
            // Apply tag filter if specified
            if (!empty($feed_config['filter_tag'])) {
                $filter_tag = strtolower($feed_config['filter_tag']);
                $has_tag = false;
                
                foreach ($item['tags'] as $tag) {
                    if (strtolower($tag) === $filter_tag) {
                        $has_tag = true;
                        break;
                    }
                }
                
                if (!$has_tag) {
                    continue; // Skip this item
                }
            }
            
            $items[] = $item;
        }
    }
    
    return $items;
}

/**
 * Clean Mastodon description - remove URLs and clean up text
 */
function clean_mastodon_description($description, $article_link) {
    // Decode HTML entities first
    $text = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
    
    // Strip HTML tags
    $text = strip_tags($text);
    
    // Remove the article URL if it appears in the text
    if (!empty($article_link)) {
        // Remove the exact URL
        $text = str_replace($article_link, '', $text);
        
        // Also try without protocol
        $url_no_protocol = preg_replace('#^https?://#', '', $article_link);
        $text = str_replace($url_no_protocol, '', $text);
    }
    
    // Remove any remaining URLs (http/https links)
    $text = preg_replace('#https?://[^\s<>"\']+#i', '', $text);
    
    // Remove hashtags that are just standalone (keep the ones in sentences)
    // This removes lines that are ONLY hashtags
    $lines = explode("\n", $text);
    $cleaned_lines = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip lines that are only hashtags
        if (preg_match('/^(#\w+\s*)+$/', $line)) {
            continue;
        }
        if (!empty($line)) {
            $cleaned_lines[] = $line;
        }
    }
    $text = implode(' ', $cleaned_lines);
    
    // Clean up multiple spaces and trim
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    return $text;
}

/**
 * Parse a single feed entry
 */
function parse_entry($entry, $feed_config) {
    // Get title
    $title = (string) $entry->title;
    
    // Get link (RSS vs Atom)
    $link = '';
    if (isset($entry->link)) {
        if (is_string($entry->link) || (is_object($entry->link) && !isset($entry->link['href']))) {
            $link = (string) $entry->link;
        } else {
            $link = (string) $entry->link['href'];
        }
    }
    
    // Get description/content
    $description = '';
    if (isset($entry->description)) {
        $description = (string) $entry->description;
    } elseif (isset($entry->summary)) {
        $description = (string) $entry->summary;
    } elseif (isset($entry->content)) {
        $description = (string) $entry->content;
    }
    
    // Check for content:encoded (common in RSS)
    $namespaces = $entry->getNamespaces(true);
    if (isset($namespaces['content'])) {
        $content_ns = $entry->children($namespaces['content']);
        if (isset($content_ns->encoded)) {
            $description = (string) $content_ns->encoded;
        }
    }
    
    // Get date
    $date = null;
    if (isset($entry->pubDate)) {
        $date = strtotime((string) $entry->pubDate);
    } elseif (isset($entry->published)) {
        $date = strtotime((string) $entry->published);
    } elseif (isset($entry->updated)) {
        $date = strtotime((string) $entry->updated);
    }
    
    if (!$date) {
        $date = time();
    }
    
    // Get categories/tags
    $tags = [];
    foreach ($entry->category as $cat) {
        $tag = (string) $cat;
        if (empty($tag) && isset($cat['term'])) {
            $tag = (string) $cat['term'];
        }
        if (!empty($tag)) {
            $tags[] = $tag;
        }
    }
    
    // Store the original link for Mastodon (before we replace it with article link)
    $original_link = $link;
    
    // For Mastodon, also extract hashtags from content
    if ($feed_config['slug'] === 'mastodon') {
        preg_match_all('/#(\w+)/', $description, $hashtag_matches);
        if (!empty($hashtag_matches[1])) {
            $tags = array_merge($tags, $hashtag_matches[1]);
        }
        
        // For Mastodon posts without titles, create one from content
        if (empty($title) || $title === $description) {
            $text_content = strip_tags(html_entity_decode($description, ENT_QUOTES, 'UTF-8'));
            // Remove URLs from the title preview
            $text_content = preg_replace('#https?://[^\s<>"\']+#i', '', $text_content);
            $text_content = trim(preg_replace('/\s+/', ' ', $text_content));
            $title = mb_substr($text_content, 0, 100);
            if (mb_strlen($text_content) > 100) {
                $title .= '...';
            }
        }
        
        // Extract the actual article link from Mastodon post content
        // First decode HTML entities, then look for links
        $decoded_description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        
        if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/', $decoded_description, $link_matches)) {
            foreach ($link_matches[1] as $found_link) {
                // Skip Mastodon internal links (hashtags, mentions, the instance itself)
                if (strpos($found_link, 'mastodon.social/tags/') !== false) continue;
                if (strpos($found_link, 'mastodon.social/@') !== false) continue;
                if (strpos($found_link, '/users/') !== false) continue;
                if (strpos($found_link, 'class="mention hashtag"') !== false) continue;
                
                // Use the first external link as the article link
                $link = $found_link;
                break;
            }
        }
    }
    
    $tags = array_unique($tags);
    
    // Get image
    $image = extract_image($entry, $description, $namespaces);
    
    // Skip items without title and description
    if (empty($title) && empty($description)) {
        return null;
    }
    
    // Create description text - with special handling for Mastodon
    if ($feed_config['slug'] === 'mastodon') {
        $description_text = clean_mastodon_description($description, $link);
    } else {
        $description_text = trim(strip_tags($description));
    }
    
    return [
        'title' => html_entity_decode($title, ENT_QUOTES, 'UTF-8'),
        'link' => $link,
        'description' => $description,
        'description_text' => $description_text,
        'date' => $date,
        'tags' => $tags,
        'image' => $image,
        'source' => $feed_config['name'],
        'source_slug' => $feed_config['slug'],
        'source_color' => $feed_config['color'],
        'source_icon' => $feed_config['icon'],
        'source_link' => isset($feed_config['link']) ? $feed_config['link'] : ''
    ];
}

/**
 * Extract image from feed entry
 */
function extract_image($entry, $description, $namespaces) {
    // Check for media:content or media:thumbnail (Mastodon uses this)
    if (isset($namespaces['media'])) {
        $media = $entry->children($namespaces['media']);
        
        // Check media:content
        if (isset($media->content)) {
            foreach ($media->content as $content) {
                $url = (string) $content['url'];
                $type = (string) $content['type'];
                if (!empty($url) && (empty($type) || strpos($type, 'image') !== false)) {
                    return $url;
                }
            }
        }
        
        // Check media:thumbnail
        if (isset($media->thumbnail) && isset($media->thumbnail['url'])) {
            return (string) $media->thumbnail['url'];
        }
    }
    
    // Check for enclosure (common in RSS for media)
    if (isset($entry->enclosure)) {
        foreach ($entry->enclosure as $enclosure) {
            $url = (string) $enclosure['url'];
            $type = (string) $enclosure['type'];
            if (!empty($url) && (empty($type) || strpos($type, 'image') !== false)) {
                return $url;
            }
        }
    }
    
    // Extract first image from description/content HTML
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $description, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Fetch OpenGraph image from a URL (with caching)
 */
function fetch_og_image($url, $cache_dir) {
    if (empty($url)) {
        return null;
    }
    
    // Create a cache file for OG images
    $cache_file = $cache_dir . '/og_' . md5($url) . '.txt';
    
    // Check cache (OG images cache for 7 days)
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 604800) {
        $cached = file_get_contents($cache_file);
        return $cached === 'NONE' ? null : $cached;
    }
    
    // Fetch the page
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'NewsWithWes/3.0 RSS Aggregator',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $og_image = null;
    
    if ($html !== false && $http_code === 200) {
        // Look for og:image meta tag
        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/', $html, $matches)) {
            $og_image = $matches[1];
        }
        // Also try the reverse attribute order
        elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/', $html, $matches)) {
            $og_image = $matches[1];
        }
        // Try twitter:image as fallback
        elseif (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/', $html, $matches)) {
            $og_image = $matches[1];
        }
        elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']twitter:image["\']/', $html, $matches)) {
            $og_image = $matches[1];
        }
    }
    
    // Cache the result (or 'NONE' if no image found)
    file_put_contents($cache_file, $og_image ?: 'NONE');
    
    return $og_image;
}

/**
 * Get pinned post URLs
 */
function get_pinned_urls($pinned_file) {
    if (!file_exists($pinned_file)) {
        return [];
    }
    
    $content = file_get_contents($pinned_file);
    $lines = explode("\n", $content);
    $urls = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '#') !== 0) {
            $urls[] = $line;
        }
    }
    
    return $urls;
}

/**
 * Format relative time (e.g., "2h ago", "Yesterday")
 */
function format_relative_time($timestamp) {
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . 'm ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . 'h ago';
    } elseif ($diff < 172800) {
        return 'Yesterday';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . 'd ago';
    } else {
        return date('M j', $timestamp);
    }
}

/**
 * Truncate text to specified length
 */
function truncate($text, $length = 200) {
    $text = trim($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * HTML escape helper
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

echo "News with Wes - RSS Feed Generator v3.0\n";
echo "========================================\n";
log_message("Started generation");

// Collect all items from all feeds
$all_items = [];
$seen_urls = []; // Track URLs to prevent duplicates across feeds

foreach ($config['feeds'] as $feed_config) {
    echo "\nFetching: {$feed_config['name']} ({$feed_config['url']})\n";
    
    $xml_content = fetch_url($feed_config['url'], $config['cache_dir'], $config['cache_duration']);
    
    if ($xml_content === false) {
        echo "  ERROR: Could not fetch feed\n";
        log_message("ERROR: Failed to fetch feed {$feed_config['name']}");
        continue;
    }
    
    $items = parse_feed($xml_content, $feed_config);
    $found_count = count($items);
    
    // Sort this feed's items by date (newest first) before limiting
    usort($items, function($a, $b) {
        return $b['date'] - $a['date'];
    });

    // Debug: Log first 3 item dates for Substacks
    if ($feed_config['slug'] === 'substack' && count($items) > 0) {
        echo "  DEBUG - First 3 dates from {$feed_config['url']}:\n";
        for ($i = 0; $i < min(3, count($items)); $i++) {
            $debug_date = date('Y-m-d H:i:s', $items[$i]['date']);
            $debug_title = substr($items[$i]['title'], 0, 50);
            echo "    [{$debug_date}] {$debug_title}\n";
        }
    }

    // Limit to max_items_per_feed from this source
    $items = array_slice($items, 0, $config['max_items_per_feed']);
    $limited_count = count($items);
    
    $added_count = 0;
    $duplicate_count = 0;
    
    foreach ($items as $item) {
        // Check for duplicate URLs (across all feeds)
        $url_key = $item['link'];
        if (isset($seen_urls[$url_key])) {
            $duplicate_count++;
            continue;
        }
        $seen_urls[$url_key] = true;
        
        $all_items[] = $item;
        $added_count++;
    }
    
    echo "  Found {$found_count} items, limited to {$limited_count}, added {$added_count}, skipped {$duplicate_count} duplicates\n";
    log_message("Feed {$feed_config['name']}: found={$found_count}, limited={$limited_count}, added={$added_count}, duplicates={$duplicate_count}");
}

// Sort all items by date (newest first)
usort($all_items, function($a, $b) {
    return $b['date'] - $a['date'];
});

// Get pinned URLs
$pinned_urls = get_pinned_urls($config['pinned_file']);

// Mark pinned items and move them to top
$pinned_items = [];
$regular_items = [];

foreach ($all_items as $item) {
    if (in_array($item['link'], $pinned_urls)) {
        $item['is_pinned'] = true;
        $pinned_items[] = $item;
    } else {
        $item['is_pinned'] = false;
        $regular_items[] = $item;
    }
}

// Combine: pinned first, then regular
$sorted_items = array_merge($pinned_items, $regular_items);

// All items are now included (already limited per-feed)
$display_items = $sorted_items;
$total_items = count($display_items);
echo "\nTotal items to display: {$total_items}\n";

// Fetch OpenGraph images for items without images
echo "\nFetching OpenGraph images for items without images...\n";
$og_fetch_count = 0;
foreach ($display_items as &$item) {
    if (empty($item['image']) && !empty($item['link'])) {
        $og_image = fetch_og_image($item['link'], $config['cache_dir']);
        if ($og_image) {
            $item['image'] = $og_image;
            $og_fetch_count++;
        }
    }
}
unset($item); // Break reference
echo "  Fetched {$og_fetch_count} OpenGraph images\n";

// Count items with images
$items_with_images = 0;
foreach ($display_items as $item) {
    if (!empty($item['image'])) {
        $items_with_images++;
    }
}
echo "  Total items with images: {$items_with_images} / " . count($display_items) . "\n";

// ============================================================================
// GENERATE HTML OUTPUT
// ============================================================================

// Generate timestamp in Eastern Time
$eastern = new DateTimeZone('America/New_York');
$now = new DateTime('now', $eastern);
$generated_time = $now->format('F j, Y \a\t g:i A') . ' EST';

// Build HTML
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$config['site_title']}</title>
    <meta name="description" content="Curated news and articles by Wes Fryer from across the web">
    
    <!-- Preload banner -->
    <link rel="preload" href="{$config['banner_image']}" as="image">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Jetsons-inspired palette */
            --jetsons-sky: #87ceeb;
            --jetsons-sky-dark: #5eb5d9;
            --jetsons-blue: #4a90d9;
            --jetsons-purple: #9b6dff;
            --jetsons-orange: #ff7f50;
            --jetsons-coral: #ff6b6b;
            --jetsons-teal: #40c4c4;
            --jetsons-yellow: #ffd93d;
            
            /* UI Colors */
            --cloud-white: #ffffff;
            --soft-gray: #f8f9fa;
            --text-dark: #2d3748;
            --text-muted: #718096;
            
            /* Shadows */
            --shadow-soft: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
            
            /* Fonts */
            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Nunito', sans-serif;
            
            /* Spacing */
            --radius-card: 20px;
            --radius-badge: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-body);
            background: linear-gradient(180deg, var(--jetsons-sky) 0%, var(--jetsons-sky-dark) 100%);
            min-height: 100vh;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 1rem;
        }

        /* Banner */
        .banner {
            border-radius: var(--radius-card);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            margin-bottom: 1.5rem;
        }

        .banner img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Header Info */
        .header-info {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .header-info p {
            font-family: var(--font-display);
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .last-updated {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--cloud-white);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
            box-shadow: var(--shadow-soft);
        }

        /* Filters */
        .filters {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            font-family: var(--font-display);
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--cloud-white);
            color: var(--text-dark);
            font-size: 0.9rem;
            box-shadow: var(--shadow-soft);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #ff1493 0%, #ff69b4 100%);
            color: white;
        }

        /* Flipboard button gets red background */
        .filter-btn.flipboard-btn {
            background: linear-gradient(135deg, #e12828 0%, #ff4444 100%);
            color: white;
        }

        .filter-btn.flipboard-btn:hover {
            background: linear-gradient(135deg, #f53333 0%, #ff5555 100%);
        }

        .filter-btn.flipboard-btn.active {
            background: linear-gradient(135deg, #c91f1f 0%, #e12828 100%);
            box-shadow: 0 4px 15px rgba(225, 40, 40, 0.4);
        }

        /* Substack/Blogs button gets light blue background */
        .filter-btn.substack-btn {
            background: linear-gradient(135deg, #87ceeb 0%, #5eb5d9 100%);
            color: white;
        }

        .filter-btn.substack-btn:hover {
            background: linear-gradient(135deg, #98d5f5 0%, #6fc6e9 100%);
        }

        .filter-btn.substack-btn.active {
            background: linear-gradient(135deg, #76bdd9 0%, #4da5c9 100%);
            box-shadow: 0 4px 15px rgba(135, 206, 235, 0.4);
        }

        /* Mastodon button gets purple/blue background */
        .filter-btn.mastodon-btn {
            background: linear-gradient(135deg, #6364ff 0%, #8b8cff 100%);
            color: white;
        }

        .filter-btn.mastodon-btn:hover {
            background: linear-gradient(135deg, #7475ff 0%, #9c9dff 100%);
        }

        .filter-btn.mastodon-btn.active {
            background: linear-gradient(135deg, #5253ee 0%, #7a7bff 100%);
            box-shadow: 0 4px 15px rgba(99, 100, 255, 0.4);
        }

        /* CookWithWes button gets orange background matching posts */
        .filter-btn.cookwithwes-btn {
            background: linear-gradient(135deg, #e85d04 0%, #ff8c42 100%);
            color: white;
        }

        .filter-btn.cookwithwes-btn:hover {
            background: linear-gradient(135deg, #f96e15 0%, #ffa053 100%);
        }

        .filter-btn.cookwithwes-btn.active {
            background: linear-gradient(135deg, #d74c00 0%, #ee7b31 100%);
            box-shadow: 0 4px 15px rgba(232, 93, 4, 0.4);
        }

        /* Feed Grid */
        .feed-grid {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        /* Feed Cards */
        .feed-card {
            background: var(--cloud-white);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: all 0.3s ease;
            display: grid;
            grid-template-columns: 1fr;
        }

        .feed-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .feed-card.has-image {
            grid-template-columns: 1fr 200px;
        }

        .feed-card.pinned {
            border: 3px solid var(--jetsons-orange);
        }

        .card-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .source-badge {
            font-family: var(--font-display);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.3rem 0.8rem;
            border-radius: var(--radius-badge);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }

        a.source-badge:hover {
            transform: scale(1.05);
        }

        .source-badge.flipboard {
            background: linear-gradient(135deg, #e12828 0%, #ff4444 100%);
            color: white;
        }

        .source-badge.substack {
            background: linear-gradient(135deg, #ff6719 0%, #ff8844 100%);
            color: white;
        }

        .source-badge.mastodon {
            background: linear-gradient(135deg, #6364ff 0%, #8b8cff 100%);
            color: white;
        }

        .source-badge.cookwithwes {
            background: linear-gradient(135deg, #e85d04 0%, #ff8c42 100%);
            color: white;
        }

        .card-date {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .card-title {
            font-family: var(--font-display);
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .card-title a {
            color: var(--text-dark);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .card-title a:hover {
            color: var(--jetsons-blue);
        }

        .card-description {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.5rem;
        }

        .tag {
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            background: var(--soft-gray);
            border-radius: 10px;
            color: var(--text-muted);
        }

        .pinned-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            color: var(--jetsons-orange);
            font-weight: 700;
        }

        /* Card Image */
        .card-image {
            position: relative;
            overflow: hidden;
            min-height: 150px;
        }

        .card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .feed-card:hover .card-image img {
            transform: scale(1.05);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 3rem 1rem;
            margin-top: 2rem;
        }

        .footer p {
            font-family: var(--font-display);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .footer a {
            color: var(--jetsons-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Responsive */
        @media (max-width: 700px) {
            .feed-card.has-image {
                grid-template-columns: 1fr;
            }

            .card-image {
                height: 180px;
                order: -1;
            }

            .filters {
                gap: 0.4rem;
            }

            .filter-btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feed-card {
            animation: fadeInUp 0.4s ease forwards;
        }

        .feed-card:nth-child(1) { animation-delay: 0.05s; }
        .feed-card:nth-child(2) { animation-delay: 0.1s; }
        .feed-card:nth-child(3) { animation-delay: 0.15s; }
        .feed-card:nth-child(4) { animation-delay: 0.2s; }
        .feed-card:nth-child(5) { animation-delay: 0.25s; }

        /* Hidden class for filtering */
        .feed-card.hidden {
            display: none;
        }

        /* No results message */
        .no-results {
            text-align: center;
            padding: 3rem;
            background: var(--cloud-white);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-soft);
        }

        .no-results h3 {
            font-family: var(--font-display);
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .no-results p {
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Banner -->
        <header class="banner">
            <img src="{$config['banner_image']}" alt="News with Wes Fryer - A futuristic cityscape with social media icons featuring Flipboard, Substack, Mastodon, Facebook, and YouTube integrated into a Jetsons-style scene with flying vehicles and holographic displays">
        </header>

        <!-- Header Info -->
        <div class="header-info">
            <p>Curated news and articles by <a href="https://wesfryer.com" style="color: var(--jetsons-blue); text-decoration: none; font-weight: 600;">Wes Fryer</a> from across the web</p>
            <div class="last-updated">
                <span>🔄</span>
                <span>Updated: {$generated_time}</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <button class="filter-btn active" data-source="all">🌐 All Sources</button>
            <button class="filter-btn flipboard-btn" data-source="flipboard">🎴 Flipboard</button>
            <button class="filter-btn substack-btn" data-source="substack">✍️ Wes' Blogs</button>
            <button class="filter-btn mastodon-btn" data-source="mastodon">📡 @FederatedReader</button>
            <button class="filter-btn cookwithwes-btn" data-source="cookwithwes">🔥 @CookWithWes</button>
        </div>

        <!-- Feed Items -->
        <main class="feed-grid">
HTML;

// Generate cards for each item
foreach ($display_items as $item) {
    $has_image_class = $item['image'] ? 'has-image' : '';
    $pinned_class = $item['is_pinned'] ? 'pinned' : '';
    $title_escaped = h($item['title']);
    $link_escaped = h($item['link']);
    $description_truncated = h(truncate($item['description_text'], 180));
    $relative_time = format_relative_time($item['date']);
    $source_slug = h($item['source_slug']);
    $source_name = h($item['source']);
    $source_icon = $item['source_icon'];
    $source_link = h($item['source_link']);
    
    // Make source badge a link if source_link exists
    if (!empty($source_link)) {
        $source_badge = "<a href=\"{$source_link}\" target=\"_blank\" rel=\"noopener\" class=\"source-badge {$source_slug}\">{$source_icon} {$source_name}</a>";
    } else {
        $source_badge = "<span class=\"source-badge {$source_slug}\">{$source_icon} {$source_name}</span>";
    }
    
    $html .= <<<CARD

            <article class="feed-card {$has_image_class} {$pinned_class}" data-source="{$source_slug}">
                <div class="card-content">
                    <div class="card-header">
                        {$source_badge}
                        <span class="card-date">{$relative_time}</span>
                    </div>
                    <h2 class="card-title">
                        <a href="{$link_escaped}" target="_blank" rel="noopener">{$title_escaped}</a>
                    </h2>
                    <p class="card-description">{$description_truncated}</p>
CARD;

    // Add tags if present
    if (!empty($item['tags'])) {
        $html .= "\n                    <div class=\"card-tags\">\n";
        $tag_count = 0;
        foreach ($item['tags'] as $tag) {
            if ($tag_count >= 5) break; // Limit to 5 tags
            $tag_escaped = h($tag);
            $html .= "                        <span class=\"tag\">#{$tag_escaped}</span>\n";
            $tag_count++;
        }
        $html .= "                    </div>\n";
    }

    $html .= "                </div>\n";

    // Add image if present
    if ($item['image']) {
        $image_escaped = h($item['image']);
        $html .= <<<IMAGE
                <div class="card-image">
                    <img src="{$image_escaped}" alt="" loading="lazy">
                </div>
IMAGE;
    }

    $html .= "\n            </article>\n";
}

$html .= <<<HTML
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>Powered by open web feeds • Curated by <a href="https://wesfryer.com">Wes Fryer</a></p>
            <div class="footer-links">
                <a href="https://healourculture.org">🖐️ Heal Our Culture</a>
                <a href="https://resistandheal.com">❤️ Resist and Heal</a>
            </div>
        </footer>
    </div>

    <script>
        // Simple client-side filtering
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active state
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const source = btn.dataset.source;
                
                // Filter cards
                document.querySelectorAll('.feed-card').forEach(card => {
                    if (source === 'all' || card.dataset.source === source) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>
HTML;

// Write the HTML file
$bytes_written = file_put_contents($config['output_file'], $html);

if ($bytes_written !== false) {
    echo "\nSuccessfully generated: {$config['output_file']}\n";
    echo "File size: " . number_format($bytes_written) . " bytes\n";
    log_message("Successfully generated index.html ({$bytes_written} bytes)");
} else {
    echo "ERROR: Could not write output file!\n";
    log_message("ERROR: Failed to write output file");
    exit(1);
}

echo "\nCompleted: " . date('Y-m-d H:i:s') . "\n";
log_message("Generation completed");
