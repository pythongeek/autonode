<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left"><h1 class="amp-page-title"><?php esc_html_e( 'API Documentation', 'autonode' ); ?></h1></div>
    <code class="amp-api-base-display"><?php echo esc_html( $api_base ); ?></code>
</div>
<div class="amp-docs-layout">
<nav class="amp-docs-sidebar">
    <a href="#quickstart"><?php esc_html_e( 'Quick Start', 'autonode' ); ?></a>
    <a href="#auth"><?php esc_html_e( 'Authentication', 'autonode' ); ?></a>
    <a href="#posts"><?php esc_html_e( 'Posts', 'autonode' ); ?></a>
    <a href="#pages"><?php esc_html_e( 'Pages', 'autonode' ); ?></a>
    <a href="#seo"><?php esc_html_e( 'SEO Fields', 'autonode' ); ?></a>
    <a href="#meta"><?php esc_html_e( 'Post Meta', 'autonode' ); ?></a>
    <a href="#media"><?php esc_html_e( 'Media', 'autonode' ); ?></a>
    <a href="#taxonomy"><?php esc_html_e( 'Taxonomy', 'autonode' ); ?></a>
    <a href="#bulk"><?php esc_html_e( 'Bulk', 'autonode' ); ?></a>
    <a href="#webhooks"><?php esc_html_e( 'Webhooks', 'autonode' ); ?></a>
    <a href="#analytics"><?php esc_html_e( 'Analytics', 'autonode' ); ?></a>
    <a href="#keys"><?php esc_html_e( 'Key Mgmt', 'autonode' ); ?></a>
    <a href="#system"><?php esc_html_e( 'System', 'autonode' ); ?></a>
    <a href="#errors"><?php esc_html_e( 'Errors', 'autonode' ); ?></a>
    <a href="#n8n"><?php esc_html_e( 'n8n Examples', 'autonode' ); ?></a>
    <hr style="border-color:var(--amp-border);margin:8px 0">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-compat' ) ); ?>"> <?php esc_html_e( 'Compat', 'autonode' ); ?></a>
</nav>
<div class="amp-docs-content">

<section id="quickstart"><h2><?php esc_html_e( 'Quick Start & Prerequisites', 'autonode' ); ?></h2>
<p><?php esc_html_e( 'To use AutoNode WP at its full potential, ensure your environment meets the following requirements:', 'autonode' ); ?></p>
<div class="amp-grid-2" style="margin-top:20px;">
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong><?php esc_html_e( 'REST API Activation', 'autonode' ); ?></strong><p><?php esc_html_e( 'WordPress REST API is active by default. Ensure no security plugins (like Wordfence or WPS Hide Login) are blocking /wp-json/.', 'autonode' ); ?></p></div>
    </div>
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong><?php esc_html_e( 'Permalinks', 'autonode' ); ?></strong><p><?php printf( esc_html__( 'Must be set to %s in Settings > Permalinks for the API routes to resolve correctly.', 'autonode' ), '<strong>' . esc_html__( 'Post name', 'autonode' ) . '</strong>' ); ?></p></div>
    </div>
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong><?php esc_html_e( 'SEO Plugin', 'autonode' ); ?></strong><p><?php printf( esc_html__( 'Install %s (recommended) or Yoast SEO to enable advanced field access.', 'autonode' ), '<strong>' . esc_html__( 'Rank Math SEO', 'autonode' ) . '</strong>' ); ?></p></div>
    </div>
    <div class="amp-step"><span class="amp-step-num">✓</span>
        <div><strong><?php esc_html_e( 'PHP 8.1+', 'autonode' ); ?></strong><p><?php esc_html_e( 'AutoNode requires PHP 8.1 or higher for optimal performance and security.', 'autonode' ); ?></p></div>
    </div>
