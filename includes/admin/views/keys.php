<?php defined( 'ABSPATH' ) || exit; ?>
<?php
$autonode_active_count   = count( array_filter( $keys, fn( $k ) => ! $k['revoked'] ) );
$autonode_total_requests = number_format( array_sum( array_map( fn( $k ) => (int) $k['total_requests'], $keys ) ) );
$autonode_revoked_count  = count( array_filter( $keys, fn( $k ) => $k['revoked'] ) );
?>
<style>
/* ── Keys Page ─────────────────────────────────────────────── */
#autonode-keys .amp-wrap { padding: 0 0 40px; }

.amp-keys-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 50%, #1e3a5f 100%);
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
.amp-keys-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.amp-keys-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 80px;
    width: 240px; height: 240px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}
.amp-keys-hero-left { position: relative; z-index: 1; }
.amp-keys-hero h1 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.amp-keys-hero h1 img { border-radius: 6px; }
.amp-keys-hero p {
    margin: 0;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
}
.amp-keys-hero-right {
    display: flex;
    gap: 16px;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
}
.amp-hero-stat {
    text-align: center;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    padding: 14px 20px;
    min-width: 100px;
}
.amp-hero-stat-val {
    display: block;
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    font-family: 'JetBrains Mono', monospace;
}
.amp-hero-stat-label {
    display: block;
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
    margin-top: 4px;
}

/* ── Form Card ────────────────────────────────────────────── */
.amp-keys-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
}
@media(max-width: 1100px) { .amp-keys-layout { grid-template-columns: 1fr; } }

.amp-form-card {
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    overflow: hidden;
}

.amp-form-section {
    padding: 24px 28px;
    border-bottom: 1px solid var(--amp-border);
}
.amp-form-section:last-child { border-bottom: none; }

.amp-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.amp-section-num {
    width: 28px; height: 28px;
    background: rgba(34, 113, 177, 0.1);
    color: var(--amp-primary);
    border: 1px solid rgba(34, 113, 177, 0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; flex-shrink: 0;
}
.amp-section-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--amp-text);
}
.amp-section-header p {
    margin: 4px 0 0 38px;
    font-size: 12px;
    color: var(--amp-muted);
}

/* ── Form Fields ───────────────────────────────────────────── */
.amp-fields-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.amp-field-full { grid-column: 1 / -1; }

.amp-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--amp-dim);
    margin-bottom: 6px;
    letter-spacing: 0.02em;
}
.amp-label small {
    font-weight: 400;
    color: var(--amp-muted);
    display: block;
    font-size: 11px;
    margin-top: 2px;
}

.amp-input, .amp-select, .amp-textarea {
    width: 100%;
    background: var(--amp-card2);
    border: 1.5px solid var(--amp-border);
    border-radius: 8px;
    color: var(--amp-text);
    padding: 10px 14px;
    font-size: 13px;
    font-family: var(--amp-sans);
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.amp-input:focus, .amp-select:focus, .amp-textarea:focus {
    border-color: var(--amp-primary);
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
    background: var(--amp-card);
}
.amp-input::placeholder { color: var(--amp-muted); }
.amp-textarea { min-height: 80px; resize: vertical; font-family: var(--amp-mono); font-size: 12px; }
.amp-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23646970' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

/* ── Scope Presets ────────────────────────────────────────── */
.amp-preset-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.amp-preset-btn {
    padding: 8px 18px;
    border-radius: 8px;
    border: 1.5px solid var(--amp-border);
    background: var(--amp-card2);
    color: var(--amp-dim);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.amp-preset-btn:hover { border-color: var(--amp-primary); color: var(--amp-primary); background: rgba(34, 113, 177, 0.05); }
.amp-preset-btn.active { background: rgba(34, 113, 177, 0.12); color: var(--amp-primary); border-color: var(--amp-primary); box-shadow: 0 0 0 1px var(--amp-primary); }

/* ── Scope Grid ───────────────────────────────────────────── */
.amp-perm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0;
    border: 1.5px solid var(--amp-border);
    border-radius: 10px;
    overflow: hidden;
}
.amp-perm-group { border-right: 1px solid var(--amp-border); border-bottom: 1px solid var(--amp-border); }
.amp-perm-group:last-child { border-right: none; }
.amp-perm-group-header {
    padding: 10px 14px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--amp-primary);
    background: var(--amp-card2);
    border-bottom: 1px solid var(--amp-border);
}
.amp-perm-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    font-size: 12px;
    cursor: pointer;
    color: var(--amp-dim);
    transition: background 0.15s;
    font-family: var(--amp-mono);
}
.amp-perm-item:hover { background: rgba(34, 113, 177, 0.04); color: var(--amp-text); }
.amp-perm-item input { accent-color: var(--amp-primary); width: 14px; height: 14px; flex-shrink: 0; cursor: pointer; }
.amp-perm-item input:checked + span { color: var(--amp-primary); font-weight: 600; }

