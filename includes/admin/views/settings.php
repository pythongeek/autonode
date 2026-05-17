<?php defined( 'ABSPATH' ) || exit;
$autonode_s      = $autonode_settings;
$autonode_cron   = \AutoNode\Cron_Health::status();
$autonode_blocks = \AutoNode\Brute_Force::list_blocks();
$autonode_sp     = \AutoNode\Rankmath_Handler::active_plugin();
$autonode_block_count = count( $autonode_blocks );
?>
<style>
/* ── Settings Page ─────────────────────────────────────────── */
#autonode-settings .amp-wrap { padding: 0 0 40px; }

.amp-set-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563a8 50%, #1e3a5f 100%);
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
.amp-set-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.amp-set-hero-left { position: relative; z-index: 1; }
.amp-set-hero h1 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.amp-set-hero h1 img { border-radius: 6px; }
.amp-set-hero p { margin: 0; font-size: 13px; color: rgba(255,255,255,0.6); }
.amp-set-hero-right {
    display: flex; gap: 14px; position: relative; z-index: 1; flex-wrap: wrap;
}
.amp-set-stat {
    text-align: center;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    padding: 13px 18px;
    min-width: 95px;
}
.amp-set-stat-val { display: block; font-size: 26px; font-weight: 800; color: #fff; line-height: 1; font-family: 'JetBrains Mono', monospace; }
.amp-set-stat-label { display: block; font-size: 11px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; margin-top: 4px; }

/* ── Layout ─────────────────────────────────────────────────── */
.amp-set-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 24px;
    align-items: start;
}
@media(max-width: 1100px) { .amp-set-layout { grid-template-columns: 1fr; } }

/* ── Card ───────────────────────────────────────────────────── */
.amp-set-card {
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    overflow: hidden;
    margin-bottom: 20px;
}
.amp-set-card-header {
    padding: 16px 20px;
    background: var(--amp-card2);
    border-bottom: 1px solid var(--amp-border);
    display: flex; align-items: center; gap: 10px;
}
.amp-set-card-header .amp-set-icon { font-size: 18px; }
.amp-set-card-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: var(--amp-text); }
.amp-set-card-body { padding: 16px 20px; }

/* ── Settings Rows ─────────────────────────────────────────── */
.amp-set-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 14px 0;
    border-bottom: 1px solid var(--amp-border);
    flex-wrap: wrap;
}
.amp-set-row:last-child { border-bottom: none; padding-bottom: 0; }
.amp-set-row:first-child { padding-top: 0; }
.amp-set-label { flex: 1; min-width: 200px; }
.amp-set-label strong { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--amp-text); margin-bottom: 3px; }
.amp-set-label p { margin: 0; font-size: 12px; color: var(--amp-muted); line-height: 1.5; }
.amp-set-control { flex-shrink: 0; }

/* ── Toggle ─────────────────────────────────────────────────── */
.amp-toggle { position: relative; display: inline-block; width: 40px; height: 22px; }
.amp-toggle input { opacity: 0; width: 0; height: 0; }
.amp-toggle-slider { position: absolute; cursor: pointer; inset: 0; background: var(--amp-border2); border-radius: 22px; transition: 0.3s; }
.amp-toggle-slider::before { content: ''; position: absolute; width: 16px; height: 16px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.amp-toggle input:checked + .amp-toggle-slider { background: var(--amp-primary); }
.amp-toggle input:checked + .amp-toggle-slider::before { transform: translateX(18px); }

/* ── Inputs ─────────────────────────────────────────────────── */
.amp-input, .amp-select {
    background: var(--amp-card2);
    border: 1.5px solid var(--amp-border);
    border-radius: 8px;
    color: var(--amp-text);
    padding: 8px 12px;
    font-size: 13px;
    font-family: var(--amp-sans);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.amp-input:focus, .amp-select:focus {
    border-color: var(--amp-primary);
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}
.amp-input[type="number"] { max-width: 90px; font-family: 'JetBrains Mono', monospace; text-align: center; }
.amp-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23646970' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }

/* ── Section Label ──────────────────────────────────────────── */
.amp-set-section-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--amp-muted);
    padding: 16px 0 8px;
    display: block;
}
.amp-set-section-label:first-child { padding-top: 0; }

