<?php
defined( 'ABSPATH' ) || exit;
$autonode_hourly_json = wp_json_encode( $autonode_hourly );
$autonode_hits_json   = wp_json_encode( array_map( fn( $r ) => (int) $r['hits'], $autonode_hourly ) );
$autonode_err_json    = wp_json_encode( array_map( fn( $r ) => (int) $r['errors'], $autonode_hourly ) );
$autonode_ms_json     = wp_json_encode( array_map( fn( $r ) => $r['total_ms'] && $r['hits'] ? round( $r['total_ms'] / $r['hits'], 1 ) : 0, $autonode_hourly ) );
?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title">
            <img src="<?php echo esc_url( AUTONODE_URL . 'assets/logo.png' ); ?>" alt="AutoNode" style="width:26px;height:26px;vertical-align:middle;margin-right:6px;border-radius:4px;">
            <?php esc_html_e( 'AutoNode WP', 'autonode-pro'); ?>
        </h1>
        <span class="amp-badge-pill">v<?php echo esc_html( AUTONODE_VERSION ); ?></span>
        <?php $autonode_sp = \AutoNode\Rankmath_Handler::active_plugin(); ?>
        <span class="amp-badge-pill amp-pill-<?php echo esc_attr( $autonode_sp ); ?>">
            <?php 
                if ( 'rankmath' === $autonode_sp ) {
                    esc_html_e( 'Rank Math', 'autonode-pro');
                } elseif ( 'yoast' === $autonode_sp ) {
                    esc_html_e( 'Yoast', 'autonode-pro');
                } else {
                    esc_html_e( 'No SEO Plugin', 'autonode-pro');
                }
            ?>
        </span>
        <?php if ( $autonode_compat['is_agentic'] ) : ?>
        <span class="amp-badge-pill amp-pill-ok"><?php esc_html_e( 'Agentic Pro', 'autonode-pro'); ?></span>
        <?php endif; ?>
    </div>
    <div class="amp-header-right">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-keys' ) ); ?>" class="amp-btn amp-btn-primary button button-primary">+ <?php esc_html_e( 'New API Key', 'autonode-pro'); ?></a>
    </div>
</div>

<div class="amp-card amp-hero-card">
    <div class="amp-card-body">
        <h2 style="margin-top:0; color:var(--amp-primary);"><?php esc_html_e( 'The Enterprise Bridge for n8n Automation', 'autonode-pro'); ?></h2>
        <p style="font-size:16px; color:var(--amp-dim);">
            <?php esc_html_e( 'AutoNode WP connects AI-powered workflows to WordPress. Manage posts, SEO fields, and media through a secure, scoped REST API.', 'autonode-pro'); ?>
        </p>
    </div>
</div>

<!-- Stats -->
<div class="amp-grid-stats">
    <?php
    $autonode_cards = array(
        array( __( 'Requests Today', 'autonode-pro'), number_format( $autonode_summary['requests_today'] ),  'amp-icon-blue', '<span class="dashicons dashicons-chart-bar"></span>' ),
        array( __( 'Requests (7d)', 'autonode-pro'),  number_format( $autonode_summary['requests_week'] ),   'amp-icon-purple', '<span class="dashicons dashicons-chart-line"></span>' ),
        array( __( 'Errors (24h)', 'autonode-pro'),   number_format( $autonode_summary['errors_24h'] ),      'amp-icon-red', '<span class="dashicons dashicons-warning"></span>' ),
        array( __( 'Avg Response', 'autonode-pro'),   esc_html( $autonode_summary['avg_response_ms'] ) . 'ms', 'amp-icon-green', '<span class="dashicons dashicons-clock"></span>' ),
        array( __( 'Active Keys', 'autonode-pro'),    esc_html( $autonode_active_keys ),                     'amp-icon-teal', '<span class="dashicons dashicons-admin-network"></span>' ),
        array( __( 'WordPress', 'autonode-pro'),      esc_html( get_bloginfo( 'version' ) ),        'amp-icon-orange', '<span class="dashicons dashicons-wordpress"></span>' ),
    );
    foreach ( $autonode_cards as list( $autonode_label, $autonode_val, $autonode_cls, $autonode_icon ) ) : ?>
    <div class="amp-stat-card">
        <div class="amp-stat-icon <?php echo esc_attr( $autonode_cls ); ?>"><?php echo wp_kses_post( $autonode_icon ); ?></div>
        <div class="amp-stat-body">
            <span class="amp-stat-value"><?php echo esc_html( $autonode_val ); ?></span>
            <span class="amp-stat-label"><?php echo esc_html( $autonode_label ); ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts -->