/* ── Form Actions ─────────────────────────────────────────── */
.amp-form-actions {
    padding: 20px 28px;
    background: var(--amp-card2);
    border-top: 1px solid var(--amp-border);
    display: flex;
    align-items: center;
    gap: 12px;
}

/* ── Submit Button ────────────────────────────────────────── */
.amp-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    background: var(--amp-primary);
    color: #fff;
    transition: all 0.2s;
    letter-spacing: 0.02em;
}
.amp-btn-primary:hover { background: var(--amp-accent); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34, 113, 177, 0.3); }
.amp-btn-primary:active { transform: translateY(0); }

/* ── Quick Reference Sidebar ──────────────────────────────── */
.amp-ref-card {
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    overflow: hidden;
}
.amp-ref-card-header {
    padding: 16px 20px;
    background: var(--amp-card2);
    border-bottom: 1px solid var(--amp-border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.amp-ref-card-header h3 { margin: 0; font-size: 14px; font-weight: 700; color: var(--amp-text); }
.amp-ref-card-header span { font-size: 16px; }
.amp-ref-card-body { padding: 20px; }

.amp-ref-block { margin-bottom: 20px; }
.amp-ref-block:last-child { margin-bottom: 0; }
.amp-ref-block-title {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--amp-muted);
    margin-bottom: 8px;
}

.amp-ref-code {
    background: var(--amp-card2);
    border: 1px solid var(--amp-border);
    border-radius: 6px;
    padding: 10px 14px;
    font-family: var(--amp-mono);
    font-size: 12px;
    color: var(--amp-primary);
    word-break: break-all;
    line-height: 1.6;
}

.amp-ref-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.amp-ref-tag {
    display: inline-block;
    background: rgba(34, 113, 177, 0.08);
    color: var(--amp-primary);
    border: 1px solid rgba(34, 113, 177, 0.2);
    padding: 3px 9px;
    border-radius: 5px;
    font-size: 11px;
    font-family: var(--amp-mono);
    font-weight: 600;
}

.amp-ref-divider { border: none; border-top: 1px solid var(--amp-border); margin: 16px 0; }

/* ── Warning Banner ───────────────────────────────────────── */
.amp-warn-banner {
    background: rgba(219, 166, 23, 0.1);
    border: 1px solid rgba(219, 166, 23, 0.3);
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 20px;
    font-size: 13px;
    color: var(--amp-dim);
}
.amp-warn-banner strong { color: var(--amp-yellow); }
.amp-warn-icon { font-size: 16px; flex-shrink: 0; }

/* ── New Key Box ──────────────────────────────────────────── */
.amp-new-key-box {
    background: linear-gradient(135deg, rgba(0, 163, 42, 0.08), rgba(0, 163, 42, 0.03));
    border: 1.5px solid rgba(0, 163, 42, 0.4);
    border-radius: 10px;
    padding: 24px;
    margin-top: 20px;
    display: none;
}
.amp-new-key-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}
.amp-new-key-header strong { font-size: 15px; color: var(--amp-green); }
.amp-key-display-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--amp-card2);
    border: 1px solid rgba(0, 163, 42, 0.25);
    border-radius: 8px;
    padding: 12px 16px;
    overflow-x: auto;
    margin-bottom: 8px;
}
.amp-key-display-row code {
    font-family: var(--amp-mono);
    font-size: 14px;
    color: var(--amp-green);
    flex: 1;
    word-break: break-all;
    user-select: all;
}
.amp-copy-btn-sm {
    padding: 5px 12px;
    border-radius: 6px;
    border: 1px solid var(--amp-border);
    background: var(--amp-card);
    color: var(--amp-dim);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}
