# Changelog — AutoNode Pro

All notable changes to this plugin are documented in this file.

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [4.2.0] — 2026-05-11

### Added
- Admin UI overhaul: hero banners, stat cards, numbered form sections, 2-column layouts
- Dark mode support (Agentic Dark theme)
- Logo branding on all admin pages
- API key usage analytics on dashboard
- n8n Starter Workflows template library
- Theme compatibility checker (Agentic Pro detection)
- IP-based brute force protection with block/unblock controls
- Sliding-window rate limiting algorithm
- Webhook event filtering by post type and action

### Changed
- All view file variables prefixed with `autonode_` (PHPCS compliance)
- All output wrapped in esc_html()/esc_attr() (XSS prevention)
- Translator comments added above all printf() calls
- debug.php now uses WP error_log instead of raw fopen/fwrite
- Menu.php passes prefixed variables to all view files

### Fixed
- PHPCS NonPrefixedVariableFound across all view files
- PHPCS OutputNotEscaped across all view files
- PHPCS MissingTranslatorsComment in webhooks.php
- debug.php fatal error when AUTONODE_DEBUG enabled without WP_CONTENT_DIR (now uses error_log())
- Logo file renamed from "autonode pro.png" to "autonode-logo.png" (Envato compliance)

### Security
- HMAC-SHA256 webhook signature verification
- Per-key IP whitelist (CIDR notation)
- Key expiration enforcement
- Brute force detection with automatic IP blocking

## [4.1.0] — 2024

### Added
- Webhook retry system (3 attempts: 60s, 300s, 900s delays)
- Exponential backoff for failed webhook deliveries
- Webhook event reference sidebar with method/endpoint info
- Inline Rank Math SEO field support (focus keyword, title, description, FAQ)
- Inline Yoast SEO field support
- Media sideloading with automatic alt text from filename

### Changed
- Webhook payloads flattened for easier n8n parsing
- REST API routes organized by resource type
- API key rotation endpoint added

## [4.0.0] — 2024

### Added
- Scoped API key authentication
- Per-key permission system (read, write, delete)
- IP whitelisting for API keys
- API key expiration and rotation
- Activity logging with filtering and export (CSV/JSON)
- Rate limiting (sliding window)
- Brute force protection

### Changed
- Plugin rebranded to AutoNode Pro
- REST API namespace: autonode/v1
- All database tables prefixed with autonode_
- AutoNode replaces Application Passwords entirely

## [3.0.0] — 2023

### Added
- WordPress REST API integration
- Basic authentication via Application Passwords
- Post/page/category/tag CRUD endpoints
- Bulk operations endpoint (oneshot publish)

### Changed
- Initial release as "AutoNode WP"