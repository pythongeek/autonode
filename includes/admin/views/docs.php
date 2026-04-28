<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left"><h1 class="amp-page-title">API Documentation</h1></div>
    <code class="amp-api-base-display"><?php echo esc_html($api_base); ?></code>
</div>
<div class="amp-docs-layout">
<nav class="amp-docs-sidebar">
    <a href="#quickstart">Quick Start</a>
    <a href="#auth">Authentication</a>
    <a href="#posts">Posts</a>
    <a href="#pages">Pages</a>
    <a href="#seo">SEO Fields</a>
    <a href="#meta">Post Meta</a>
    <a href="#media">Media</a>
    <a href="#taxonomy">Taxonomy</a>
    <a href="#bulk">Bulk</a>
    <a href="#webhooks">Webhooks</a>
    <a href="#analytics">Analytics</a>
    <a href="#keys">Key Mgmt</a>
    <a href="#system">System</a>
    <a href="#errors">Errors</a>
    <a href="#n8n">n8n Examples</a>
    <hr style="border-color:var(--amp-border);margin:8px 0">
    <a href="<?php echo esc_url(admin_url('admin.php?page=autonode-compat')); ?>"> Compat</a>
</nav>
<div class="amp-docs-content">

<section id="quickstart"><h2>Quick Start & Prerequisites</h2>
<p>To use AutoNode WP at its full potential, ensure your environment meets the following requirements:</p>
<div class="amp-grid-2" style="margin-top:20px;">
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong>REST API Activation</strong><p>WordPress REST API is active by default. Ensure no security plugins (like Wordfence or WPS Hide Login) are blocking <code>/wp-json/</code>.</p></div>
    </div>
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong>Permalinks</strong><p>Must be set to <strong>Post name</strong> in Settings > Permalinks for the API routes to resolve correctly.</p></div>
    </div>
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong>SEO Plugin</strong><p>Install <strong>Rank Math SEO</strong> (recommended) or Yoast SEO to enable advanced field access.</p></div>
    </div>
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong>PHP 8.1+</strong><p>AutoNode requires PHP 8.1 or higher for optimal performance and security.</p></div>
    </div>
</div>
<h3>Why use AutoNode Keys instead of Application Passwords?</h3>
<p>Standard WordPress Application Passwords grant full access to your user account. <strong>AutoNode API Keys</strong> are:</p>
<ul style="color:var(--amp-dim); margin-left:20px;">
    <li><strong>Scoped</strong>: Limit access to specific actions (e.g., only "posts:write").</li>
    <li><strong>Secure</strong>: Support IP whitelisting and auto-rotation.</li>
    <li><strong>Monitored</strong>: Track usage and performance specifically for your n8n workflows.</li>
</ul>
</section>

<section id="auth"><h2>Authentication</h2>
<p>Send your API key as a Bearer token or in the <code>X-API-Key</code> header (preferred for n8n):</p>
<pre class="amp-code-block">Authorization: Bearer ampcm_&lt;64-hex-chars&gt;
# OR
X-API-Key: ampcm_&lt;64-hex-chars&gt;</pre>
<p>Every authenticated response includes rate-limit headers:</p>
<pre class="amp-code-block">X-RateLimit-Limit: 120
X-RateLimit-Remaining: 118
X-RateLimit-Reset: 1742473260   (Unix timestamp)</pre>
<p>Response envelope:</p>
<pre class="amp-code-block">{ "success": true, "data": {  }, "request_id": "uuid" }</pre>
<h3>Brute-Force Protection</h3>
<p>Failed auth attempts are tracked per IP. After the configured limit (default 10) within a 5-minute window, the IP is blocked with escalating durations: 5min  30min  2h  24h. Successful authentication automatically clears the block. Blocked IPs are visible in Settings.</p>
</section>