</div>
<h3><?php esc_html_e( 'Why use AutoNode Keys instead of Application Passwords?', 'autonode' ); ?></h3>
<p><?php printf( esc_html__( 'Standard WordPress Application Passwords grant full access to your user account. %s are:', 'autonode' ), '<strong>' . esc_html__( 'AutoNode API Keys', 'autonode' ) . '</strong>' ); ?></p>
<ul style="color:var(--amp-dim); margin-left:20px;">
    <li><strong><?php esc_html_e( 'Scoped', 'autonode' ); ?></strong>: <?php esc_html_e( 'Limit access to specific actions (e.g., only "posts:write").', 'autonode' ); ?></li>
    <li><strong><?php esc_html_e( 'Secure', 'autonode' ); ?></strong>: <?php esc_html_e( 'Support IP whitelisting and auto-rotation.', 'autonode' ); ?></li>
    <li><strong><?php esc_html_e( 'Monitored', 'autonode' ); ?></strong>: <?php esc_html_e( 'Track usage and performance specifically for your n8n workflows.', 'autonode' ); ?></li>
</ul>
</section>

<section id="auth"><h2><?php esc_html_e( 'Authentication', 'autonode' ); ?></h2>
<p><?php esc_html_e( 'Send your API key as a Bearer token or in the X-API-Key header (preferred for n8n):', 'autonode' ); ?></p>
<pre class="amp-code-block">Authorization: Bearer ampcm_&lt;64-hex-chars&gt;
# OR
X-API-Key: ampcm_&lt;64-hex-chars&gt;</pre>
<p><?php esc_html_e( 'Every authenticated response includes rate-limit headers:', 'autonode' ); ?></p>
<pre class="amp-code-block">X-RateLimit-Limit: 120
X-RateLimit-Remaining: 118
X-RateLimit-Reset: 1742473260   (Unix timestamp)</pre>
<p><?php esc_html_e( 'Response envelope:', 'autonode' ); ?></p>
<pre class="amp-code-block">{ "success": true, "data": {  }, "request_id": "uuid" }</pre>
<h3><?php esc_html_e( 'Brute-Force Protection', 'autonode' ); ?></h3>
<p><?php esc_html_e( 'Failed auth attempts are tracked per IP. After the configured limit (default 10) within a 5-minute window, the IP is blocked with escalating durations: 5min -> 30min -> 2h -> 24h. Successful authentication automatically clears the block. Blocked IPs are visible in Settings.', 'autonode' ); ?></p>
</section>

