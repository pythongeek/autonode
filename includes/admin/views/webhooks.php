<?php
/*  webhooks.php  */
defined( 'ABSPATH' ) || exit;
$autonode_active_count = count( array_filter( $autonode_webhooks, fn( $w ) => $w['active'] ) );
$autonode_total_fires  = array_sum( array_map( fn( $w ) => (int) $w['fire_count'], $autonode_webhooks ) );
$autonode_failed_fires = array_sum( array_map( fn( $w ) => (int) $w['fail_count'], $autonode_webhooks ) );
$autonode_wh_count     = count( $autonode_webhooks );
?>
<style>
/* ── Webhooks Page ──────────────────────────────────────────── */
#autonode-webhooks .amp-wrap { padding: 0 0 40px; }

.amp-wh-hero {
    background: linear-gradient(135deg, #4a1d96 0%, #6b31c4 50%, #4a1d96 100%);
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
.amp-wh-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.amp-wh-hero::after {
    content: '';
    position: absolute;
    bottom: -70px; right: 100px;
    width: 280px; height: 280px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}
.amp-wh-hero-left { position: relative; z-index: 1; }
.amp-wh-hero h1 {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.amp-wh-hero h1 img { border-radius: 6px; }
.amp-wh-hero p { margin: 0; font-size: 13px; color: rgba(255,255,255,0.6); }
.amp-wh-hero-right { display: flex; gap: 14px; position: relative; z-index: 1; flex-wrap: wrap; }
.amp-wh-stat {
    text-align: center;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 10px;
    padding: 13px 18px;
    min-width: 95px;
}
.amp-wh-stat-val { display: block; font-size: 26px; font-weight: 800; color: #fff; line-height: 1; font-family: 'JetBrains Mono', monospace; }
.amp-wh-stat-label { display: block; font-size: 11px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; margin-top: 4px; }

/* ── Layout ─────────────────────────────────────────────────── */
.amp-wh-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 24px;
    align-items: start;
}
@media(max-width: 1100px) { .amp-wh-layout { grid-template-columns: 1fr; } }

/* ── Card ───────────────────────────────────────────────────── */
.amp-wf-card {
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    overflow: hidden;
}
.amp-wf-section {
    padding: 22px 26px;
    border-bottom: 1px solid var(--amp-border);
}
.amp-wf-section:last-child { border-bottom: none; }
.amp-wf-section-header {
    display: flex; align-items: center; gap: 10px; margin-bottom: 18px;
}
.amp-wf-num {
    width: 28px; height: 28px;
    background: rgba(74, 31, 184, 0.1);
    color: #6b31c4;
    border: 1px solid rgba(74, 31, 184, 0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; flex-shrink: 0;
}
.amp-wf-section-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: var(--amp-text); }
.amp-wf-section-header p { margin: 4px 0 0 38px; font-size: 12px; color: var(--amp-muted); }

/* ── Fields ─────────────────────────────────────────────────── */
.amp-fields-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.amp-field-full { grid-column: 1 / -1; }
.amp-label { display: block; font-size: 12px; font-weight: 700; color: var(--amp-dim); margin-bottom: 6px; letter-spacing: 0.02em; }
.amp-label small { font-weight: 400; color: var(--amp-muted); display: block; font-size: 11px; margin-top: 2px; }
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
    border-color: #6b31c4;
    box-shadow: 0 0 0 3px rgba(74, 31, 184, 0.1);
    background: var(--amp-card);
}
.amp-input::placeholder { color: var(--amp-muted); }
.amp-textarea { min-height: 72px; resize: vertical; font-family: var(--amp-mono); font-size: 12px; }
.amp-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23646970' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

/* ── Checkbox Grids ─────────────────────────────────────────── */
.amp-ev-grid {
    display: flex; flex-wrap: wrap; gap: 8px;
    background: var(--amp-card2);
    border: 1.5px solid var(--amp-border);
    border-radius: 10px;
    padding: 14px;
}
.amp-ev-item {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; cursor: pointer; color: var(--amp-dim);
    font-family: var(--amp-mono);
    padding: 5px 10px;
    border-radius: 6px;
    border: 1.5px solid transparent;
    transition: all 0.15s;
}
.amp-ev-item:hover { background: rgba(74, 31, 184, 0.06); color: var(--amp-text); border-color: rgba(74, 31, 184, 0.2); }
.amp-ev-item input { accent-color: #6b31c4; width: 14px; height: 14px; cursor: pointer; flex-shrink: 0; }
.amp-ev-item input:checked + code { color: #6b31c4; font-weight: 700; }
.amp-ev-item code { font-size: 11px; }

/* ── Submit ─────────────────────────────────────────────────── */
.amp-wf-actions {
    padding: 18px 26px;
    background: var(--amp-card2);
    border-top: 1px solid var(--amp-border);
    display: flex; align-items: center; gap: 12px;
}
.amp-btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 24px; border-radius: 8px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    border: none; background: #6b31c4; color: #fff;
    transition: all 0.2s; letter-spacing: 0.02em;
}
.amp-btn-primary:hover { background: #5417b3; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(74, 31, 184, 0.3); }
.amp-btn-primary:active { transform: translateY(0); }

/* ── Ref Sidebar ────────────────────────────────────────────── */
.amp-ref-card {
    background: var(--amp-card);
    border: 1px solid var(--amp-border);
    border-radius: var(--amp-radius);
    overflow: hidden;
}
.amp-ref-card-header {
    padding: 14px 18px;
    background: var(--amp-card2);
    border-bottom: 1px solid var(--amp-border);
    display: flex; align-items: center; gap: 8px;
}
.amp-ref-card-header h3 { margin: 0; font-size: 14px; font-weight: 700; color: var(--amp-text); }
.amp-ref-card-header span { font-size: 16px; }
.amp-ref-card-body { padding: 18px; }
.amp-ref-block { margin-bottom: 18px; }
.amp-ref-block:last-child { margin-bottom: 0; }
.amp-ref-block-title {
    font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--amp-muted); margin-bottom: 8px;
}
.amp-ref-code {
    background: var(--amp-card2);
    border: 1px solid var(--amp-border);
    border-radius: 6px; padding: 10px 14px;
    font-family: var(--amp-mono); font-size: 12px;
    color: #6b31c4; word-break: break-all; line-height: 1.6;
}
.amp-ref-divider { border: none; border-top: 1px solid var(--amp-border); margin: 14px 0; }
.amp-ref-tag {
    display: inline-block;
    background: rgba(74, 31, 184, 0.08);
    color: #6b31c4;
    border: 1px solid rgba(74, 31, 184, 0.2);
    padding: 3px 9px; border-radius: 5px;
    font-size: 11px; font-family: var(--amp-mono); font-weight: 600;
}
.amp-ref-tags { display: flex; flex-wrap: wrap; gap: 5px; }

/* ── Info Banner ────────────────────────────────────────────── */
.amp-info-banner {
    background: rgba(74, 31, 184, 0.06);
    border: 1px solid rgba(74, 31, 184, 0.2);
    border-radius: 8px; padding: 11px 16px;
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 13px; color: var(--amp-dim); margin-bottom: 18px;
}
.amp-info-banner strong { color: #6b31c4; }
.amp-info-icon { font-size: 15px; flex-shrink: 0; }

/* ── Table Section ──────────────────────────────────────────── */
.amp-table-section { margin-top: 28px; }
.amp-table-section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; flex-wrap: wrap; gap: 12px;
}
.amp-table-section-header h2 {
    margin: 0; font-size: 18px; font-weight: 800; color: var(--amp-text);
    display: flex; align-items: center; gap: 8px;
}
.amp-table-controls { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.amp-search-input {
    padding: 7px 14px;
    border: 1.5px solid var(--amp-border);
    border-radius: 8px; font-size: 13px;
    background: var(--amp-card); color: var(--amp-text);
    outline: none; min-width: 200px; transition: border-color 0.2s;
}
.amp-search-input:focus { border-color: #6b31c4; box-shadow: 0 0 0 3px rgba(74, 31, 184, 0.1); }

/* ── Toggle ─────────────────────────────────────────────────── */
.amp-toggle-wrap { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--amp-dim); cursor: pointer; white-space: nowrap; }
.amp-toggle { position: relative; display: inline-block; width: 36px; height: 20px; flex-shrink: 0; }
.amp-toggle input { opacity: 0; width: 0; height: 0; }
.amp-toggle-slider { position: absolute; cursor: pointer; inset: 0; background: var(--amp-border2); border-radius: 20px; transition: 0.3s; }
.amp-toggle-slider::before { content: ''; position: absolute; width: 14px; height: 14px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.amp-toggle input:checked + .amp-toggle-slider { background: #6b31c4; }
.amp-toggle input:checked + .amp-toggle-slider::before { transform: translateX(16px); }

/* ── Actions ────────────────────────────────────────────────── */
.amp-copy-btn-sm {
    padding: 5px 12px; border-radius: 6px;
    border: 1px solid var(--amp-border);
    background: var(--amp-card); color: var(--amp-dim);
    font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; flex-shrink: 0;
}
.amp-copy-btn-sm:hover { border-color: #6b31c4; color: #6b31c4; }
.amp-copy-btn-sm.danger { color: var(--amp-red); border-color: rgba(214,54,56,0.3); }
.amp-copy-btn-sm.danger:hover { background: rgba(214,54,56,0.08); border-color: var(--amp-red); }

/* ── Modal ──────────────────────────────────────────────────── */
.amp-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100000; display: flex; align-items: center; justify-content: center; }
.amp-modal-box { background: var(--amp-card); border: 1px solid var(--amp-border2); border-radius: var(--amp-radius); padding: 28px; max-width: 620px; width: 95%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.amp-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--amp-border); padding-bottom: 14px; }
.amp-modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--amp-text); display: flex; align-items: center; gap: 8px; }
.amp-modal-close { background: var(--amp-card2); border: 1px solid var(--amp-border); color: var(--amp-muted); width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; cursor: pointer; transition: 0.2s; }
.amp-modal-close:hover { background: rgba(214,54,56,0.1); color: var(--amp-red); border-color: rgba(214,54,56,0.3); }
.amp-code-block { background: var(--amp-card2); border: 1px solid var(--amp-border2); border-radius: 8px; padding: 18px; font-family: var(--amp-mono); font-size: 13px; line-height: 1.6; color: var(--amp-text); overflow-x: auto; white-space: pre-wrap; margin: 0; }
</style>

<div class="amp-wrap" id="autonode-webhooks">

<!-- Hero Banner -->
<div class="amp-wh-hero">
    <div class="amp-wh-hero-left">
        <h1>
            <img src="<?php echo esc_url( AUTONODE_URL . 'assets/logo.png' ); ?>" alt="AutoNode" style="width:28px;height:28px;border-radius:6px;vertical-align:middle;">
            <?php esc_html_e( 'Webhooks', 'autonode-pro'); ?>
        </h1>
        <p><?php esc_html_e( 'Real-time event notifications with HMAC signing and retry logic.', 'autonode-pro'); ?></p>
    </div>
    <div class="amp-wh-hero-right">
        <div class="amp-wh-stat">
            <span class="amp-wh-stat-val"><?php echo esc_html( $autonode_active_count ); ?></span>
            <span class="amp-wh-stat-label"><?php esc_html_e( 'Active', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-wh-stat">
            <span class="amp-wh-stat-val"><?php echo esc_html( number_format( $autonode_total_fires ) ); ?></span>
            <span class="amp-wh-stat-label"><?php esc_html_e( 'Fires', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-wh-stat">
            <span class="amp-wh-stat-val"><?php echo esc_html( number_format( $autonode_failed_fires ) ); ?></span>
            <span class="amp-wh-stat-label"><?php esc_html_e( 'Failed', 'autonode-pro'); ?></span>
        </div>
        <div class="amp-wh-stat">
            <span class="amp-wh-stat-val"><?php echo esc_html( $autonode_wh_count ); ?></span>
            <span class="amp-wh-stat-label"><?php esc_html_e( 'Total', 'autonode-pro'); ?></span>
        </div>
    </div>
</div>

<!-- Main Layout -->
<div class="amp-wh-layout">

    <!-- Left: Register Form -->
    <div class="amp-wf-card">
        <form id="amp-create-webhook-form">

            <!-- Section 1: Target -->
            <div class="amp-wf-section">
                <div class="amp-wf-section-header">
                    <div class="amp-wf-num">1</div>
                    <div>
                        <h3><?php esc_html_e( 'Endpoint Configuration', 'autonode-pro'); ?></h3>
                        <p><?php esc_html_e( 'Where should we send the webhook payloads?', 'autonode-pro'); ?></p>
                    </div>
                </div>
                <div class="amp-fields-row">
                    <div>
                        <label class="amp-label"><?php esc_html_e( 'Label', 'autonode-pro'); ?> <span style="color:var(--amp-red)">*</span></label>
                        <input type="text" name="label" class="amp-input" placeholder="<?php esc_attr_e( 'e.g. n8n Post Published', 'autonode-pro'); ?>" required>
                    </div>
                    <div>
                        <label class="amp-label"><?php esc_html_e( 'Target URL', 'autonode-pro'); ?> <span style="color:var(--amp-red)">*</span></label>
                        <input type="url" name="target_url" class="amp-input" placeholder="https://your-n8n.com/webhook/" required>
                    </div>
                    <div class="amp-field-full">
                        <label class="amp-label"><?php esc_html_e( 'Signing Secret', 'autonode-pro'); ?> <small><?php esc_html_e( 'optional — used for X-autonode-Signature header', 'autonode-pro'); ?></small></label>
                        <input type="password" name="secret" class="amp-input" placeholder="<?php esc_attr_e( 'Leave blank for no signature verification', 'autonode-pro'); ?>">
                    </div>
                </div>
            </div>

            <!-- Section 2: Events -->
            <div class="amp-wf-section">
                <div class="amp-wf-section-header">
                    <div class="amp-wf-num">2</div>
                    <div>
                        <h3><?php esc_html_e( 'Events & Post Types', 'autonode-pro'); ?></h3>
                        <p><?php esc_html_e( 'Choose which events trigger this webhook.', 'autonode-pro'); ?></p>
                    </div>
                </div>

                <div style="margin-bottom:16px">
                    <label class="amp-label" style="margin-bottom:10px"><?php esc_html_e( 'Events', 'autonode-pro'); ?></label>
                    <div class="amp-ev-grid">
                        <?php foreach ( \AutoNode\Webhook_Manager::EVENTS as $autonode_ev ) : ?>
                        <label class="amp-ev-item">
                            <input type="checkbox" name="events[]" value="<?php echo esc_attr( $autonode_ev ); ?>" <?php echo in_array( $autonode_ev, array( 'post.published', 'page.published' ), true ) ? 'checked' : ''; ?>>
                            <code><?php echo esc_html( $autonode_ev ); ?></code>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="amp-label" style="margin-bottom:10px"><?php esc_html_e( 'Post Types', 'autonode-pro'); ?></label>
                    <div class="amp-ev-grid">
                        <label class="amp-ev-item"><input type="checkbox" name="post_types[]" value="post" checked> <code>post</code> <?php esc_html_e( 'Posts', 'autonode-pro'); ?></label>
                        <label class="amp-ev-item"><input type="checkbox" name="post_types[]" value="page" checked> <code>page</code> <?php esc_html_e( 'Pages', 'autonode-pro'); ?></label>
                    </div>
                </div>
            </div>

            <!-- Section 3: Cron Info -->
            <div class="amp-wf-section">
                <div class="amp-wf-section-header">
                    <div class="amp-wf-num">!</div>
                    <div>
                        <h3><?php esc_html_e( 'Delivery Mode', 'autonode-pro'); ?></h3>
                    </div>
                </div>
                <div class="amp-info-banner">
                    <span class="amp-info-icon">ℹ️</span>
                    <div>
                        <?php
                            printf(
                                /* translators: %s: cron command example */
                                esc_html__( 'Webhooks fire via WP-Cron by default. For instant delivery add a real server cron job: %s', 'autonode-pro'),
                                '<br><code style="font-size:12px;margin-top:4px;display:block">*/1 * * * * php /path/to/public_html/wp-cron.php</code>'
                            );
                        ?>
                    </div>
                </div>
            </div>

            <div class="amp-wf-actions">
                <button type="submit" class="amp-btn-primary" id="amp-create-wh-btn">
                    🪝 <?php esc_html_e( 'Register Webhook', 'autonode-pro'); ?>
                </button>
                <span style="font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Payloads include HMAC signature for verification.', 'autonode-pro'); ?></span>
            </div>
        </form>
    </div>

    <!-- Right: Event Reference -->
    <div class="amp-ref-card">
        <div class="amp-ref-card-header">
            <span>📋</span>
            <h3><?php esc_html_e( 'Event Reference', 'autonode-pro'); ?></h3>
        </div>
        <div class="amp-ref-card-body">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'HMAC Signature', 'autonode-pro'); ?></div>
                <div class="amp-ref-code">X-autonode-Signature:<br>sha256=&lt;hmac&gt;</div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Verification (PHP)', 'autonode-pro'); ?></div>
                <div class="amp-ref-code" style="font-size:11px">hash_hmac('sha256', $payload, $secret)</div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Verification (JS)', 'autonode-pro'); ?></div>
                <div class="amp-ref-code" style="font-size:11px">crypto.createHmac('sha256', secret)<br>  .update(payload)<br>  .digest('hex')</div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Payload Format', 'autonode-pro'); ?></div>
                <div class="amp-ref-code" style="font-size:10px">{
  "event": "post.published",
  "timestamp": "2026-05-11T12:00:00Z",
  "data": { "post_id": 123, ... }
}</div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Available Events', 'autonode-pro'); ?></div>
                <div class="amp-ref-tags">
                    <?php foreach ( \AutoNode\Webhook_Manager::EVENTS as $autonode_ev ) : ?>
                    <span class="amp-ref-tag"><?php echo esc_html( $autonode_ev ); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr class="amp-ref-divider">

            <div class="amp-ref-block">
                <div class="amp-ref-block-title"><?php esc_html_e( 'Delivery Behavior', 'autonode-pro'); ?></div>
                <div style="font-size:12px;color:var(--amp-muted);line-height:1.7">
                    • 3 automatic retries on failure<br>
                    • Logs every delivery attempt<br>
                    • HTTP status visible in table
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Registered Webhooks Table -->
<div class="amp-table-section">
    <div class="amp-table-section-header">
        <h2>🪝 <?php esc_html_e( 'Registered Webhooks', 'autonode-pro'); ?></h2>
        <div class="amp-table-controls">
            <input type="text" id="amp-wh-search" class="amp-search-input" placeholder="<?php esc_attr_e( 'Search by label or URL…', 'autonode-pro'); ?>">
        </div>
    </div>

    <div class="amp-wf-card">
        <?php if ( empty( $autonode_webhooks ) ) : ?>
            <div style="padding:48px 20px;text-align:center">
                <div style="font-size:40px;margin-bottom:12px">🪝</div>
                <div style="font-size:15px;font-weight:700;color:var(--amp-text);margin-bottom:6px"><?php esc_html_e( 'No webhooks yet', 'autonode-pro'); ?></div>
                <div style="font-size:13px;color:var(--amp-muted)"><?php esc_html_e( 'Register one above to start receiving event notifications.', 'autonode-pro'); ?></div>
            </div>
        <?php else : ?>
        <table class="amp-table widefat wp-list-table" style="margin:0">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Label', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Target URL', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Events', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Status', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Fires', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Last Fire', 'autonode-pro'); ?></th>
                    <th><?php esc_html_e( 'Actions', 'autonode-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $autonode_webhooks as $autonode_wh ) :
                $autonode_events = json_decode( $autonode_wh['events'], true ) ?: array();
            ?>
            <tr data-id="<?php echo (int) $autonode_wh['id']; ?>"
                data-label="<?php echo esc_attr( strtolower( $autonode_wh['label'] . ' ' . $autonode_wh['target_url'] ) ); ?>">

                <td>
                    <div style="display:flex;align-items:center;gap:6px">
                        <strong style="font-size:13px"><?php echo esc_html( $autonode_wh['label'] ); ?></strong>
                        <span id="amp-wh-url-<?php echo (int) $autonode_wh['id']; ?>" style="display:none"><?php echo esc_url( $autonode_wh['target_url'] ); ?></span>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <code style="font-size:11px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:var(--amp-mono);color:var(--amp-dim)" title="<?php echo esc_attr( $autonode_wh['target_url'] ); ?>">
                            <?php echo esc_html( substr( $autonode_wh['target_url'], 0, 45 ) ); ?>…
                        </code>
                        <button class="amp-copy-btn-sm amp-copy-btn" data-target="amp-wh-url-<?php echo (int) $autonode_wh['id']; ?>" title="<?php esc_attr_e( 'Copy URL', 'autonode-pro'); ?>">📋</button>
                    </div>
                </td>
                <td>
                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                        <?php
                            foreach ( array_slice( $autonode_events, 0, 4 ) as $autonode_ev ) {
                                printf( '<span style="font-size:10px;background:rgba(74,31,184,0.08);color:#6b31c4;border:1px solid rgba(74,31,184,0.2);padding:2px 6px;border-radius:4px;font-family:var(--amp-mono)">%s</span>', esc_html( $autonode_ev ) );
                            }
                            if ( count( $autonode_events ) > 4 ) {
                                echo '<span style="font-size:10px;color:var(--amp-muted)">+' . ( count( $autonode_events ) - 4 ) . '</span>';
                            }
                        ?>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <label class="amp-toggle">
                            <input type="checkbox" class="amp-wh-toggle" data-id="<?php echo (int) $autonode_wh['id']; ?>" <?php checked( (bool) $autonode_wh['active'] ); ?>>
                            <div class="amp-toggle-slider"></div>
                        </label>
                        <?php
                            if ( $autonode_wh['last_status'] && $autonode_wh['last_status'] >= 400 ) {
                                echo '<span style="font-size:10px;font-weight:700;padding:3px 7px;border-radius:5px;background:rgba(214,54,56,0.1);color:var(--amp-red);border:1px solid rgba(214,54,56,0.3)">HTTP ' . (int) $autonode_wh['last_status'] . '</span>';
                            } elseif ( $autonode_wh['last_status'] ) {
                                echo '<span style="font-size:10px;font-weight:700;padding:3px 7px;border-radius:5px;background:rgba(0,163,42,0.1);color:var(--amp-green);border:1px solid rgba(0,163,42,0.3)">HTTP ' . (int) $autonode_wh['last_status'] . '</span>';
                            }
                        ?>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="font-family:var(--amp-mono);font-size:13px;font-weight:700;color:var(--amp-green)"><?php echo number_format( (int) $autonode_wh['fire_count'] ); ?></span>
                        <?php if ( $autonode_wh['fail_count'] > 0 ) : ?>
                            <span style="font-size:11px;color:var(--amp-red)">/ <?php echo (int) $autonode_wh['fail_count']; ?> fail</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td><small style="font-size:11px;color:var(--amp-muted)"><?php echo $autonode_wh['last_fired_at'] ? esc_html( human_time_diff( strtotime( $autonode_wh['last_fired_at'] ) ) . ' ago' ) : '—'; ?></small></td>
                <td>
                    <div style="display:flex;gap:5px;flex-wrap:wrap">
                        <button class="amp-copy-btn-sm amp-view-log" data-id="<?php echo (int) $autonode_wh['id']; ?>" data-label="<?php echo esc_attr( $autonode_wh['label'] ); ?>">📋 <?php esc_html_e( 'Log', 'autonode-pro'); ?></button>
                        <button class="amp-copy-btn-sm amp-test-wh" data-id="<?php echo (int) $autonode_wh['id']; ?>">🧪 <?php esc_html_e( 'Test', 'autonode-pro'); ?></button>
                        <button class="amp-copy-btn-sm danger amp-del-wh" data-id="<?php echo (int) $autonode_wh['id']; ?>">✕ <?php esc_html_e( 'Del', 'autonode-pro'); ?></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Deliveries -->
<div class="amp-table-section">
    <div class="amp-table-section-header">
        <h2>📡 <?php esc_html_e( 'Recent Deliveries', 'autonode-pro'); ?></h2>
        <span class="amp-badge-pill" id="amp-delivery-count"></span>
    </div>
    <div class="amp-wf-card">
        <div id="amp-delivery-log" style="padding:24px">
            <div style="text-align:center;padding:32px 20px">
                <div style="font-size:36px;margin-bottom:10px">📡</div>
                <div style="font-size:14px;font-weight:600;color:var(--amp-text);margin-bottom:4px"><?php esc_html_e( 'No deliveries loaded', 'autonode-pro'); ?></div>
                <div style="font-size:13px;color:var(--amp-muted)"><?php esc_html_e( 'Click "Log" on a webhook above to view its delivery history.', 'autonode-pro'); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Test Modal -->
<div id="amp-test-modal" class="amp-modal" style="display:none">
    <div class="amp-modal-box">
        <div class="amp-modal-header">
            <h3>🧪 <?php esc_html_e( 'Webhook Test Result', 'autonode-pro'); ?></h3>
            <button class="amp-modal-close">✕</button>
        </div>
        <pre id="amp-test-result" class="amp-code-block"></pre>
    </div>
</div>

</div>