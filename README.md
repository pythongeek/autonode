# AutoNode Pro - WordPress REST API Bridge for n8n

Connect WordPress to n8n workflows with scoped API keys, HMAC-signed webhooks, sliding-window rate limiting, and inline Rank Math / Yoast SEO field control.

## Requirements

- WordPress 6.0+
- PHP 7.4+ (PHP 8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- n8n instance (self-hosted or cloud) for workflow automation

## Installation

1. Upload the `autonode-pro` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Navigate to **AutoNode Pro → API Keys** to create your first key
4. Copy the key into your n8n HTTP Request node headers: `X-API-Key: your_key_here`

## Quick Setup

### Create API Key
1. Go to **AutoNode Pro → API Keys**
2. Click **+ New API Key**
3. Set label, permissions, IP whitelist (optional), and expiration
4. Copy the generated key — shown only once

### n8n HTTP Request Setup
```
Method: POST
URL: https://your-site.com/wp-json/autonode/v1/posts
Headers:
  X-API-Key: ampcm_YOUR_64_CHAR_KEY
  Content-Type: application/json
Body (JSON):
{
  "title": "Hello from n8n",
  "content": "<p>Automated post content</p>",
  "status": "draft",
  "seo": {
    "focus_keyword": "n8n automation",
    "title": "Hello | Your Site",
    "description": "Posted automatically via n8n"
  }
}
```

## Features

### API Key Authentication
- Scoped permissions (read, write, delete)
- Per-key IP whitelist (CIDR notation supported)
- Key expiration and rotation enforcement
- Request counters and last-used tracking

### Webhooks
- HMAC-SHA256 signed payloads (verify with secret key)
- 3-attempt exponential backoff: 60s → 300s → 900s
- Flattened JSON payloads for easier n8n parsing
- Per-webhook event filtering

### Rate Limiting
- Sliding-window algorithm (configurable per minute)
- Per-key and global limits
- Brute-force IP blocking after failed attempts

### SEO Integration
- Inline Rank Math SEO fields (focus keyword, title, description, FAQ)
- Inline Yoast SEO fields
- Media sideloading with automatic alt text

### Admin UI
- Dashboard with live charts (24h/7d activity)
- API Keys management with rotation
- Webhook logs and retry management
- System status and compatibility checker

## Permissions

| Endpoint | read | write | delete |
|----------|------|-------|--------|
| GET /posts, /pages, /categories, /tags | ✓ | ✓ | ✗ |
| POST /posts, /pages | ✗ | ✓ | ✗ |
| POST /media/sideload | ✗ | ✓ | ✗ |
| POST /webhooks | ✗ | ✓ | ✗ |
| DELETE /posts/{id} | ✗ | ✗ | ✓ |

## Configuration

Add to `wp-config.php`:

```php
// Enable debug logging (writes to wp-content/debug.log)
// define('AUTONODE_DEBUG', true);

// Custom debug log path:
// define('AUTONODE_DEBUG_LOG', WP_CONTENT_DIR . '/autonode-debug.log');
```

Plugin settings available at **AutoNode Pro → Settings**:
- Rate limits (requests per window)
- Brute-force protection thresholds
- Webhook retry behavior
- UI mode (standard / dark)
- Minimum capability for admin access

## Uninstall

To cleanly remove all plugin data:
1. Deactivate the plugin
2. Go to **Plugins → Installed Plugins**
3. Click **Delete** below AutoNode Pro
4. All plugin tables (`autonode_api_keys`, `autonode_activity_log`, `autonode_webhooks`) and options are removed automatically

## FAQ

**Q: Does this work with WP Engine or other managed hosts?**
A: Yes. Tested on WP Engine, Kinsta, Cloudways, and standard Apache/NGINX shared hosting.

**Q: Can I use Application Passwords instead?**
A: This plugin is designed to replace Application Passwords. It uses scoped API keys which are more secure and offer IP whitelisting and expiration.

**Q: How do webhooks work?**
A: Configure a webhook URL in WordPress (pointing to your n8n workflow). When events fire, WordPress sends a POST to your n8n webhook URL. Payloads are HMAC-signed — verify the `X-AutoNode-Signature` header using your secret key.

**Q: Does it support Rank Math and Yoast simultaneously?**
A: Yes. The plugin detects which SEO plugin is active and formats payloads accordingly. Only one can be active at a time.

**Q: How do I increase rate limits for high-volume workflows?**
A: Go to **AutoNode Pro → Settings** and adjust "Requests per window" under Rate Limiting. You can also set per-key overrides in the API Keys section.

## Changelog

See CHANGELOG.md for version history.