<section id="posts"><h2>Posts</h2>
<?php
$autonode_eps = array(
    array( 'GET',    '/posts',              'posts:read',   'List posts. Params: per_page, page, status, search, orderby, order, category' ),
    array( 'POST',   '/posts',              'posts:write',  'Create post with optional inline SEO' ),
    array( 'GET',    '/posts/{id}',         'posts:read',   'Get post + full SEO fields + all meta' ),
    array( 'PUT',    '/posts/{id}',         'posts:write',  'Update post fields + optional inline SEO' ),
    array( 'DELETE', '/posts/{id}',         'posts:delete', 'Trash post. Add ?force=true to permanently delete' ),
    array( 'POST',   '/posts/{id}/publish', 'posts:publish', 'Publish a draft post immediately' ),
);
foreach ( $autonode_eps as list( $autonode_m, $autonode_ep, $autonode_sc, $autonode_d ) ) : ?>
<div class="amp-endpoint"><div class="amp-endpoint-header"><span class="amp-method amp-method-<?php echo esc_attr( strtolower( $autonode_m ) ); ?>"><?php echo esc_html( $autonode_m ); ?></span><code class="amp-endpoint-path"><?php echo esc_html( $autonode_ep ); ?></code><span class="amp-scope-tag"><?php echo esc_html( $autonode_sc ); ?></span></div><p><?php echo esc_html( $autonode_d ); ?></p></div>
<?php endforeach; ?>
<pre class="amp-code-block">POST /posts
{
  "title":          "Article Title",
  "content":        "&lt;p&gt;HTML content&lt;/p&gt;",
  "status":         "draft",           // draft|publish|pending|private|future
  "excerpt":        "Short summary",
  "slug":           "custom-url-slug",
  "featured_media": 2231,              // attachment ID
  "category_names": ["AI Marketing","SEO"],   // auto-created if missing
  "tag_names":      ["n8n","automation"],      // auto-created if missing
  "date":           "2026-03-20T10:00:00Z",
  "meta":           { "custom_field": "value" },
  "seo": {
    "focus_keyword":   "AI marketing automation",
    "title":           "SEO Title 2026",
    "description":     "Meta description 145155 chars",
    "canonical_url":   "https://example.com/ai-marketing/",
    "og_title":        "OG Title", "og_description": "OG Desc",
    "og_image_id":     2231,
    "robots":          "index,follow",
    "pillar_content":  true
  }
}</pre>
</section>

<section id="pages"><h2>Pages</h2>
<p>Identical payload to Posts. Replace <code>/posts</code> with <code>/pages</code>. Use scopes <code>pages:read</code>, <code>pages:write</code>, <code>pages:delete</code>.</p>
<pre class="amp-code-block">POST /pages
{ "title": "Landing Page", "content": "", "status": "publish", "parent_id": 0 }</pre>
</section>

<section id="seo"><h2>SEO Fields</h2>
<p>Read or update all SEO fields independently. Works for both posts and pages.</p>
<pre class="amp-code-block">GET  /posts/{id}/seo      returns all Rank Math / Yoast fields
PUT  /posts/{id}/seo      update any subset of fields
GET  /pages/{id}/seo
PUT  /pages/{id}/seo

// Full field list (Rank Math Pro):
{
  "focus_keyword", "title", "description", "robots", "canonical_url",
  "og_title", "og_description", "og_image", "og_image_id", "og_object_type",
  "twitter_title", "twitter_description", "twitter_image", "twitter_card_type",
  "primary_category", "schema_type", "breadcrumb_title",
  "pillar_content", "advanced_robots", "faq_schema"
}</pre>
</section>

<section id="meta"><h2>Post Meta</h2>
<p>Dedicated endpoint for custom fields. SEO meta keys (rank_math_*, _yoast_wpseo_*) are blocked here  use the <code>/seo</code> endpoint for those.</p>
<pre class="amp-code-block">GET    /posts/{id}/meta              // all custom fields (filtered)
PUT    /posts/{id}/meta              // upsert one or more fields
DELETE /posts/{id}/meta/{key}        // delete a single field

GET    /pages/{id}/meta
PUT    /pages/{id}/meta
DELETE /pages/{id}/meta/{key}

// Example PUT:
PUT /posts/123/meta
{ "custom_css_class": "hero-blue", "reading_time_minutes": 5 }

// Response:
{ "post_id": 123, "updated": 2, "skipped": 0, "errors": [] }</pre>
</section>

<section id="media"><h2>Media</h2>
<pre class="amp-code-block">GET    /media                // list attachments
GET    /media/{id}           // single attachment
PUT    /media/{id}           // update title/alt/caption/description
DELETE /media/{id}           // permanently delete attachment

// Upload via base64:
POST /media
{
  "filename":    "featured.jpg",
  "file_base64": "&lt;base64 bytes&gt;",
  "mime_type":   "image/jpeg",
  "title":       "Image Title",
  "alt":         "Alt text for SEO",
  "caption":     "Image caption",
  "post_id":     123              // optional: attach to post
}