<div class="amp-grid-2">
    <div class="amp-card">
        <div class="amp-card-header">
            <h3><?php esc_html_e( 'Request Volume (24h)', 'autonode-pro'); ?></h3>
            <div class="amp-chart-btns">
                <button class="amp-chart-btn active" data-hours="24">24h</button>
                <button class="amp-chart-btn" data-hours="48">48h</button>
                <button class="amp-chart-btn" data-hours="168">7d</button>
            </div>
        </div>
        <div style="height:200px;position:relative"><canvas id="amp-req-chart"></canvas></div>
    </div>
    <div class="amp-card">
        <div class="amp-card-header"><h3><?php esc_html_e( 'Response Time (ms)', 'autonode-pro'); ?></h3></div>
        <div style="height:200px;position:relative"><canvas id="amp-ms-chart"></canvas></div>
    </div>
</div>

<!-- Bottom Row -->
<div class="amp-grid-2">
    <div class="amp-card">
        <div class="amp-card-header"><h3><?php esc_html_e( 'Top Endpoints (7d)', 'autonode-pro'); ?></h3></div>
        <?php if ( empty( $autonode_top_ep ) ) : ?>
            <p class="amp-empty"><?php esc_html_e( 'No data yet. Make API requests to see analytics.', 'autonode-pro'); ?></p>
        <?php else : ?>
        <table class="amp-table widefat">
            <thead><tr>
                <th><?php esc_html_e( 'Endpoint', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Method', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Hits', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Avg ms', 'autonode-pro'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $autonode_top_ep as $autonode_ep ) : ?>
            <tr>
                <td><code><?php echo esc_html( $autonode_ep['endpoint'] ); ?></code></td>
                <td><span class="amp-method amp-method-<?php echo esc_attr( strtolower( $autonode_ep['method'] ) ); ?>"><?php echo esc_html( $autonode_ep['method'] ); ?></span></td>
                <td><strong><?php echo esc_html( number_format( (int) $autonode_ep['hits'] ) ); ?></strong></td>
                <td><?php echo $autonode_ep['avg_ms'] ? esc_html( round( (float) $autonode_ep['avg_ms'], 1 ) . 'ms' ) : ''; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="amp-card">
        <div class="amp-card-header">
            <h3><?php esc_html_e( 'Active API Keys', 'autonode-pro'); ?></h3>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-keys' ) ); ?>" class="amp-link"><?php esc_html_e( 'Manage', 'autonode-pro'); ?></a>
        </div>
        <?php $autonode_active = array_filter( $autonode_keys, fn( $k ) => ! $k['revoked'] ); ?>
        <?php if ( empty( $autonode_active ) ) : ?>
            <p class="amp-empty"><a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-keys' ) ); ?>"><?php esc_html_e( 'Create your first API key', 'autonode-pro'); ?></a></p>
        <?php else : ?>
        <table class="amp-table">
            <thead><tr>
                <th><?php esc_html_e( 'Key', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Label', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Requests', 'autonode-pro'); ?></th>
                <th><?php esc_html_e( 'Last Used', 'autonode-pro'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ( array_slice( $autonode_active, 0, 6 ) as $autonode_k ) : ?>
            <tr>
                <td><code class="amp-key-prefix"><?php echo esc_html( $autonode_k['key_prefix'] ); ?></code></td>
                <td><span class="amp-env-dot amp-env-<?php echo esc_attr( $autonode_k['environment'] ); ?>"></span><?php echo esc_html( $autonode_k['label'] ); ?></td>
                <td><?php echo esc_html( number_format( (int) $autonode_k['total_requests'] ) ); ?></td>
                <td><small><?php echo $autonode_k['last_used_at'] ? esc_html( human_time_diff( strtotime( $autonode_k['last_used_at'] ) ) . ' ' . __( 'ago', 'autonode-pro') ) : esc_html__( 'Never', 'autonode-pro'); ?></small></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- System Status -->
<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'System Status', 'autonode-pro'); ?></h3></div>
    <div class="amp-grid-3">
        <div class="amp-step" style="border:0">
            <div class="amp-stat-body">
                <span style="font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Permalinks', 'autonode-pro'); ?></span><br>
                <?php 
                $autonode_perm = get_option( 'selection_structure' ) || get_option( 'permalink_structure' ); 
                if ( ! empty( $autonode_perm ) ) : ?>
                    <span class="amp-badge amp-badge-active">✓ <?php esc_html_e( 'Post Name / Custom', 'autonode-pro'); ?></span>
                <?php else : ?>
                    <span class="amp-badge amp-badge-revoked">⚠ <?php esc_html_e( 'Plain (API incompatible)', 'autonode-pro'); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="amp-step" style="border:0">
            <div class="amp-stat-body">
                <span style="font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'SEO Support', 'autonode-pro'); ?></span><br>
                <?php if ( 'none' !== $autonode_sp ) : ?>
                    <span class="amp-badge amp-badge-active">✓ <?php echo esc_html( ucfirst( $autonode_sp ) ); ?></span>
                <?php else : ?>
                    <span class="amp-badge amp-badge-expired">⚠ <?php esc_html_e( 'Not Detected', 'autonode-pro'); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="amp-step" style="border:0">
            <div class="amp-stat-body">
                <span style="font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'SSL / HTTPS', 'autonode-pro'); ?></span><br>
                <?php if ( is_ssl() ) : ?>
                    <span class="amp-badge amp-badge-active">✓ <?php esc_html_e( 'Secure', 'autonode-pro'); ?></span>
                <?php else : ?>
                    <span class="amp-badge amp-badge-revoked">⚠ <?php esc_html_e( 'Insecure', 'autonode-pro'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- n8n Quick Connect -->
<div class="amp-card">
    <div class="amp-card-header">
        <h3><?php esc_html_e( 'n8n Quick Connect', 'autonode-pro'); ?></h3>
        <button id="amp-download-n8n" class="amp-btn amp-btn-ghost" style="font-size:12px">
            <span class="dashicons dashicons-download" style="font-size:14px;margin-top:2px"></span> <?php esc_html_e( 'Download n8n Config', 'autonode-pro'); ?>
        </button>
    </div>
    <div class="amp-grid-3">
        <div class="amp-step"><span class="amp-step-num">1</span>
            <div>
                <strong><?php esc_html_e( 'Add Header Auth Credential', 'autonode-pro'); ?></strong>
                <p><?php esc_html_e( 'In n8n -> Credentials -> Header Auth', 'autonode-pro'); ?></p>
                <code>Name: Authorization<br>Value: Bearer ampcm_&lt;key&gt;</code>
            </div>
        </div>
        <div class="amp-step"><span class="amp-step-num">2</span>
            <div>
                <strong><?php esc_html_e( 'Set Base URL', 'autonode-pro'); ?></strong>
                <p><?php esc_html_e( 'HTTP Request node URL:', 'autonode-pro'); ?></p>
                <code><?php echo esc_url( get_site_url() . '/wp-json/' . AUTONODE_NS ); ?></code>
            </div>
        </div>
        <div class="amp-step"><span class="amp-step-num">3</span>
            <div>
                <strong><?php esc_html_e( 'POST /posts with inline SEO', 'autonode-pro'); ?></strong>
                <p><?php esc_html_e( 'Send title, content, categories, tags, seo fields in one call.', 'autonode-pro'); ?></p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-docs' ) ); ?>" class="amp-link"><?php esc_html_e( 'Full Docs', 'autonode-pro'); ?></a>
            </div>
        </div>
    </div>
</div>
</div>


