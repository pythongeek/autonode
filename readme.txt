=== AutoNode ===
Contributors: bdowneertech
Tags: api, rest, n8n, automation, seo
Requires at least: 6.3
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 4.2.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

The ultimate REST API bridge for n8n. Manage posts, Rank Math SEO, media, and webhooks with advanced security and OpenAPI support.

== REST API & Server Requirements ==

To use AutoNode WP at its full potential, ensure the following:
* **Permalinks:** Must be set to "Post name" in WordPress Settings.
* **PHP Version:** 8.1 or higher is required.
* **SEO Plugin:** Rank Math SEO or Yoast SEO should be active for metadata support.
* **REST API:** Ensure your hosting or security plugins are not blocking `/wp-json/`.

== Description ==

AutoNode WP provides a secure, enterprise-grade REST API designed specifically for n8n automation workflows. It bridges WordPress and n8n with features including:

* **Full Post Management** â€” Create, update, publish, and delete posts and pages via API
* **Inline SEO** â€” Read and write Rank Math and Yoast SEO fields in a single API call
* **Media Sideloading** â€” Upload images from URL without base64 encoding
* **Webhooks** â€” Real-time event notifications with HMAC signing, retry logic, and delivery logs
* **API Key Authentication** â€” Scoped keys with IP whitelisting, expiration, and rotation
* **Rate Limiting** â€” Configurable per-key rate limits with sliding window
* **Brute-Force Protection** â€” Automatic IP blocking with escalating durations
* **Analytics Dashboard** â€” Track API usage, response times, and error rates
* **OpenAPI Specification** â€” Auto-generated spec for n8n HTTP Request nodes
* **Bulk Operations** â€” Process up to 50 items per request

== Real-World Use Case: AI Content Publishing Pipeline ==

AutoNode WP is the perfect companion for fully autonomous AI content workflows in n8n. Here is a blueprint of how you can build a complete AI publishing pipeline:

1. **Centralized Configuration Hub:** Set up a Code node at the very beginning of your workflow to store your AutoNode WP API URL and `X-API-Key`. All downstream HTTP Request nodes can reference this globally (e.g., `$('Workflow Config Hub').first().json.wp_api_key`), keeping your workflow clean and easily maintainable.
2. **Content Planning (Google Sheets):** Trigger your n8n workflow from a Google Sheet containing topic ideas, target keywords, and content types (e.g., News, Longform, Case Study).
3. **AI Keyword & Intent Research:** Use n8n AI nodes (like Gemini Flash or OpenAI) to generate LSI keywords and search intent analysis.
4. **Draft Generation & Formatting:** Pass the research to a larger AI model (like Gemini Pro) to write full HTML articles with proper headings and semantic structure.
5. **Automated WP Post Creation:** Use the n8n HTTP Request node to send the generated HTML directly to AutoNode's `/posts` endpoint, creating the draft in WordPress securely via your `X-API-Key`.
6. **Inline SEO Injection:** In the same API request, pass the AI-generated focus keyword, title, and meta description directly into Rank Math or Yoast SEO fields using AutoNode.
7. **Dynamic Taxonomy:** Automatically resolve or create Categories and Tags on the fly via the AutoNode `/categories` and `/tags` endpoints.
8. **Media Sideloading:** Generate an AI featured image and pass the image URL to AutoNode, which securely sideloads it into the WordPress Media Library and sets it as the post's featured image.
9. **Finalizing & Publishing:** Update the post status to `publish` and sync the live URL back to your Google Sheet.

With AutoNode WP, this entire flow happens seamlessly, with zero manual WordPress entry required.

== Installation ==

1. Upload the `autonode-wp` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to AutoNode WP â†’ API Keys to create your first key
4. Configure your n8n workflow with the generated API key

== Frequently Asked Questions ==

= Does this require n8n? =

No. While designed for n8n, AutoNode WP works with any HTTP client or automation tool that supports REST APIs.

= Which SEO plugins are supported? =

Rank Math and Yoast SEO are fully supported. The plugin auto-detects which is active.

= Is HTTPS required? =

HTTPS is recommended but optional. You can enforce it in Settings.

== Changelog ==

= 4.2.0 =
* Added brute-force protection with escalating block durations
* Added webhook delivery log with retry tracking
* Added cron health monitoring
* Added bulk one-shot publish endpoint
* Added OpenAPI specification endpoint
* Fixed HMAC webhook signing (uses raw secret)
* Improved admin dashboard with real-time charts
* Full PHPCS WordPress coding standards compliance

= 4.1.0 =
* Initial release with posts, pages, SEO, media, webhooks, and analytics

== Upgrade Notice ==

= 4.2.0 =
Security improvements: brute-force protection, HMAC fix, and PHPCS compliance.