/* ── Right Sidebar Cards ────────────────────────────────────── */
.amp-sys-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
.amp-sys-table th { padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--amp-muted); background: var(--amp-card2); border-bottom: 1px solid var(--amp-border); }
.amp-sys-table td { padding: 8px 12px; border-bottom: 1px solid var(--amp-border); color: var(--amp-dim); vertical-align: middle; }
.amp-sys-table tr:last-child td { border-bottom: none; }
.amp-sys-table td code { font-size: 11px; }
.amp-sys-badge { display: inline-block; padding: 3px 9px; border-radius: 5px; font-size: 11px; font-weight: 700; }
.amp-sys-ok { background: rgba(0, 163, 42, 0.1); color: var(--amp-green); border: 1px solid rgba(0, 163, 42, 0.3); }
.amp-sys-warn { background: rgba(219, 166, 23, 0.1); color: var(--amp-yellow); border: 1px solid rgba(219, 166, 23, 0.3); }
.amp-sys-err { background: rgba(214, 54, 56, 0.1); color: var(--amp-red); border: 1px solid rgba(214, 54, 56, 0.3); }
.amp-sys-info { background: rgba(34, 113, 177, 0.1); color: var(--amp-primary); border: 1px solid rgba(34, 113, 177, 0.3); }

/* ── Cron Health ────────────────────────────────────────────── */
.amp-cron-ok { border-color: rgba(0, 163, 42, 0.3); }
.amp-cron-stale { border-color: rgba(245, 158, 11, 0.4); }
.amp-cron-alert { background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 12px 14px; font-size: 13px; color: var(--amp-dim); margin-top: 12px; }
.amp-cron-alert strong { color: var(--amp-yellow); }

/* ── Blocked IPs ────────────────────────────────────────────── */
.amp-block-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; border: 1px solid; font-size: 12px; font-family: 'JetBrains Mono', monospace; margin: 4px 4px 4px 0; }
.amp-block-chip.ip { background: rgba(214, 54, 56, 0.1); border-color: rgba(214, 54, 56, 0.3); color: var(--amp-red); }
.amp-block-chip .amp-unblock { background: none; border: none; cursor: pointer; font-size: 12px; padding: 0; color: var(--amp-red); opacity: 0.7; transition: opacity 0.2s; }
.amp-block-chip .amp-unblock:hover { opacity: 1; }

/* ── Save Button ────────────────────────────────────────────── */
.amp-save-row { display: flex; align-items: center; gap: 12px; padding: 18px 20px; background: var(--amp-card2); border-top: 1px solid var(--amp-border); }
.amp-btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 8px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    border: none; background: var(--amp-primary); color: #fff;
    transition: all 0.2s; letter-spacing: 0.02em;
}
.amp-btn-primary:hover { background: var(--amp-accent); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34, 113, 177, 0.3); }
.amp-save-status { font-size: 13px; font-weight: 600; }

/* ── Connection Test ────────────────────────────────────────── */
.amp-conn-row { display: flex; gap: 8px; margin-bottom: 10px; }
.amp-conn-row .amp-input { flex: 1; }
.amp-btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    border: 1.5px solid var(--amp-border); background: var(--amp-card2); color: var(--amp-dim);
    transition: all 0.2s;
}
.amp-btn-secondary:hover { border-color: var(--amp-primary); color: var(--amp-primary); }
.amp-conn-result { display: none; margin-top: 10px; }

/* ── Import/Export ──────────────────────────────────────────── */
.amp-imp-exp { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
.amp-btn-ghost {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    border: 1.5px solid var(--amp-border); background: transparent; color: var(--amp-dim);
    transition: all 0.2s;
}
.amp-btn-ghost:hover { border-color: var(--amp-primary); color: var(--amp-primary); background: rgba(34, 113, 177, 0.05); }

/* ── Alerts ────────────────────────────────────────────────── */
.amp-alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin: 10px 0; border-left: 4px solid; display: flex; align-items: flex-start; gap: 10px; }
.amp-alert-info { background: rgba(34, 113, 177, 0.08); border-left-color: var(--amp-primary); color: var(--amp-dim); }
.amp-alert-warn { background: rgba(219, 166, 23, 0.08); border-left-color: var(--amp-yellow); color: var(--amp-dim); }
.amp-alert-error { background: rgba(214, 54, 56, 0.08); border-left-color: var(--amp-red); color: var(--amp-dim); }

/* ── Empty State ────────────────────────────────────────────── */
.amp-empty { text-align: center; padding: 24px 20px; color: var(--amp-muted); font-size: 13px; }
.amp-empty strong { display: block; font-size: 14px; color: var(--amp-dim); margin-bottom: 4px; }
</style>