// Sideload from URL (n8n-friendly  avoids base64 encoding):
POST /media/sideload
{
  "url":      "https://cdn.example.com/image.jpg",
  "title":    "Featured Image",
  "alt":      "AI robot working at desk",
  "filename": "featured-image.jpg",   // optional override
  "post_id":  123
}

// Both return the same media object:
{
  "id": 2231, "title": "", "alt": "", "caption": "",
  "source_url": "https:///image.jpg", "full": "", "thumbnail": "",
  "width": 1920, "height": 1080, "filesize": 84521
}</pre>
</section>

<section id="taxonomy"><h2>Taxonomy</h2>
<pre class="amp-code-block">GET  /categories   // all categories with id, name, slug, count, parent
POST /categories   { "name": "AI Tools", "slug": "ai-tools" }
GET  /tags         // all tags
POST /tags         { "name": "automation" }

// Auto-create in post body (recommended  no pre-check needed):
"category_names": ["AI Marketing", "Tutorials"]
"tag_names":      ["n8n", "automation", "AI"]</pre>
</section>

<section id="bulk"><h2>Bulk Operations</h2>
<p>Process up to 50 items per request. Scope: <code>bulk:write</code>. Operations are atomic per-item  partial success is returned.</p>
<pre class="amp-code-block">POST /bulk
{
  "operation": "publish",   // publish|draft|trash|delete|update_seo|update_meta
  "items": [
    { "id": 101 },
    { "id": 102 },
    { "id": 103, "seo": { "focus_keyword": "new kw" } }
  ]
}

// Response:
{
  "operation": "publish", "total": 3, "success": 2, "errors": 1,
  "results": [
    { "id": 101, "status": "success", "new_status": "publish" },
    { "id": 102, "status": "success" },
    { "id": 103, "status": "error", "message": "Post not found." }
  ]
}</pre>
</section>

<section id="webhooks"><h2>Webhooks</h2>
<p>Webhooks fire asynchronously via WP-Cron, with automatic retry on failure (configurable up to 3 attempts: 60s  300s  900s delays). Each delivery is logged  see <code>GET /webhooks/{id}/deliveries</code>.</p>
<pre class="amp-code-block">GET    /webhooks
POST   /webhooks
GET    /webhooks/{id}
PUT    /webhooks/{id}
DELETE /webhooks/{id}
POST   /webhooks/{id}/test           // fire a test event immediately
GET    /webhooks/{id}/deliveries     // last 50 delivery attempts with status + body

// Create:
POST /webhooks
{
  "label":      "n8n Post Published",
  "target_url": "https://n8n.autonode.wikiofautomation.com/webhook/",
  "secret":     "my-signing-secret",   // optional HMAC signing
  "events":     ["post.published","page.published"],
  "post_types": ["post","page"]
}

// Available events:
// post.created  post.updated  post.published  post.deleted
// page.created  page.updated  page.published  page.deleted
// media.uploaded  test

// Verify in n8n  HTTP Request  Header:
// X-autonode-Signature: sha256=HMAC-SHA256(secret, raw_request_body)</pre>

<h3>Webhook Payload</h3>
<pre class="amp-code-block">{
  "event":       "post.published",
  "object_id":   123,
  "object_type": "post",
  "timestamp":   "2026-03-21T10:30:00+00:00",
  "site_url":    "https://autonode.wikiofautomation.com",
  "post": { "id":123, "title":"", "status":"publish", "link":"https://" },
  "seo": { "plugin":"rankmath", "focus_keyword":"", "title":"" }
}</pre>
</section>

<section id="analytics"><h2>Analytics</h2>
<pre class="amp-code-block">GET /analytics/summary              // today's requests, week total, 24h errors, avg ms
GET /analytics/hourly?hours=24      // per-hour buckets (1168 hours)
GET /analytics/endpoints            // top 10 endpoints by hits (7d)
GET /analytics/keys                 // per-key breakdown: requests, avg ms, errors (7d)</pre>
</section>

<section id="keys"><h2>Key Management</h2>
<pre class="amp-code-block">GET  /keys                          // list your keys (scopes:read)
POST /keys/{id}/revoke              // permanently revoke (irreversible)
POST /keys/{id}/rotate              // generate new secret, keep same id/scopes

// rotate returns the new raw key  store it immediately:
{
  "raw_key": "ampcm_&lt;new-64-hex&gt;",
  "id": 5,
  "prefix": "ampcm_a3f8",
  "label": "n8n Production"
}</pre>
</section>