.amp-copy-btn-sm:hover { border-color: var(--amp-primary); color: var(--amp-primary); }
.amp-key-note {
    font-size: 12px;
    color: var(--amp-yellow);
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.amp-dismiss-btn {
    margin-top: 14px;
    padding: 9px 18px;
    border-radius: 8px;
    border: 1.5px solid rgba(0, 163, 42, 0.4);
    background: transparent;
    color: var(--amp-green);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.amp-dismiss-btn:hover { background: rgba(0, 163, 42, 0.1); }

/* ── Table Section ─────────────────────────────────────────── */
.amp-table-section { margin-top: 28px; }
.amp-table-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.amp-table-section-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: var(--amp-text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.amp-table-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.amp-search-input {
    padding: 7px 14px;
    border: 1.5px solid var(--amp-border);
    border-radius: 8px;
    font-size: 13px;
    background: var(--amp-card);
    color: var(--amp-text);
    outline: none;
    min-width: 200px;
    transition: border-color 0.2s;
}
.amp-search-input:focus { border-color: var(--amp-primary); box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1); }
.amp-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--amp-dim);
    cursor: pointer;
    white-space: nowrap;
}
.amp-toggle { position: relative; display: inline-block; width: 36px; height: 20px; flex-shrink: 0; }
.amp-toggle input { opacity: 0; width: 0; height: 0; }
.amp-toggle-slider { position: absolute; cursor: pointer; inset: 0; background: var(--amp-border2); border-radius: 20px; transition: 0.3s; }
.amp-toggle-slider::before { content: ''; position: absolute; width: 14px; height: 14px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.amp-toggle input:checked + .amp-toggle-slider { background: var(--amp-primary); }
.amp-toggle input:checked + .amp-toggle-slider::before { transform: translateX(16px); }
</style>

<div class="amp-wrap" id="autonode-keys">

<!-- Hero Banner -->
<div class="amp-keys-hero">
    <div class="amp-keys-hero-left">
        <h1>
            <img src="<?php echo esc_url( AUTONODE_URL . 'assets/logo.png' ); ?>" alt="AutoNode" style="width:28px;height:28px;border-radius:6px;vertical-align:middle;">
            <?php esc_html_e( 'API Keys', 'autonode-pro'); ?>
        </h1>
        <p><?php esc_html_e( 'Scoped API keys for n8n and external automation clients.', 'autonode-pro'); ?></p>
    </div>
    <div class="amp-keys-hero-right">
        <div class="amp-hero-stat">
            <span class="amp-hero-stat-val"><?php echo esc_html( $autonode_active_count ); ?></span>
            <span class="amp-hero-stat-label"><?php esc_html_e( 'Active', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-hero-stat">
            <span class="amp-hero-stat-val"><?php echo esc_html( $autonode_total_requests ); ?></span>
            <span class="amp-hero-stat-label"><?php esc_html_e( 'Requests', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-hero-stat">
            <span class="amp-hero-stat-val"><?php echo esc_html( $autonode_revoked_count ); ?></span>
            <span class="amp-hero-stat-label"><?php esc_html_e( 'Revoked', 'autonode-pro'); ?></span>
        </div>
    </div>
</div>

<!-- Main Layout -->
<div class="amp-keys-layout">

    <!-- Left: Create Form -->
    <div class="amp-form-card">
        <form id="amp-create-key-form">

            <!-- Section 1: Basic Info -->
            <div class="amp-form-section">
                <div class="amp-section-header">
                    <div class="amp-section-num">1</div>
                    <div>
                        <h3><?php esc_html_e( 'Basic Information', 'autonode-pro'); ?></h3>
                        <p><?php esc_html_e( 'Give this key a name and optional description.', 'autonode-pro'); ?></p>
                    </div>
                </div>
                <div class="amp-fields-row">
                    <div>
                        <label class="amp-label"><?php esc_html_e( 'Label', 'autonode-pro'); ?> <span style="color:var(--amp-red)">*</span></label>
                        <input type="text" name="label" class="amp-input" placeholder="<?php esc_attr_e( 'e.g. n8n Production', 'autonode-pro'); ?>" required>
                    </div>
                    <div>
                        <label class="amp-label"><?php esc_html_e( 'Description', 'autonode-pro'); ?></label>
                        <input type="text" name="description" class="amp-input" placeholder="<?php esc_attr_e( 'What is this key used for?', 'autonode-pro'); ?>">
                    </div>
                    <div>
                        <label class="amp-label"><?php esc_html_e( 'Environment', 'autonode-pro'); ?></label>
                        <select name="environment" class="amp-select">
                            <option value="production">🏢 Production</option>
                            <option value="staging">🧪 Staging</option>
                            <option value="development">🔧 Development</option>
                        </select>
                    </div>
                    <div>
                        <label class="amp-label"><?php esc_html_e( 'Expires', 'autonode-pro'); ?> <small><?php esc_html_e( 'optional', 'autonode-pro'); ?></small></label>
                        <input type="datetime-local" name="expires_at" class="amp-input">
                    </div>
                </div>
            </div>

            <!-- Section 2: Security -->
            <div class="amp-form-section">
                <div class="amp-section-header">
                    <div class="amp-section-num">2</div>
                    <div>
                        <h3><?php esc_html_e( 'Security & Access', 'autonode-pro'); ?></h3>
                        <p><?php esc_html_e( 'Restrict key usage by IP address.', 'autonode-pro'); ?></p>
                    </div>
                </div>
                <div class="amp-field-full">
                    <label class="amp-label"><?php esc_html_e( 'IP Whitelist', 'autonode-pro'); ?> <small><?php esc_html_e( 'CIDR notation, one per line. Leave blank for any IP.', 'autonode-pro'); ?></small></label>
                    <textarea name="ip_whitelist" class="amp-textarea" placeholder="<?php esc_attr_e( "203.0.113.0/24\n198.51.100.42\n0.0.0.0/0", 'autonode-pro'); ?>" rows="3"></textarea>
                </div>
            </div>

            <!-- Section 3: Scope Presets -->
            <div class="amp-form-section">
                <div class="amp-section-header">
                    <div class="amp-section-num">3</div>
                    <div>
                        <h3><?php esc_html_e( 'Permissions', 'autonode-pro'); ?></h3>
                        <p><?php esc_html_e( 'Choose a preset or fine-tune below.', 'autonode-pro'); ?></p>
                    </div>
                </div>

                <div style="margin-bottom:16px">
                    <label class="amp-label" style="margin-bottom:10px"><?php esc_html_e( 'Scope Preset', 'autonode-pro'); ?></label>
                    <div class="amp-preset-row">
                        <button type="button" class="amp-preset-btn" data-preset="readonly">👁️ <?php esc_html_e( 'Read Only', 'autonode-pro'); ?></button>
                        <button type="button" class="amp-preset-btn active" data-preset="writer">✏️ <?php esc_html_e( 'Content Writer', 'autonode-pro'); ?></button>
                        <button type="button" class="amp-preset-btn" data-preset="full_access">🔓 <?php esc_html_e( 'Full Access', 'autonode-pro'); ?></button>
                    </div>
                </div>

                <label class="amp-label" style="margin-bottom:10px"><?php esc_html_e( 'Fine-Tune Permissions', 'autonode-pro'); ?></label>
                <div class="amp-perm-grid">
                    <?php
                    $autonode_groups = array(
                        'Posts'     => array( 'posts:read', 'posts:write', 'posts:delete', 'posts:publish' ),
                        'Pages'     => array( 'pages:read', 'pages:write', 'pages:delete' ),
                        'SEO'       => array( 'seo:read', 'seo:write' ),
                        'Media'     => array( 'media:read', 'media:write', 'media:delete' ),
                        'Taxonomy'  => array( 'taxonomy:read', 'taxonomy:write' ),
                        'Advanced'  => array( 'bulk:write', 'webhooks:read', 'webhooks:write', 'analytics:read', 'keys:read', 'system:read' ),
                    );
                    $autonode_default_writer = \AutoNode\Api_Auth::PRESET_SCOPES['writer'];
                    foreach ( $autonode_groups as $autonode_group => $autonode_scopes ) : ?>
                    <div class="amp-perm-group">
                        <div class="amp-perm-group-header"><?php echo esc_html( $autonode_group ); ?></div>
                        <?php foreach ( $autonode_scopes as $autonode_scope ) : ?>
                        <label class="amp-perm-item">
                            <input type="checkbox" name="scopes[]" value="<?php echo esc_attr( $autonode_scope ); ?>" <?php echo in_array( $autonode_scope, $autonode_default_writer, true ) ? 'checked' : ''; ?>>
                            <span><?php echo esc_html( $autonode_scope ); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Section 4: Submit -->
            <div class="amp-form-actions">
                <button type="submit" class="amp-btn-primary" id="amp-create-key-btn">
                    🔑 <?php esc_html_e( 'Generate API Key', 'autonode-pro'); ?>
                </button>
                <span style="font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Raw key shown once — store immediately.', 'autonode-pro'); ?></span>
            </div>
        </form>

        <!-- New Key Result -->
        <div id="amp-new-key-result" style="display:none;padding:0 28px 28px">
            <div class="amp-warn-banner">
                <span class="amp-warn-icon">⚠️</span>
                <div><?php esc_html_e( 'This key is shown once and cannot be retrieved later. Copy and store it now.', 'autonode-pro'); ?></div>
            </div>
            <div class="amp-new-key-header"><strong>✅ <?php esc_html_e( 'Key Created — Copy it now!', 'autonode-pro'); ?></strong></div>

            <div style="margin-top:16px">
                <div style="font-size:11px;font-weight:700;color:var(--amp-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px"><?php esc_html_e( 'Raw API Key', 'autonode-pro'); ?></div>
                <div class="amp-key-display-row">
                    <code id="amp-raw-key"></code>
                    <button class="amp-copy-btn-sm amp-copy-btn" data-target="amp-raw-key">📋 Copy</button>
                </div>
            </div>

            <div style="margin-top:12px">
                <div style="font-size:11px;font-weight:700;color:var(--amp-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px"><?php esc_html_e( 'n8n Header Auth Value', 'autonode-pro'); ?></div>
                <div class="amp-key-display-row">
                    <code id="amp-n8n-val" style="font-size:13px"></code>
                    <button class="amp-copy-btn-sm amp-copy-btn" data-target="amp-n8n-val">📋 Copy</button>
                </div>
            </div>

            <div class="amp-key-note">🔒 <?php esc_html_e( 'This key is shown once. It cannot be retrieved after you dismiss this message.', 'autonode-pro'); ?></div>
            <button class="amp-dismiss-btn" id="amp-dismiss-key">✅ <?php esc_html_e( "I've saved it — Dismiss", 'autonode-pro'); ?></button>
        </div>
    </div>

    <!-- Right: Quick Reference -->
    <div class="amp-ref-card">
        <div class="amp-ref-card-header">
            <span>📋</span>
            <h3><?php esc_html_e( 'Quick Reference', 'autonode-pro'); ?></h3>
        </div>
        <div class="amp-ref-card-body">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'n8n HTTP Request Node', 'autonode-pro'); ?></div>
                <div class="amp-ref-code">Authorization: Bearer &lt;key&gt;</div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Base URL', 'autonode-pro'); ?></div>
                <div class="amp-ref-code" style="font-size:11px;word-break:break-all"><?php echo esc_url( get_site_url() . '/wp-json/' . AUTONODE_NS ); ?></div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Authentication', 'autonode-pro'); ?></div>
                <div class="amp-ref-tags">
                    <span class="amp-ref-tag">Header Auth</span>
                    <span class="amp-ref-tag">Bearer Token</span>
                </div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Key Prefix', 'autonode-pro'); ?></div>
                <div class="amp-ref-code">ampcm_xxxx…</div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Available Scopes', 'autonode-pro'); ?></div>
                <div class="amp-ref-tags">
                    <span class="amp-ref-tag">posts:read</span>
                    <span class="amp-ref-tag">posts:write</span>
                    <span class="amp-ref-tag">posts:publish</span>
                    <span class="amp-ref-tag">seo:read</span>
                    <span class="amp-ref-tag">seo:write</span>
                    <span class="amp-ref-tag">media:write</span>
                    <span class="amp-ref-tag">bulk:write</span>
                    <span class="amp-ref-tag">webhooks:read</span>
                    <span class="amp-ref-tag">analytics:read</span>
                    <span class="amp-ref-tag">system:read</span>
                </div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Example Request', 'autonode-pro'); ?></div>
                <div class="amp-ref-code" style="font-size:10px">POST /wp-json/autonode/v1/posts<br>Authorization: Bearer ampcm_xxxx…<br><br>{ "title": "My Post", "content": "..." }</div>
            </div>

        </div>
    </div>

</div>

<!-- All Keys Table -->
<div class="amp-table-section">
    <div class="amp-table-section-header">
        <h2>🔑 <?php esc_html_e( 'All API Keys', 'autonode-pro'); ?></h2>
        <div class="amp-table-controls">
            <input type="text" id="amp-key-search" class="amp-search-input" placeholder="<?php esc_attr_e( 'Search by label or prefix…', 'autonode-pro'); ?>">
            <label class="amp-toggle-wrap">
                <div class="amp-toggle">
                    <input type="checkbox" id="amp-show-revoked">
                    <div class="amp-toggle-slider"></div>
                </div>
                <?php esc_html_e( 'Show revoked', 'autonode-pro'); ?>
            </label>
        </div>
    </div>

    <div class="amp-form-card">
        <table class="amp-table widefat wp-list-table" style="margin:0">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Key', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Label', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Env', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Scopes', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Requests', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Last Used', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Expires', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Status', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Action', 'autonode-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $keys ) ) : ?>
                <tr><td colspan="9" class="amp-empty-cell"><?php esc_html_e( 'No keys yet. Create one above.', 'autonode-pro'); ?></td></tr>
            <?php else : ?>
            <?php foreach ( $keys as $autonode_k ) :
                $autonode_scopes    = json_decode( $autonode_k['scopes'], true ) ?: array();
                $autonode_is_active = ! $autonode_k['revoked'] && ( ! $autonode_k['expires_at'] || strtotime( $autonode_k['expires_at'] ) > time() );
            ?>
            <tr class="amp-key-row <?php echo esc_attr( $autonode_k['revoked'] ? 'amp-row-revoked' : '' ); ?>"
                data-revoked="<?php echo (int) $autonode_k['revoked']; ?>"
                data-label="<?php echo esc_attr( strtolower( $autonode_k['label'] . ' ' . $autonode_k['key_prefix'] ) ); ?>">

                <td><code class="amp-key-prefix" style="font-size:12px"><?php echo esc_html( $autonode_k['key_prefix'] ); ?>…</code></td>
                <td>
                    <strong style="font-size:13px"><?php echo esc_html( $autonode_k['label'] ); ?></strong>
                    <?php if ( $autonode_k['description'] ) : ?>
                        <br><small class="amp-muted" style="font-size:11px"><?php echo esc_html( substr( $autonode_k['description'], 0, 60 ) ); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $autonode_env_colors = array(
                        'production'  => 'var(--amp-green)',
                        'staging'     => 'var(--amp-yellow)',
                        'development' => 'var(--amp-blue)',
                    );
                    $autonode_env_color = $autonode_env_colors[ $autonode_k['environment'] ] ?? 'var(--amp-muted)';
                    ?>
                    <span class="amp-env-badge amp-env-<?php echo esc_attr( $autonode_k['environment'] ); ?>" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;padding:3px 8px;border-radius:5px;background:rgba(0,0,0,0.05);color:<?php echo esc_attr( $autonode_env_color ); ?>;border:1px solid rgba(0,0,0,0.1)">
                        <?php echo esc_html( $autonode_k['environment'] ); ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;flex-wrap:wrap;gap:3px">
                        <?php
                            if ( empty( $autonode_scopes ) ) {
                                echo '<span style="font-size:10px;background:rgba(192,132,252,0.1);color:var(--amp-purple);border:1px solid rgba(192,132,252,0.3);padding:2px 6px;border-radius:4px;font-family:var(--amp-mono)">All</span>';
                            } else {
                                foreach ( array_slice( $autonode_scopes, 0, 3 ) as $autonode_s ) {
                                    printf( '<span style="font-size:10px;background:rgba(34,113,177,0.08);color:var(--amp-primary);border:1px solid rgba(34,113,177,0.2);padding:2px 6px;border-radius:4px;font-family:var(--amp-mono)">%s</span>', esc_html( $autonode_s ) );
                                }
                                if ( count( $autonode_scopes ) > 3 ) {
                                    echo '<span style="font-size:10px;color:var(--amp-muted)">+' . ( count( $autonode_scopes ) - 3 ) . '</span>';
                                }
                            }
                        ?>
                    </div>
                </td>
                <td style="font-family:var(--amp-mono);font-size:13px;font-weight:600"><?php echo esc_html( number_format( (int) $autonode_k['total_requests'] ) ); ?></td>
                <td><small style="font-size:11px;color:var(--amp-muted)"><?php echo $autonode_k['last_used_at'] ? esc_html( human_time_diff( strtotime( $autonode_k['last_used_at'] ) ) . ' ago' ) : '—'; ?></small></td>
                <td><small style="font-size:11px;color:var(--amp-muted)"><?php echo $autonode_k['expires_at'] ? esc_html( substr( $autonode_k['expires_at'], 0, 10 ) ) : '—'; ?></small></td>
                <td>
                    <?php
                        if ( $autonode_k['revoked'] ) {
                            echo '<span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:5px;background:rgba(214,54,56,0.1);color:var(--amp-red);border:1px solid rgba(214,54,56,0.3)">Revoked</span>';
                        } elseif ( ! $autonode_is_active ) {
                            echo '<span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:5px;background:rgba(219,166,23,0.1);color:var(--amp-yellow);border:1px solid rgba(219,166,23,0.3)">Expired</span>';
                        } else {
                            echo '<span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:5px;background:rgba(0,163,42,0.1);color:var(--amp-green);border:1px solid rgba(0,163,42,0.3)">Active</span>';
                        }
                    ?>
                </td>
                <td>
                    <?php if ( ! $autonode_k['revoked'] ) : ?>
                    <div style="display:flex;gap:5px;flex-wrap:wrap">
                        <button class="amp-copy-btn-sm amp-copy-btn" data-target="amp-key-<?php echo (int) $autonode_k['id']; ?>" title="<?php esc_attr_e( 'Copy key prefix', 'autonode-pro'); ?>">📋</button>
                        <button class="amp-copy-btn-sm amp-rotate-btn" data-id="<?php echo (int) $autonode_k['id']; ?>" data-label="<?php echo esc_attr( $autonode_k['label'] ); ?>" style="font-size:11px">↻ <?php esc_html_e( 'Rotate', 'autonode-pro'); ?></button>
                        <button class="amp-copy-btn-sm amp-revoke-btn" data-id="<?php echo (int) $autonode_k['id']; ?>" data-label="<?php echo esc_attr( $autonode_k['label'] ); ?>" style="font-size:11px;color:var(--amp-red);border-color:rgba(214,54,56,0.3)">✕ <?php esc_html_e( 'Revoke', 'autonode-pro'); ?></button>
                    </div>
                    <span id="amp-key-<?php echo (int) $autonode_k['id']; ?>" style="display:none"><?php echo esc_html( $autonode_k['key_prefix'] ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>