<div class="amp-wrap" id="autonode-settings">

<!-- Hero Banner -->
<div class="amp-set-hero">
    <div class="amp-set-hero-left">
        <h1>
            <img src="<?php echo esc_url( AUTONODE_URL . 'assets/logo.png' ); ?>" alt="AutoNode" style="width:28px;height:28px;border-radius:6px;vertical-align:middle;">
            <?php esc_html_e( 'Settings', 'autonode-pro'); ?>
        </h1>
        <p><?php esc_html_e( 'Configure security, rate limiting, webhooks, and plugin behavior.', 'autonode-pro'); ?></p>
    </div>
    <div class="amp-set-hero-right">
        <div class="amp-set-stat">
            <span class="amp-set-stat-val">v<?php echo esc_html( AUTONODE_VERSION ); ?></span>
            <span class="amp-set-stat-label"><?php esc_html_e( 'Version', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-set-stat">
            <span class="amp-set-stat-val"><?php echo (int) get_option( 'autonode_db_version' ); ?></span>
            <span class="amp-set-stat-label"><?php esc_html_e( 'DB Schema', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-set-stat">
            <span class="amp-set-stat-val"><?php echo esc_html( $autonode_block_count ); ?></span>
            <span class="amp-set-stat-label"><?php esc_html_e( 'Blocked', 'autonode-pro'); ?></span>
        </div>
    </div>
</div>

<div class="amp-set-layout">

<!-- Left: Settings Forms -->
<div>

    <form id="amp-settings-form">

        <!-- Security -->
        <div class="amp-set-card">
            <div class="amp-set-card-header">
                <span class="amp-set-icon">🔒</span>
                <h3><?php esc_html_e( 'Security', 'autonode-pro'); ?></h3>
            </div>
            <div class="amp-set-card-body">

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Require HTTPS', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Reject all API requests over plain HTTP.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <label class="amp-toggle">
                            <input type="checkbox" name="require_https" <?php checked( ! empty( $autonode_s['require_https'] ) ); ?>>
                            <div class="amp-toggle-slider"></div>
                        </label>
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Brute-Force Limit', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Failed auth attempts per IP before blocking.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="brute_force_limit" value="<?php echo (int) ( $autonode_s['brute_force_limit'] ?? 10 ); ?>" min="3" max="100" class="amp-input">
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Brute-Force Window', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Seconds before failure counter resets. Default: 300 (5 min).', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="brute_force_window" value="<?php echo (int) ( $autonode_s['brute_force_window'] ?? 300 ); ?>" min="60" max="3600" class="amp-input">
                    </div>
                </div>

            </div>
        </div>

        <!-- Rate Limiting -->
        <div class="amp-set-card">
            <div class="amp-set-card-header">
                <span class="amp-set-icon">⚡</span>
                <h3><?php esc_html_e( 'Rate Limiting', 'autonode-pro'); ?></h3>
            </div>
            <div class="amp-set-card-body">

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Max Requests / Window', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Per API key. n8n workflows should stay under the limit.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="rate_limit" value="<?php echo (int) ( $autonode_s['rate_limit'] ?? 120 ); ?>" min="1" max="10000" class="amp-input">
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Window Size', 'autonode-pro'); ?> <small>(<?php esc_html_e( 'seconds', 'autonode-pro'); ?>)</small></strong>
                        <p><?php esc_html_e( 'Time window for rate limit counter. Default: 60s.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="rate_window_sec" value="<?php echo (int) ( $autonode_s['rate_window_sec'] ?? 60 ); ?>" min="10" max="3600" class="amp-input">
                    </div>
                </div>

            </div>
        </div>

        <!-- Webhooks -->
        <div class="amp-set-card">
            <div class="amp-set-card-header">
                <span class="amp-set-icon">🪝</span>
                <h3><?php esc_html_e( 'Webhooks', 'autonode-pro'); ?></h3>
            </div>
            <div class="amp-set-card-body">

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Enable Webhooks', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Allow webhook registration and event dispatching.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <label class="amp-toggle">
                            <input type="checkbox" name="enable_webhooks" <?php checked( ! empty( $autonode_s['enable_webhooks'] ) ); ?>>
                            <div class="amp-toggle-slider"></div>
                        </label>
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Timeout', 'autonode-pro'); ?> <small>(<?php esc_html_e( 'ms', 'autonode-pro'); ?>)</small></strong>
                        <p><?php esc_html_e( 'HTTP timeout per delivery attempt. Default: 5000ms.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="webhook_timeout_ms" value="<?php echo (int) ( $autonode_s['webhook_timeout_ms'] ?? 5000 ); ?>" min="1000" max="30000" step="500" class="amp-input">
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Max Retry Attempts', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Retries on failure with escalating delays: 1min → 5min → 15min.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="max_retry_attempts" value="<?php echo (int) ( $autonode_s['max_retry_attempts'] ?? 3 ); ?>" min="0" max="5" class="amp-input">
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Flatten n8n Payloads', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Simplify JSON structure for n8n to avoid complex expressions.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <label class="amp-toggle">
                            <input type="checkbox" name="flatten_n8n_webhooks" <?php checked( ! empty( $autonode_s['flatten_n8n_webhooks'] ) ); ?>>
                            <div class="amp-toggle-slider"></div>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <!-- Appearance -->
        <div class="amp-set-card">
            <div class="amp-set-card-header">
                <span class="amp-set-icon">🎨</span>
                <h3><?php esc_html_e( 'Appearance', 'autonode-pro'); ?></h3>
            </div>
            <div class="amp-set-card-body">

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'UI Mode', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Standard WordPress look or Agentic Dark mode.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <select name="ui_mode" class="amp-select" style="max-width:180px">
                            <option value="standard" <?php selected( $autonode_s['ui_mode'] ?? 'standard', 'standard' ); ?>><?php esc_html_e( 'Standard', 'autonode-pro'); ?></option>
                            <option value="dark" <?php selected( $autonode_s['ui_mode'] ?? 'standard', 'dark' ); ?>><?php esc_html_e( 'Agentic Dark', 'autonode-pro'); ?></option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <!-- Access Control -->
        <div class="amp-set-card">
            <div class="amp-set-card-header">
                <span class="amp-set-icon">👥</span>
                <h3><?php esc_html_e( 'Access Control', 'autonode-pro'); ?></h3>
            </div>
            <div class="amp-set-card-body">

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Menu Access Capability', 'autonode-pro'); ?></strong>
                        <p><?php esc_html_e( 'Minimum capability to access AutoNode menu and dashboard.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <select name="min_capability" class="amp-select" style="max-width:200px">
                            <option value="manage_options" <?php selected( $autonode_s['min_capability'] ?? 'manage_options', 'manage_options' ); ?>><?php esc_html_e( 'Administrator', 'autonode-pro'); ?></option>
                            <option value="edit_others_posts" <?php selected( $autonode_s['min_capability'] ?? 'manage_options', 'edit_others_posts' ); ?>><?php esc_html_e( 'Editor', 'autonode-pro'); ?></option>
                            <option value="publish_posts" <?php selected( $autonode_s['min_capability'] ?? 'manage_options', 'publish_posts' ); ?>><?php esc_html_e( 'Author', 'autonode-pro'); ?></option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <!-- Media & Storage -->
        <div class="amp-set-card">
            <div class="amp-set-card-header">
                <span class="amp-set-icon">💾</span>
                <h3><?php esc_html_e( 'Media & Storage', 'autonode-pro'); ?></h3>
            </div>
            <div class="amp-set-card-body">

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Key Rotation', 'autonode-pro'); ?> <small>(<?php esc_html_e( 'days, 0 = off', 'autonode-pro'); ?>)</small></strong>
                        <p><?php esc_html_e( 'Force API keys to expire after X days. 0 to disable.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="key_rotation_days" value="<?php echo (int) ( $autonode_s['key_rotation_days'] ?? 0 ); ?>" min="0" max="365" class="amp-input">
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Max Sideload Size', 'autonode-pro'); ?> <small>(MB)</small></strong>
                        <p><?php esc_html_e( 'Maximum file size for URL sideloading and base64 uploads.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="max_sideload_size" value="<?php echo (int) ( $autonode_s['max_sideload_size'] ?? 20 ); ?>" min="1" max="500" class="amp-input">
                    </div>
                </div>

                <div class="amp-set-row">
                    <div class="amp-set-label">
                        <strong><?php esc_html_e( 'Log Retention', 'autonode-pro'); ?> <small>(<?php esc_html_e( 'days', 'autonode-pro'); ?>)</small></strong>
                        <p><?php esc_html_e( 'Activity log and analytics pruned daily. Min: 7 days.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-set-control">
                        <input type="number" name="log_retention_days" value="<?php echo (int) ( $autonode_s['log_retention_days'] ?? 90 ); ?>" min="7" max="365" class="amp-input">
                    </div>
                </div>

            </div>
        </div>

        <!-- Save -->
        <div class="amp-set-card">
            <div class="amp-save-row">
                <button type="submit" class="amp-btn-primary">💾 <?php esc_html_e( 'Save Settings', 'autonode-pro'); ?></button>
                <span id="amp-save-status" class="amp-save-status"></span>
            </div>
        </div>

    </form>

    <!-- Import/Export -->
    <div class="amp-set-card">
        <div class="amp-set-card-header">
            <span class="amp-set-icon">📦</span>
            <h3><?php esc_html_e( 'Configuration', 'autonode-pro'); ?></h3>
        </div>
        <div class="amp-set-card-body">
            <p style="font-size:13px;color:var(--amp-muted);margin:0 0 12px"><?php esc_html_e( 'Export your settings to a JSON file for backup, or import on another site.', 'autonode-pro'); ?></p>
            <div class="amp-imp-exp">
                <button id="amp-export-settings" class="amp-btn-secondary">📤 <?php esc_html_e( 'Export Settings', 'autonode-pro'); ?></button>
                <label for="amp-import-settings-file" class="amp-btn-ghost" style="cursor:pointer">📥 <?php esc_html_e( 'Import Settings', 'autonode-pro'); ?></label>
                <input type="file" id="amp-import-settings-file" accept=".json" style="display:none">
            </div>
            <div id="amp-import-status" style="margin-top:10px;font-size:13px;display:none"></div>
        </div>
    </div>

</div>

<!-- Right: System Info -->
<div>

    <!-- System Info -->
    <div class="amp-set-card">
        <div class="amp-set-card-header">
            <span class="amp-set-icon">🖥️</span>
            <h3><?php esc_html_e( 'System Info', 'autonode-pro'); ?></h3>
        </div>
        <div class="amp-set-card-body" style="padding:0">
            <table class="amp-sys-table">
                <tbody>
                    <?php
                    $autonode_sys_rows = array(
                        array( __( 'Plugin', 'autonode-pro'), esc_html( AUTONODE_VERSION ) ),
                        array( __( 'DB Schema', 'autonode-pro'), 'v' . (int) get_option( 'autonode_db_version' ) ),
                        array( __( 'WordPress', 'autonode-pro'), esc_html( get_bloginfo( 'version' ) ) ),
                        array( __( 'PHP', 'autonode-pro'), esc_html( PHP_VERSION ) ),
                        array( __( 'SEO Plugin', 'autonode-pro'), ucfirst( $autonode_sp ), 'none' !== $autonode_sp ? 'amp-sys-ok' : 'amp-sys-err' ),
                        array( __( 'Theme', 'autonode-pro'), esc_html( $compat['theme_name'] ) . ( $compat['is_agentic'] ? ' <span class="amp-sys-badge amp-sys-ok">' . esc_html__( 'Compat', 'autonode-pro') . '</span>' : '' ) ),
                        array( __( 'HTTPS', 'autonode-pro'), is_ssl() ? '<span class="amp-sys-badge amp-sys-ok">' . esc_html__( 'Yes', 'autonode-pro') . '</span>' : '<span class="amp-sys-badge amp-sys-err">' . esc_html__( 'No', 'autonode-pro') . '</span>' ),
                    );
                    foreach ( $autonode_sys_rows as list( $autonode_label, $autonode_value, $autonode_class ) ) :
                        if ( ! isset( $autonode_class ) ) $autonode_class = '';
                    ?>
                    <tr>
                        <th><?php echo esc_html( $autonode_label ); ?></th>
                        <td><?php echo wp_kses_post( $autonode_value ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th><?php esc_html_e( 'API Base', 'autonode-pro'); ?></th>
                        <td><code style="font-size:10px;word-break:break-all"><?php echo esc_html( get_site_url() . '/wp-json/' . AUTONODE_NS ); ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cron Health -->
    <div class="amp-set-card <?php echo 'stale' === $autonode_cron['status'] ? 'amp-cron-stale' : 'amp-cron-ok'; ?>">
        <div class="amp-set-card-header">
            <span class="amp-set-icon">⏰</span>
            <h3><?php esc_html_e( 'Cron Health', 'autonode-pro'); ?></h3>
            <?php if ( 'ok' === $autonode_cron['status'] || 'real_cron' === $autonode_cron['status'] ) : ?>
                <span class="amp-sys-badge amp-sys-ok"><?php echo 'real_cron' === $autonode_cron['status'] ? esc_html__( 'Server Cron', 'autonode-pro') : esc_html__( 'Healthy', 'autonode-pro'); ?></span>
            <?php else : ?>
                <span class="amp-sys-badge amp-sys-warn"><?php esc_html_e( 'Stale', 'autonode-pro'); ?></span>
            <?php endif; ?>
        </div>
        <div class="amp-set-card-body">
    <p style="font-size:13px;color:var(--amp-muted);margin:0 0 12px"><?php echo esc_html( $autonode_cron['message'] ); ?></p>

            <?php if ( 'stale' === $autonode_cron['status'] ) : ?>
            <div class="amp-cron-alert">
                <strong><?php esc_html_e( 'Fix:', 'autonode-pro'); ?></strong> <?php esc_html_e( 'Add a real server cron to your hosting control panel:', 'autonode-pro'); ?><br>
                <code style="font-size:11px;margin-top:6px;display:block;font-family:'JetBrains Mono',monospace">*/5 * * * * php <?php echo esc_html( ABSPATH ); ?>wp-cron.php</code>
            </div>
            <?php endif; ?>

            <table class="amp-sys-table" style="margin-top:12px">
                <thead><tr>
                    <th><?php esc_html_e( 'Hook', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Next Run', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Status', 'autonode-pro'); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ( $autonode_cron['scheduled_hooks'] as $autonode_hook ) : ?>
                <tr>
                    <td><code><?php echo esc_html( $autonode_hook['hook'] ); ?></code></td>
                    <td><small><?php echo esc_html( $autonode_hook['next_run'] ); ?></small></td>
                    <td>
                        <?php if ( $autonode_hook['scheduled'] ) : ?>
                            <span class="amp-sys-badge amp-sys-ok">✓</span>
                        <?php else : ?>
                            <span class="amp-sys-badge amp-sys-err"><?php esc_html_e( 'Missing', 'autonode-pro'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Blocked IPs -->
    <div class="amp-set-card">
        <div class="amp-set-card-header">
            <span class="amp-set-icon">🚫</span>
            <h3><?php esc_html_e( 'Blocked IPs', 'autonode-pro'); ?></h3>
            <span class="amp-sys-badge amp-sys-err" style="margin-left:auto"><?php echo (int) $block_count; ?> <?php esc_html_e( 'active', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-set-card-body">
            <?php if ( empty( $autonode_blocks ) ) : ?>
                <div class="amp-empty">
                    <strong><?php esc_html_e( 'No blocked IPs', 'autonode-pro'); ?></strong>
                    <?php esc_html_e( 'Brute-force protection is active.', 'autonode-pro'); ?>
                </div>
            <?php else : ?>
                <div style="display:flex;flex-wrap:wrap;gap:0">
                    <?php foreach ( $autonode_blocks as $autonode_b ) : ?>
                    <div class="amp-block-chip ip">
                        <span><?php echo esc_html( $autonode_b['ip_address'] ); ?></span>
                        <small style="opacity:0.6;font-size:10px"><?php echo $autonode_b['blocked_until'] ? esc_html( human_time_diff( strtotime( $autonode_b['blocked_until'] ) ) . ' left' ) : ''; ?></small>
                        <button class="amp-unblock" data-ip="<?php echo esc_attr( $autonode_b['ip_address'] ); ?>">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Connection Test -->
    <div class="amp-set-card">
        <div class="amp-set-card-header">
            <span class="amp-set-icon">🔌</span>
            <h3><?php esc_html_e( 'Live Connection Test', 'autonode-pro'); ?></h3>
        </div>
        <div class="amp-set-card-body">
            <p style="font-size:12px;color:var(--amp-muted);margin:0 0 10px"><?php esc_html_e( 'Paste your API key to test it from the browser.', 'autonode-pro'); ?></p>
            <div class="amp-conn-row">
                <input type="password" id="amp-test-key" placeholder="ampcm_xxxxxxxx…" class="amp-input" style="flex:1">
                <button class="amp-btn-secondary" id="amp-test-conn-btn"><?php esc_html_e( 'Test', 'autonode-pro'); ?></button>
            </div>
            <pre id="amp-conn-result" class="amp-code-block" style="display:none;white-space:pre-wrap;font-size:11px;margin-top:10px"></pre>
        </div>
    </div>

</div>

</div>
</div>