<section id="system"><h2>System</h2>
<pre class="amp-code-block">GET /ping               // public health check  no auth required
GET /status             // key info, scopes, SEO plugin, cron status
GET /cron-health        // detailed cron health: last ping, all hooks, stale warning
GET /blocked-ips        // current brute-force blocks (system:read)</pre>
</section>

<section id="errors"><h2>Error Reference</h2>
<table class="amp-table"><thead><tr><th>HTTP</th><th>Code</th><th>Cause</th></tr></thead><tbody>
<tr><td>401</td><td><code>amp_no_auth</code></td><td>Missing Authorization / X-API-Key header</td></tr>
<tr><td>401</td><td><code>amp_bad_token</code></td><td>Token doesn't start with <code>ampcm_</code></td></tr>
<tr><td>401</td><td><code>amp_invalid_key</code></td><td>Key not found, revoked, or expired</td></tr>
<tr><td>403</td><td><code>amp_ip_blocked</code></td><td>Client IP not in whitelist (key-level) or brute-force blocked</td></tr>
<tr><td>403</td><td><code>amp_forbidden</code></td><td>Key lacks required scope</td></tr>
<tr><td>403</td><td><code>amp_ssl_required</code></td><td>HTTPS required but HTTP used</td></tr>
<tr><td>404</td><td><code>amp_not_found</code></td><td>Resource not found</td></tr>
<tr><td>429</td><td><code>amp_rate_limited</code></td><td>Rate limit exceeded  check X-RateLimit-Reset header</td></tr>
<tr><td>429</td><td><code>amp_ip_blocked</code></td><td>Brute-force block  check error message for retry-after seconds</td></tr>
<tr><td>400</td><td><code>amp_invalid</code></td><td>Missing or invalid field in request body</td></tr>
</tbody></table>
</section>

<section id="n8n"><h2>n8n Workflow Examples</h2>
<h3>1. Header Auth Credential</h3>
<pre class="amp-code-block">Type: Header Auth
Name: Authorization
Value: Bearer ampcm_YOUR_KEY_HERE</pre>

<h3>2. Create Post with Full SEO (n8n expression body)</h3>
<pre class="amp-code-block">POST <?php echo esc_html($api_base); ?>/posts
{
  "title":          "{{ $json.seo_title }}",
  "content":        "{{ $json.article_html_with_css }}",
  "status":         "draft",
  "category_names": {{ $json.categories.split(',').map(s => s.trim()) }},
  "tag_names":      {{ $json.tags.split(',').map(s => s.trim()) }},
  "featured_media": {{ $json.wp_media_id }},
  "seo": {
    "focus_keyword":  "{{ $json.seo_focus_keyword }}",
    "title":          "{{ $json.seo_title }}",
    "description":    "{{ $json.seo_meta_description }}",
    "canonical_url":  "{{ $json.canonical_url }}",
    "og_image_id":    {{ $json.wp_media_id }},
    "robots":         "index,follow",
    "pillar_content": false
  }
}</pre>

<h3>3. Upload Image from URL (replaces base64 node)</h3>
<pre class="amp-code-block">POST <?php echo esc_html($api_base); ?>/media/sideload
{
  "url":   "{{ $json.image_url }}",
  "title": "{{ $json.image_title }}",
  "alt":   "{{ $json.image_alt }}"
}
// Returns: { "media": { "id": 2231, "source_url": "" } }
// Then pass media.id as featured_media in the /posts call</pre>

<h3>4. Bulk Publish After QA Gate</h3>
<pre class="amp-code-block">POST <?php echo esc_html($api_base); ?>/bulk
{
  "operation": "publish",
  "items": {{ $items.map(p => ({ id: p.wp_post_id })) }}
}</pre>

<h3>5. Verify Webhook Signature in n8n</h3>
<pre class="amp-code-block">// In n8n Code node after Webhook trigger:
const crypto = require('crypto');
const body   = $input.first().json.rawBody;  // raw string
const secret = 'your-webhook-secret';
const expected = 'sha256=' + crypto.createHmac('sha256', secret).update(body).digest('hex');
const received  = $input.first().headers['x-autonode-signature'];
if (expected !== received) throw new Error('Signature mismatch  rejected');
return $input.all();</pre>

<h3>6. Rotate Key (automated key rotation workflow)</h3>
<pre class="amp-code-block">POST <?php echo esc_html($api_base); ?>/keys/{{ $json.key_id }}/rotate
// Headers: Authorization: Bearer ampcm_&lt;current-key-with-keys:rotate-scope&gt;
// Immediately update n8n credential with the returned raw_key</pre>
</section>

</div></div></div>

