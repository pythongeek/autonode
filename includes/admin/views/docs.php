<?php defined( 'ABSPATH' ) || exit; ?>
<style>
/* ── Docs Page ──────────────────────────────────────────────── */
#autonode-docs .amp-wrap { padding: 0 0 40px; }

.amp-docs-hero {
    background: linear-gradient(135deg, #0f3d1e 0%, #14532d 50%, #0f3d1e 100%);
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}
.amp-docs-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.amp-docs-hero-left { position: relative; z-index: 1; }
.amp-docs-hero h1 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.amp-docs-hero p { margin: 0; font-size: 13px; color: rgba(255,255,255,0.6); }
.amp-docs-base {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px;
    padding: 10px 16px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #a7f3b0;
    word-break: break-all;
}

/* ── Docs Layout ───────────────────────────────────────────── */
.amp-docs-layout {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 24px;
    align-items: start;
}
@media(max-width: 900px) { .amp-docs-layout { grid-template-columns: 1fr; } }

/* ── Sidebar ───────────────────────────────────────────────── */
.amp-docs-sidebar {
    position: sticky;
    top: 40px;
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
}
.amp-docs-sidebar a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    color: var(--amp-dim);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.15s;
    border: 1px solid transparent;
}
.amp-docs-sidebar a:hover {
    background: rgba(20, 83, 45, 0.08);
    color: #14532d;
    padding-left: 16px;
    border-color: rgba(20, 83, 45, 0.15);
}
.amp-docs-sidebar a.active {
    background: rgba(20, 83, 45, 0.12);
    color: #14532d;
    border-color: rgba(20, 83, 45, 0.25);
    font-weight: 700;
}
.amp-docs-sidebar hr { border: none; border-top: 1px solid var(--amp-border); margin: 8px 0; }
.amp-docs-sidebar .amp-sidebar-icon { font-size: 14px; flex-shrink: 0; }

/* ── Content ───────────────────────────────────────────────── */
.amp-docs-content { display: flex; flex-direction: column; gap: 20px; }

.amp-doc-section {
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    overflow: hidden;
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    scroll-margin-top: 40px;
}
.amp-doc-section-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--amp-border);
    display: flex;
    align-items: center;
    gap: 12px;
}
.amp-doc-section-icon { font-size: 20px; }
.amp-doc-section-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: var(--amp-text);
}
.amp-doc-section-body { padding: 20px 24px; }