<section id="posts"><h2><?php esc_html_e( 'Posts', 'autonode' ); ?></h2>
<?php
$autonode_eps = array(
    array( 'GET',    '/posts',              'posts:read',   __( 'List posts. Params: per_page, page, status, search, orderby, order, category', 'autonode' ) ),
    array( 'POST',   '/posts',              'posts:write',  __( 'Create post with optional inline SEO', 'autonode' ) ),
    array( 'GET',    '/posts/{id}',         'posts:read',   __( 'Get post + full SEO fields + all meta', 'autonode' ) ),
    array( 'PUT',    '/posts/{id}',         'posts:write',  __( 'Update post fields + optional inline SEO', 'autonode' ) ),
    array( 'DELETE', '/posts/{id}',         'posts:delete', __( 'Trash post. Add ?force=true to permanently delete', 'autonode' ) ),
    array( 'POST',   '/posts/{id}/publish', 'posts:publish', __( 'Publish a draft post immediately', 'autonode' ) ),
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

<section id="pages"><h2><?php esc_html_e( 'Pages', 'autonode' ); ?></h2>
<p><?php printf( esc_html__( 'Identical payload to Posts. Replace %s with %s. Use scopes %s, %s, %s.', 'autonode' ), '<code>/posts</code>', '<code>/pages</code>', '<code>pages:read</code>', '<code>pages:write</code>', '<code>pages:delete</code>' ); ?></p>
<pre class="amp-code-block">POST /pages
{ "title": "Landing Page", "content": "", "status": "publish", "parent_id": 0 }</pre>
</section>

<section id="seo"><h2><?php esc_html_e( 'SEO Fields', 'autonode' ); ?></h2>
<p><?php esc_html_e( 'Read or update all SEO fields independently. Works for both posts and pages.', 'autonode' ); ?></p>
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

<section id="meta"><h2><?php esc_html_e( 'Post Meta', 'autonode' ); ?></h2>
<p><?php printf( esc_html__( 'Dedicated endpoint for custom fields. SEO meta keys (rank_math_*, _yoast_wpseo_*) are blocked here - use the %s endpoint for those.', 'autonode' ), '<code>/seo</code>' ); ?></p>
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

<section id="media"><h2><?php esc_html_e( 'Media', 'autonode' ); ?></h2>
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

// Sideload from URL (n8n-friendly - avoids base64 encoding):
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

<section id="taxonomy"><h2><?php esc_html_e( 'Taxonomy', 'autonode' ); ?></h2>
<pre class="amp-code-block">GET  /categories   // all categories with id, name, slug, count, parent
POST /categories   { "name": "AI Tools", "slug": "ai-tools" }
GET  /tags         // all tags
POST /tags         { "name": "automation" }

// Auto-create in post body (recommended - no pre-check needed):
"category_names": ["AI Marketing", "Tutorials"]
"tag_names":      ["n8n", "automation", "AI"]</pre>
</section>

<section id="bulk"><h2><?php esc_html_e( 'Bulk Operations', 'autonode' ); ?></h2>
<p><?php printf( esc_html__( 'Process up to 50 items per request. Scope: %s. Operations are atomic per-item - partial success is returned.', 'autonode' ), '<code>bulk:write</code>' ); ?></p>
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

<section id="webhooks"><h2><?php esc_html_e( 'Webhooks', 'autonode' ); ?></h2>
<p><?php printf( esc_html__( 'Webhooks fire asynchronously via WP-Cron, with automatic retry on failure (configurable up to 3 attempts: 60s -> 300s -> 900s delays). Each delivery is logged - see %s.', 'autonode' ), '<code>GET /webhooks/{id}/deliveries</code>' ); ?></p>
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

// Verify in n8n - HTTP Request - Header:
// X-autonode-Signature: sha256=HMAC-SHA256(secret, raw_request_body)</pre>

<h3><?php esc_html_e( 'Webhook Payload', 'autonode' ); ?></h3>
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

<section id="analytics"><h2><?php esc_html_e( 'Analytics', 'autonode' ); ?></h2>
<pre class="amp-code-block">GET /analytics/summary              // today's requests, week total, 24h errors, avg ms
GET /analytics/hourly?hours=24      // per-hour buckets (max 168 hours)
GET /analytics/endpoints            // top 10 endpoints by hits (7d)
GET /analytics/keys                 // per-key breakdown: requests, avg ms, errors (7d)</pre>
</section>

<section id="keys"><h2><?php esc_html_e( 'Key Management', 'autonode' ); ?></h2>
<pre class="amp-code-block">GET  /keys                          // list your keys (scopes:read)
POST /keys/{id}/revoke              // permanently revoke (irreversible)
POST /keys/{id}/rotate              // generate new secret, keep same id/scopes

// rotate returns the new raw key - store it immediately:
{
  "raw_key": "ampcm_&lt;new-64-hex&gt;",
  "id": 5,
  "prefix": "ampcm_a3f8",
  "label": "n8n Production"
}</pre>
</section>

<section id="system"><h2><?php esc_html_e( 'System', 'autonode' ); ?></h2>
<pre class="amp-code-block">GET /ping               // public health check - no auth required
GET /status             // key info, scopes, SEO plugin, cron status
GET /cron-health        // detailed cron health: last ping, all hooks, stale warning
GET /blocked-ips        // current brute-force blocks (system:read)</pre>
</section>

<section id="errors"><h2><?php esc_html_e( 'Error Reference', 'autonode' ); ?></h2>
<table class="amp-table"><thead><tr>
    <th><?php esc_html_e( 'HTTP', 'autonode' ); ?></th>
    <th><?php esc_html_e( 'Code', 'autonode' ); ?></th>
    <th><?php esc_html_e( 'Cause', 'autonode' ); ?></th>
</tr></thead><tbody>
<tr><td>401</td><td><code>amp_no_auth</code></td><td><?php esc_html_e( 'Missing Authorization / X-API-Key header', 'autonode' ); ?></td></tr>
<tr><td>401</td><td><code>amp_bad_token</code></td><td><?php esc_html_e( 'Token doesn\'t start with ampcm_', 'autonode' ); ?></td></tr>
<tr><td>401</td><td><code>amp_invalid_key</code></td><td><?php esc_html_e( 'Key not found, revoked, or expired', 'autonode' ); ?></td></tr>
<tr><td>403</td><td><code>amp_ip_blocked</code></td><td><?php esc_html_e( 'Client IP not in whitelist (key-level) or brute-force blocked', 'autonode' ); ?></td></tr>
<tr><td>403</td><td><code>amp_forbidden</code></td><td><?php esc_html_e( 'Key lacks required scope', 'autonode' ); ?></td></tr>
<tr><td>403</td><td><code>amp_ssl_required</code></td><td><?php esc_html_e( 'HTTPS required but HTTP used', 'autonode' ); ?></td></tr>
<tr><td>404</td><td><code>amp_not_found</code></td><td><?php esc_html_e( 'Resource not found', 'autonode' ); ?></td></tr>
<tr><td>429</td><td><code>amp_rate_limited</code></td><td><?php esc_html_e( 'Rate limit exceeded - check X-RateLimit-Reset header', 'autonode' ); ?></td></tr>
<tr><td>429</td><td><code>amp_ip_blocked</code></td><td><?php esc_html_e( 'Brute-force block - check error message for retry-after seconds', 'autonode' ); ?></td></tr>
<tr><td>400</td><td><code>amp_invalid</code></td><td><?php esc_html_e( 'Missing or invalid field in request body', 'autonode' ); ?></td></tr>
</tbody></table>
</section>

<section id="n8n"><h2><?php esc_html_e( 'n8n Workflow Examples', 'autonode' ); ?></h2>
<h3><?php esc_html_e( '1. Header Auth Credential', 'autonode' ); ?></h3>
<pre class="amp-code-block">Type: Header Auth
Name: Authorization
Value: Bearer ampcm_YOUR_KEY_HERE</pre>

<h3><?php esc_html_e( '2. Create Post with Full SEO (n8n expression body)', 'autonode' ); ?></h3>
<pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/posts
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

<h3><?php esc_html_e( '3. Upload Image from URL (replaces base64 node)', 'autonode' ); ?></h3>
<pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/media/sideload
{
  "url":   "{{ $json.image_url }}",
  "title": "{{ $json.image_title }}",
  "alt":   "{{ $json.image_alt }}"
}
// Returns: { "media": { "id": 2231, "source_url": "" } }
// Then pass media.id as featured_media in the /posts call</pre>

<h3><?php esc_html_e( '4. Bulk Publish After QA Gate', 'autonode' ); ?></h3>
<pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/bulk
{
  "operation": "publish",
  "items": {{ $items.map(p => ({ id: p.wp_post_id })) }}
}</pre>

<h3><?php esc_html_e( '5. Verify Webhook Signature in n8n', 'autonode' ); ?></h3>
<pre class="amp-code-block">// In n8n Code node after Webhook trigger:
const crypto = require('crypto');
const body   = $input.first().json.rawBody;  // raw string
const secret = 'your-webhook-secret';
const expected = 'sha256=' + crypto.createHmac('sha256', secret).update(body).digest('hex');
const received  = $input.first().headers['x-autonode-signature'];
if (expected !== received) throw new Error('Signature mismatch - rejected');
return $input.all();</pre>

<h3><?php esc_html_e( '6. Rotate Key (automated key rotation workflow)', 'autonode' ); ?></h3>
<pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/keys/{{ $json.key_id }}/rotate
// Headers: Authorization: Bearer ampcm_&lt;current-key-with-keys:rotate-scope&gt;
// Immediately update n8n credential with the returned raw_key</pre>
</section>

</div></div></div>