/* ── Prerequisites Grid ────────────────────────────────────── */
.amp-prereq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 14px;
    margin: 16px 0 24px;
}
.amp-prereq-card {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    background: var(--amp-card2);
    border: 1px solid var(--amp-border);
    border-radius: 8px;
    transition: 0.2s;
}
.amp-prereq-card:hover { border-color: #14532d; transform: translateY(-1px); }
.amp-prereq-icon { font-size: 22px; flex-shrink: 0; }
.amp-prereq-card strong { display: block; font-size: 13px; color: var(--amp-text); margin-bottom: 2px; }
.amp-prereq-card p { margin: 0; font-size: 12px; color: var(--amp-muted); }

/* ── Feature List ──────────────────────────────────────────── */
.amp-feature-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 10px;
}
.amp-feature-list li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    background: var(--amp-card2);
    border: 1px solid var(--amp-border);
    border-radius: 8px;
    font-size: 13px;
    color: var(--amp-dim);
}
.amp-feature-list li .amp-check { color: #16a34a; font-size: 15px; flex-shrink: 0; margin-top: 1px; }
.amp-feature-list li strong { color: var(--amp-text); }

/* ── Endpoint Cards ────────────────────────────────────────── */
.amp-ep-list { display: flex; flex-direction: column; gap: 2px; }
.amp-ep-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 8px;
    transition: 0.15s;
}
.amp-ep-card:hover { background: var(--amp-card2); }
.amp-ep-method {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 800;
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: 0.05em;
    white-space: nowrap;
    flex-shrink: 0;
    min-width: 60px;
    text-align: center;
}
.amp-method-get { background: rgba(74, 222, 128, 0.15); color: #16a34a; border: 1px solid rgba(74, 222, 128, 0.3); }
.amp-method-post { background: rgba(96, 165, 250, 0.15); color: #2563eb; border: 1px solid rgba(96, 165, 250, 0.3); }
.amp-method-put { background: rgba(250, 204, 21, 0.15); color: #ca8a04; border: 1px solid rgba(250, 204, 21, 0.3); }
.amp-method-delete { background: rgba(248, 113, 113, 0.15); color: #dc2626; border: 1px solid rgba(248, 113, 113, 0.3); }
.amp-ep-path {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    color: var(--amp-primary);
    background: rgba(34, 113, 177, 0.08);
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid rgba(34, 113, 177, 0.15);
    flex-shrink: 0;
}
.amp-ep-scope {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-family: 'JetBrains Mono', monospace;
    background: rgba(192, 132, 252, 0.1);
    color: #7c3aed;
    border: 1px solid rgba(192, 132, 252, 0.25);
    flex-shrink: 0;
}
.amp-ep-desc { font-size: 12px; color: var(--amp-muted); flex: 1; margin-top: 4px; }
.amp-ep-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.amp-ep-row + .amp-ep-desc { margin-top: 6px; }

/* ── Code Block ────────────────────────────────────────────── */
.amp-code-block {
    background: #0d1117;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px;
    padding: 16px 20px;
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.7;
    color: #e6edf3;
    overflow-x: auto;
    white-space: pre;
    margin: 12px 0;
}
.amp-code-block .cm-key { color: #79c0ff; }
.amp-code-block .cm-str { color: #a5d6ff; }
.amp-code-block .cm-num { color: #79c0ff; }
.amp-code-block .cm-cmt { color: #8b949e; font-style: italic; }

/* ── Errors Table ──────────────────────────────────────────── */
.amp-err-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
.amp-err-table thead th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    font-weight: 700;
    color: var(--amp-muted);
    background: var(--amp-card2);
    border-bottom: 1px solid var(--amp-border);
}
.amp-err-table tbody td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--amp-border);
    vertical-align: middle;
}
.amp-err-table tbody tr:last-child td { border-bottom: none; }
.amp-err-table tbody tr:hover td { background: rgba(34, 113, 177, 0.02); }
.amp-err-http { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px; }
.amp-err-401 .amp-err-http { color: var(--amp-red); }
.amp-err-403 .amp-err-http { color: var(--amp-orange); }
.amp-err-404 .amp-err-http { color: var(--amp-yellow); }
.amp-err-429 .amp-err-http { color: var(--amp-purple); }
.amp-err-400 .amp-err-http { color: var(--amp-blue); }
.amp-err-code { font-family: 'JetBrains Mono', monospace; font-size: 12px; background: rgba(34, 113, 177, 0.1); color: var(--amp-primary); padding: 2px 7px; border-radius: 4px; border: 1px solid rgba(34, 113, 177, 0.2); }
.amp-err-cause { color: var(--amp-dim); font-size: 12px; }

/* ── Section Intro ─────────────────────────────────────────── */
.amp-doc-intro { font-size: 14px; color: var(--amp-dim); margin-bottom: 16px; }
.amp-doc-intro code { font-size: 12px; }

/* ── n8n Example Blocks ───────────────────────────────────── */
.amp-n8n-example { margin: 20px 0; }
.amp-n8n-example h4 {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--amp-text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.amp-n8n-example h4 .amp-n8n-num {
    width: 22px; height: 22px;
    background: rgba(34, 113, 177, 0.1);
    color: var(--amp-primary);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
}
</style>

<div class="amp-wrap" id="autonode-docs">

<!-- Hero Banner -->
<div class="amp-docs-hero">
    <div class="amp-docs-hero-left">
        <h1>
            <img src="<?php echo esc_url( AUTONODE_URL . 'assets/logo.png' ); ?>" alt="AutoNode" style="width:28px;height:28px;border-radius:6px;vertical-align:middle;">
            <?php esc_html_e( 'API Documentation', 'autonode-pro'); ?>
        </h1>
        <p><?php esc_html_e( 'Complete REST API reference for AutoNode Pro.', 'autonode-pro'); ?></p>
    </div>
    <div style="position:relative;z-index:1;min-width:280px">
        <div style="font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.07em;font-weight:600"><?php esc_html_e( 'API Base URL', 'autonode-pro'); ?></div>
        <div class="amp-docs-base"><?php echo esc_html( $api_base ); ?></div>
    </div>
</div>

<div class="amp-docs-layout">

<!-- Sidebar Nav -->
<nav class="amp-docs-sidebar">
    <a href="#quickstart"><span class="amp-sidebar-icon">🚀</span><?php esc_html_e( 'Quick Start', 'autonode-pro'); ?></a>
    <a href="#auth"><span class="amp-sidebar-icon">🔐</span><?php esc_html_e( 'Authentication', 'autonode-pro'); ?></a>
    <a href="#posts"><span class="amp-sidebar-icon">📝</span><?php esc_html_e( 'Posts', 'autonode-pro'); ?></a>
    <a href="#pages"><span class="amp-sidebar-icon">📄</span><?php esc_html_e( 'Pages', 'autonode-pro'); ?></a>
    <a href="#seo"><span class="amp-sidebar-icon">🔍</span><?php esc_html_e( 'SEO Fields', 'autonode-pro'); ?></a>
    <a href="#meta"><span class="amp-sidebar-icon">🔑</span><?php esc_html_e( 'Post Meta', 'autonode-pro'); ?></a>
    <a href="#media"><span class="amp-sidebar-icon">🖼️</span><?php esc_html_e( 'Media', 'autonode-pro'); ?></a>
    <a href="#taxonomy"><span class="amp-sidebar-icon">🏷️</span><?php esc_html_e( 'Taxonomy', 'autonode-pro'); ?></a>
    <a href="#bulk"><span class="amp-sidebar-icon">⚡</span><?php esc_html_e( 'Bulk', 'autonode-pro'); ?></a>
    <a href="#webhooks"><span class="amp-sidebar-icon">🪝</span><?php esc_html_e( 'Webhooks', 'autonode-pro'); ?></a>
    <a href="#analytics"><span class="amp-sidebar-icon">📊</span><?php esc_html_e( 'Analytics', 'autonode-pro'); ?></a>
    <a href="#keys"><span class="amp-sidebar-icon">🔑</span><?php esc_html_e( 'Key Mgmt', 'autonode-pro'); ?></a>
    <a href="#system"><span class="amp-sidebar-icon">⚙️</span><?php esc_html_e( 'System', 'autonode-pro'); ?></a>
    <a href="#errors"><span class="amp-sidebar-icon">⚠️</span><?php esc_html_e( 'Errors', 'autonode-pro'); ?></a>
    <a href="#n8n"><span class="amp-sidebar-icon">🔧</span><?php esc_html_e( 'n8n Examples', 'autonode-pro'); ?></a>
    <hr>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-compat' ) ); ?>"><span class="amp-sidebar-icon">📋</span><?php esc_html_e( 'Compatibility', 'autonode-pro'); ?></a>
</nav>

<!-- Main Content -->
<div class="amp-docs-content">

<!-- Quick Start -->
<section class="amp-doc-section" id="quickstart">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🚀</span>
        <h2><?php esc_html_e( 'Quick Start & Prerequisites', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Ensure your environment meets these requirements before using the API:', 'autonode-pro'); ?></p>
        <div class="amp-prereq-grid">
            <div class="amp-prereq-card">
                <span class="amp-prereq-icon">🔌</span>
                <div><strong><?php esc_html_e( 'REST API Active', 'autonode-pro'); ?></strong><p><?php esc_html_e( 'No security plugins blocking /wp-json/.', 'autonode-pro'); ?></p></div>
            </div>
            <div class="amp-prereq-card">
                <span class="amp-prereq-icon">🔗</span>
                <div><strong><?php esc_html_e( 'Permalinks: Post Name', 'autonode-pro'); ?></strong><p><?php esc_html_e( 'Required for API routes to resolve.', 'autonode-pro'); ?></p></div>
            </div>
            <div class="amp-prereq-card">
                <span class="amp-prereq-icon">🔍</span>
                <div><strong><?php esc_html_e( 'Rank Math or Yoast SEO', 'autonode-pro'); ?></strong><p><?php esc_html_e( 'Enables advanced SEO field access.', 'autonode-pro'); ?></p></div>
            </div>
            <div class="amp-prereq-card">
                <span class="amp-prereq-icon">⚡</span>
                <div><strong><?php esc_html_e( 'PHP 8.1+', 'autonode-pro'); ?></strong><p><?php esc_html_e( 'Required for optimal performance.', 'autonode-pro'); ?></p></div>
            </div>
        </div>

        <h3 style="font-size:15px;font-weight:700;margin:20px 0 10px"><?php esc_html_e( 'Why AutoNode Keys over Application Passwords?', 'autonode-pro'); ?></h3>
        <ul class="amp-feature-list">
            <li><span class="amp-check">✓</span><div><strong><?php esc_html_e( 'Scoped', 'autonode-pro'); ?></strong> — <?php esc_html_e( 'Limit to specific actions (e.g. only posts:write)', 'autonode-pro'); ?></div></li>
            <li><span class="amp-check">✓</span><div><strong><?php esc_html_e( 'Secure', 'autonode-pro'); ?></strong> — <?php esc_html_e( 'IP whitelisting and automatic key rotation', 'autonode-pro'); ?></div></li>
            <li><span class="amp-check">✓</span><div><strong><?php esc_html_e( 'Monitored', 'autonode-pro'); ?></strong> — <?php esc_html_e( 'Per-key analytics for n8n workflow tracking', 'autonode-pro'); ?></div></li>
        </ul>
    </div>
</section>

<!-- Authentication -->
<section class="amp-doc-section" id="auth">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🔐</span>
        <h2><?php esc_html_e( 'Authentication', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Send your API key as a Bearer token (preferred for n8n):', 'autonode-pro'); ?></p>
        <pre class="amp-code-block">Authorization: Bearer ampcm_&lt;64-hex-chars&gt;</pre>
        <p class="amp-doc-intro" style="margin-top:14px"><?php esc_html_e( 'Or as X-API-Key header:', 'autonode-pro'); ?></p>
        <pre class="amp-code-block">X-API-Key: ampcm_&lt;64-hex-chars&gt;</pre>

        <h3 style="font-size:14px;font-weight:700;margin:20px 0 8px"><?php esc_html_e( 'Rate Limit Headers', 'autonode-pro'); ?></h3>
        <pre class="amp-code-block">X-RateLimit-Limit: 120
X-RateLimit-Remaining: 118
X-RateLimit-Reset: 1742473260   ← Unix timestamp</pre>

        <h3 style="font-size:14px;font-weight:700;margin:20px 0 8px"><?php esc_html_e( 'Response Envelope', 'autonode-pro'); ?></h3>
        <pre class="amp-code-block">{
  "success": true,
  "data": { },
  "request_id": "uuid"
}</pre>

        <div class="amp-alert amp-alert-info" style="margin-top:16px">
            <strong><?php esc_html_e( 'Brute-Force Protection:', 'autonode-pro'); ?></strong> <?php esc_html_e( 'After 10 failed attempts within a 5-minute window, the IP is blocked with escalating durations (5min → 30min → 2h → 24h). Successful auth clears the block.', 'autonode-pro'); ?>
        </div>
    </div>
</section>

<!-- Posts -->
<section class="amp-doc-section" id="posts">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">📝</span>
        <h2><?php esc_html_e( 'Posts', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <div class="amp-ep-list" style="margin-bottom:16px">
            <?php
            $autonode_eps = array(
                array( 'GET',    '/posts',              'posts:read',    __( 'List posts — per_page, page, status, search, orderby, order, category', 'autonode-pro') ),
                array( 'POST',   '/posts',              'posts:write',   __( 'Create post with optional inline SEO', 'autonode-pro') ),
                array( 'GET',    '/posts/{id}',         'posts:read',    __( 'Get post + full SEO fields + all meta', 'autonode-pro') ),
                array( 'PUT',    '/posts/{id}',         'posts:write',   __( 'Update post fields + optional inline SEO', 'autonode-pro') ),
                array( 'DELETE', '/posts/{id}',         'posts:delete',  __( 'Trash post. Add ?force=true for permanent delete', 'autonode-pro') ),
                array( 'POST',   '/posts/{id}/publish', 'posts:publish', __( 'Publish a draft post immediately', 'autonode-pro') ),
            );
            foreach ( $autonode_eps as list( $autonode_m, $autonode_ep, $autonode_sc, $autonode_d ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-<?php echo esc_attr( strtolower( $autonode_m ) ); ?>"><?php echo esc_html( $autonode_m ); ?></span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span>
                </div>
                <div class="amp-ep-desc"><?php echo esc_html( $autonode_d ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/posts
{
  "title":          "Article Title",
  "content":        "&lt;p&gt;HTML content&lt;/p&gt;",
  "status":         "draft",              // draft | publish | pending | private | future
  "excerpt":        "Short summary",
  "slug":           "custom-url-slug",
  "featured_media": 2231,                 // attachment ID
  "category_names": ["AI Marketing","SEO"],  // auto-created if missing
  "tag_names":      ["n8n","automation"],     // auto-created if missing
  "date":           "2026-03-20T10:00:00Z",
  "meta":           { "custom_field": "value" },
  "seo": {
    "focus_keyword": "AI marketing automation",
    "title":         "SEO Title 2026",
    "description":   "Meta description 145155 chars",
    "canonical_url": "https://example.com/ai-marketing/",
    "og_title":      "OG Title", "og_description": "OG Desc",
    "og_image_id":   2231,
    "robots":        "index,follow",
    "pillar_content": true
  }
}</pre>
    </div>
</section>

<!-- Pages -->
<section class="amp-doc-section" id="pages">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">📄</span>
        <h2><?php esc_html_e( 'Pages', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Identical payload to Posts. Replace', 'autonode-pro'); ?> <code>/posts</code> → <code>/pages</code>. <?php esc_html_e( 'Scopes:', 'autonode-pro'); ?> <code>pages:read</code> <code>pages:write</code> <code>pages:delete</code></p>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/pages
{ "title": "Landing Page", "content": "", "status": "publish", "parent_id": 0 }</pre>
    </div>
</section>

<!-- SEO -->
<section class="amp-doc-section" id="seo">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🔍</span>
        <h2><?php esc_html_e( 'SEO Fields', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Read or update all SEO fields independently. Works for both posts and pages.', 'autonode-pro'); ?></p>
        <div class="amp-ep-list" style="margin-bottom:16px">
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-get">GET</span>
                    <code class="amp-ep-path">/posts/{id}/seo</code>
                    <span class="amp-ep-scope">seo:read</span>
                    <code class="amp-ep-path">/pages/{id}/seo</code>
                </div>
                <div class="amp-ep-desc"><?php esc_html_e( 'Returns all Rank Math / Yoast SEO fields', 'autonode-pro'); ?></div>
            </div>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-put">PUT</span>
                    <code class="amp-ep-path">/posts/{id}/seo</code>
                    <span class="amp-ep-scope">seo:write</span>
                </div>
                <div class="amp-ep-desc"><?php esc_html_e( 'Update any subset of SEO fields', 'autonode-pro'); ?></div>
            </div>
        </div>
        <pre class="amp-code-block">// Full field list (Rank Math):
{
  "focus_keyword", "title", "description", "robots", "canonical_url",
  "og_title", "og_description", "og_image", "og_image_id", "og_object_type",
  "twitter_title", "twitter_description", "twitter_image", "twitter_card_type",
  "primary_category", "schema_type", "breadcrumb_title",
  "pillar_content", "advanced_robots", "faq_schema"
}</pre>
    </div>
</section>

<!-- Meta -->
<section class="amp-doc-section" id="meta">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🔑</span>
        <h2><?php esc_html_e( 'Post Meta', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Dedicated endpoint for custom fields. SEO meta keys (rank_math_*, _yoast_wpseo_*) are blocked — use the', 'autonode-pro'); ?> <code>/seo</code> <?php esc_html_e( 'endpoint for those.', 'autonode-pro'); ?></p>
        <div class="amp-ep-list" style="margin-bottom:16px">
            <?php foreach ( array(
                array( 'GET',    '/posts/{id}/meta',         'posts:read' ),
                array( 'PUT',    '/posts/{id}/meta',         'posts:write' ),
                array( 'DELETE', '/posts/{id}/meta/{key}',   'posts:write' ),
            ) as list( $autonode_m, $autonode_ep, $autonode_sc ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-<?php echo esc_attr( strtolower( $autonode_m ) ); ?>"><?php echo esc_html( $autonode_m ); ?></span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <pre class="amp-code-block">PUT /posts/123/meta
{ "custom_css_class": "hero-blue", "reading_time_minutes": 5 }

// Response:
{ "post_id": 123, "updated": 2, "skipped": 0, "errors": [] }</pre>
    </div>
</section>

<!-- Media -->
<section class="amp-doc-section" id="media">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🖼️</span>
        <h2><?php esc_html_e( 'Media', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <div class="amp-ep-list" style="margin-bottom:16px">
            <?php foreach ( array(
                array( 'GET',    '/media',           'media:read' ),
                array( 'GET',    '/media/{id}',       'media:read' ),
                array( 'PUT',    '/media/{id}',       'media:write' ),
                array( 'DELETE', '/media/{id}',       'media:delete' ),
            ) as list( $autonode_m, $autonode_ep, $autonode_sc ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-<?php echo esc_attr( strtolower( $autonode_m ) ); ?>"><?php echo esc_html( $autonode_m ); ?></span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <h4 style="font-size:13px;font-weight:700;margin:0 0 8px"><?php esc_html_e( 'Upload via base64', 'autonode-pro'); ?></h4>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/media
{
  "filename":    "featured.jpg",
  "file_base64": "&lt;base64 bytes&gt;",
  "mime_type":   "image/jpeg",
  "title":       "Image Title",
  "alt":         "Alt text for SEO",
  "post_id":     123
}</pre>
        <h4 style="font-size:13px;font-weight:700;margin:16px 0 8px"><?php esc_html_e( 'Sideload from URL (n8n-friendly)', 'autonode-pro'); ?></h4>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/media/sideload
{
  "url":      "https://cdn.example.com/image.jpg",
  "title":    "Featured Image",
  "alt":      "AI robot working at desk",
  "filename": "featured-image.jpg",
  "post_id":  123
}

// Returns:
{ "id": 2231, "title": "", "source_url": "https://…", "width": 1920, "height": 1080 }</pre>
    </div>
</section>

<!-- Taxonomy -->
<section class="amp-doc-section" id="taxonomy">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🏷️</span>
        <h2><?php esc_html_e( 'Taxonomy', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <pre class="amp-code-block">GET  <?php echo esc_html( $api_base ); ?>/categories    // all categories
POST <?php echo esc_html( $api_base ); ?>/categories    { "name": "AI Tools", "slug": "ai-tools" }
GET  <?php echo esc_html( $api_base ); ?>/tags          // all tags
POST <?php echo esc_html( $api_base ); ?>/tags          { "name": "automation" }

// Auto-create in post body (recommended — no pre-check needed):
POST /posts
{
  "category_names": ["AI Marketing", "Tutorials"],
  "tag_names":      ["n8n", "automation"]
}</pre>
    </div>
</section>

<!-- Bulk -->
<section class="amp-doc-section" id="bulk">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">⚡</span>
        <h2><?php esc_html_e( 'Bulk Operations', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Process up to 50 items per request. Scope:', 'autonode-pro'); ?> <code>bulk:write</code></p>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/bulk
{
  "operation": "publish",    // publish | draft | trash | delete | update_seo | update_meta
  "items": [
    { "id": 101 },
    { "id": 102 },
    { "id": 103, "seo": { "focus_keyword": "new keyword" } }
  ]
}

// Response:
{
  "operation": "publish",
  "total": 3,
  "success": 2,
  "errors": 1,
  "results": [
    { "id": 101, "status": "success", "new_status": "publish" },
    { "id": 102, "status": "success" },
    { "id": 103, "status": "error", "message": "Post not found." }
  ]
}</pre>
    </div>
</section>

<!-- Webhooks -->
<section class="amp-doc-section" id="webhooks">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🪝</span>
        <h2><?php esc_html_e( 'Webhooks', 'autonode-pro'); ?></h2>
    </div>
   <div class="amp-doc-section-body">
        <p class="amp-doc-intro"><?php esc_html_e( 'Webhooks fire asynchronously via WP-Cron with automatic retry (up to 3 attempts: 60s → 300s → 900s delays).', 'autonode-pro'); ?></p>
        <div class="amp-ep-list" style="margin-bottom:14px">
            <?php foreach ( array(
                array( 'GET',    '/webhooks',                   'webhooks:read' ),
                array( 'POST',   '/webhooks',                   'webhooks:write' ),
                array( 'PUT',    '/webhooks/{id}',              'webhooks:write' ),
                array( 'DELETE', '/webhooks/{id}',              'webhooks:write' ),
                array( 'POST',   '/webhooks/{id}/test',         'webhooks:write' ),
                array( 'GET',    '/webhooks/{id}/deliveries',   'webhooks:read' ),
            ) as list( $autonode_m, $autonode_ep, $autonode_sc ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-<?php echo esc_attr( strtolower( $autonode_m ) ); ?>"><?php echo esc_html( $autonode_m ); ?></span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/webhooks
{
  "label":      "n8n Post Published",
  "target_url": "https://n8n.example.com/webhook/",
  "secret":     "my-signing-secret",       // optional HMAC signing
  "events":     ["post.published","page.published"],
  "post_types": ["post","page"]
}</pre>
        <h4 style="font-size:13px;font-weight:700;margin:16px 0 8px"><?php esc_html_e( 'Available Events', 'autonode-pro'); ?></h4>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px">
            <?php foreach ( array( 'post.created', 'post.updated', 'post.published', 'post.deleted', 'page.created', 'page.updated', 'page.published', 'page.deleted', 'media.uploaded', 'test' ) as $autonode_ev ) : ?>
            <span class="amp-ep-scope"><?php echo esc_html( $autonode_ev ); ?></span>
            <?php endforeach; ?>
        </div>
        <h4 style="font-size:13px;font-weight:700;margin:0 0 8px"><?php esc_html_e( 'Webhook Payload', 'autonode-pro'); ?></h4>
        <pre class="amp-code-block">{
  "event":       "post.published",
  "object_id":   123,
  "object_type": "post",
  "timestamp":   "2026-03-21T10:30:00+00:00",
  "site_url":    "https://example.com",
  "post": { "id": 123, "title": "", "status": "publish", "link": "https://…" },
  "seo": { "plugin": "rankmath", "focus_keyword": "", "title": "" }
}</pre>
    </div>
</section>

<!-- Analytics -->
<section class="amp-doc-section" id="analytics">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">📊</span>
        <h2><?php esc_html_e( 'Analytics', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <div class="amp-ep-list">
            <?php foreach ( array(
                array( 'GET', '/analytics/summary',          'analytics:read', __( "Today's requests, week total, 24h errors, avg ms", 'autonode-pro') ),
                array( 'GET', '/analytics/hourly?hours=24',   'analytics:read', __( 'Per-hour buckets (max 168 hours)', 'autonode-pro') ),
                array( 'GET', '/analytics/endpoints',         'analytics:read', __( 'Top 10 endpoints by hits (7d)', 'autonode-pro') ),
                array( 'GET', '/analytics/keys',             'analytics:read', __( 'Per-key breakdown: requests, avg ms, errors (7d)', 'autonode-pro') ),
            ) as list( $autonode_m, $autonode_ep, $autonode_sc, $autonode_d ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-get">GET</span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span>
                </div>
                <div class="amp-ep-desc"><?php echo esc_html( $autonode_d ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Key Management -->
<section class="amp-doc-section" id="keys">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🔑</span>
        <h2><?php esc_html_e( 'Key Management', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <div class="amp-ep-list" style="margin-bottom:14px">
            <?php foreach ( array(
                array( 'GET',    '/keys',                   'keys:read',   __( 'List all your API keys', 'autonode-pro') ),
                array( 'POST',   '/keys/{id}/revoke',       'keys:write',  __( 'Permanently revoke a key', 'autonode-pro') ),
                array( 'POST',   '/keys/{id}/rotate',       'keys:write',  __( 'Generate new secret, keep same ID and scopes', 'autonode-pro') ),
            ) as list( $autonode_m, $autonode_ep, $autonode_sc, $autonode_d ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-<?php echo esc_attr( strtolower( $autonode_m ) ); ?>"><?php echo esc_html( $autonode_m ); ?></span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span>
                </div>
                <div class="amp-ep-desc"><?php echo esc_html( $autonode_d ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/keys/{id}/rotate
// Returns new raw key — store immediately:
{
  "raw_key": "ampcm_&lt;new-64-hex&gt;",
  "id": 5,
  "prefix": "ampcm_a3f8",
  "label": "n8n Production"
}</pre>
    </div>
</section>

<!-- System -->
<section class="amp-doc-section" id="system">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">⚙️</span>
        <h2><?php esc_html_e( 'System', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">
        <div class="amp-ep-list">
            <?php foreach ( array(
                array( 'GET', '/ping',        null,           __( 'Public health check — no auth required', 'autonode-pro') ),
                array( 'GET', '/status',      'system:read',  __( 'Key info, scopes, SEO plugin, cron status', 'autonode-pro') ),
                array( 'GET', '/cron-health', 'system:read',  __( 'Detailed cron health: last ping, stale warning', 'autonode-pro') ),
                array( 'GET', '/blocked-ips', 'system:read',  __( 'Current brute-force IP blocks', 'autonode-pro') ),
            ) as list( $autonode_m, $autonode_ep, $autonode_sc, $autonode_d ) ) : ?>
            <div class="amp-ep-card">
                <div class="amp-ep-row">
                    <span class="amp-ep-method amp-method-get">GET</span>
                    <code class="amp-ep-path"><?php echo esc_html( $autonode_ep ); ?></code>
                    <?php if ( $autonode_sc ) : ?><span class="amp-ep-scope"><?php echo esc_html( $autonode_sc ); ?></span><?php endif; ?>
                </div>
                <div class="amp-ep-desc"><?php echo esc_html( $autonode_d ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Errors -->
<section class="amp-doc-section" id="errors">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">⚠️</span>
        <h2><?php esc_html_e( 'Error Reference', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body" style="overflow-x:auto">
        <table class="amp-err-table">
            <thead><tr>
                <th><?php esc_html_e( 'HTTP', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Code', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Cause', 'autonode-pro'); ?></th>
            </tr></thead>
            <tbody>
            <?php
            $autonode_errors = array(
                array( 401, 'amp_no_auth',       __( 'Missing Authorization / X-API-Key header', 'autonode-pro') ),
                array( 401, 'amp_bad_token',     __( "Token doesn't start with ampcm_", 'autonode-pro') ),
                array( 401, 'amp_invalid_key',   __( 'Key not found, revoked, or expired', 'autonode-pro') ),
                array( 403, 'amp_ip_blocked',    __( 'IP not in whitelist or brute-force blocked', 'autonode-pro') ),
                array( 403, 'amp_forbidden',     __( 'Key lacks required scope', 'autonode-pro') ),
                array( 403, 'amp_ssl_required',  __( 'HTTPS required but HTTP used', 'autonode-pro') ),
                array( 404, 'amp_not_found',     __( 'Resource not found', 'autonode-pro') ),
                array( 429, 'amp_rate_limited',  __( 'Rate limit exceeded — check X-RateLimit-Reset', 'autonode-pro') ),
                array( 429, 'amp_ip_blocked',    __( 'Brute-force block — check retry-after seconds', 'autonode-pro') ),
                array( 400, 'amp_invalid',       __( 'Missing or invalid field in request body', 'autonode-pro') ),
            );
            foreach ( $autonode_errors as list( $autonode_code, $autonode_err_code, $autonode_cause ) ) : ?>
            <tr class="amp-err-<?php echo (int) $autonode_code; ?>">
                <td class="amp-err-http"><?php echo (int) $autonode_code; ?></td>
                <td><code class="amp-err-code"><?php echo esc_html( $autonode_err_code ); ?></code></td>
                <td class="amp-err-cause"><?php echo esc_html( $autonode_cause ); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- n8n Examples -->
<section class="amp-doc-section" id="n8n">
    <div class="amp-doc-section-header">
        <span class="amp-doc-section-icon">🔧</span>
        <h2><?php esc_html_e( 'n8n Workflow Examples', 'autonode-pro'); ?></h2>
    </div>
    <div class="amp-doc-section-body">

        <div class="amp-n8n-example">
            <h4><span class="amp-n8n-num">1</span><?php esc_html_e( 'Header Auth Credential', 'autonode-pro'); ?></h4>
            <pre class="amp-code-block">Type: Header Auth
Name: Authorization
Value: Bearer ampcm_YOUR_KEY_HERE</pre>
        </div>

        <div class="amp-n8n-example">
            <h4><span class="amp-n8n-num">2</span><?php esc_html_e( 'Create Post with Full SEO', 'autonode-pro'); ?></h4>
            <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/posts
{
  "title":          "{{ $json.seo_title }}",
  "content":        "{{ $json.article_html }}",
  "status":         "draft",
  "category_names": {{ $json.categories.split(',').map(s => s.trim()) }},
  "tag_names":      {{ $json.tags.split(',').map(s => s.trim()) }},
  "featured_media": {{ $json.wp_media_id }},
  "seo": {
    "focus_keyword": "{{ $json.seo_focus_keyword }}",
    "title":         "{{ $json.seo_title }}",
    "description":   "{{ $json.seo_meta_description }}",
    "robots":        "index,follow",
    "pillar_content": false
  }
}</pre>
        </div>

        <div class="amp-n8n-example">
            <h4><span class="amp-n8n-num">3</span><?php esc_html_e( 'Upload Image from URL (sideload)', 'autonode-pro'); ?></h4>
            <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/media/sideload
{
  "url":   "{{ $json.image_url }}",
  "title": "{{ $json.image_title }}",
  "alt":   "{{ $json.image_alt }}"
}
// Returns: { "media": { "id": 2231, "source_url": "…" } }
// Use media.id as featured_media in /posts</pre>
        </div>

        <div class="amp-n8n-example">
            <h4><span class="amp-n8n-num">4</span><?php esc_html_e( 'Bulk Publish After QA Gate', 'autonode-pro'); ?></h4>
            <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/bulk
{
  "operation": "publish",
  "items": {{ $items.map(p => ({ id: p.json.wp_post_id })) }}
}</pre>
        </div>

        <div class="amp-n8n-example">
            <h4><span class="amp-n8n-num">5</span><?php esc_html_e( 'Verify Webhook HMAC Signature', 'autonode-pro'); ?></h4>
            <pre class="amp-code-block">// Code node after Webhook trigger:
const crypto = require('crypto');
const body   = $input.first().json.rawBody;
const secret = 'your-webhook-secret';
const expected = 'sha256=' + crypto.createHmac('sha256', secret).update(body).digest('hex');
const received  = $input.first().headers['x-autonode-signature'];
if (expected !== received) throw new Error('Signature mismatch');
return $input.all();</pre>
        </div>

        <div class="amp-n8n-example">
            <h4><span class="amp-n8n-num">6</span><?php esc_html_e( 'Rotate Key (automated rotation workflow)', 'autonode-pro'); ?></h4>
            <pre class="amp-code-block">POST <?php echo esc_html( $api_base ); ?>/keys/{{ $json.key_id }}/rotate
// Returns new raw_key — immediately update n8n credential with new value</pre>
        </div>

    </div>
</section>

</div>
</div>
</div>