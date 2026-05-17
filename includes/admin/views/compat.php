<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title"><?php esc_html_e( 'Theme Compatibility', 'autonode-pro'); ?></h1>
        <span class="amp-badge-pill <?php echo esc_attr( $autonode_compat['is_agentic'] ? 'amp-pill-ok' : '' ); ?>">
            <?php echo $autonode_compat['is_agentic'] ? esc_html__( 'Agentic Pro Detected', 'autonode-pro') : esc_html__( 'Generic WP Theme', 'autonode-pro'); ?>
        </span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Active Theme', 'autonode-pro'); ?></h3></div>
    <table class="amp-table widefat"><tbody>
        <tr><th style="width:35%;padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Theme Name', 'autonode-pro'); ?></th><td style="padding:10px 14px"><strong><?php echo esc_html( $autonode_compat['theme_name'] ); ?></strong></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Slug', 'autonode-pro'); ?></th><td style="padding:10px 14px"><code><?php echo esc_html( $autonode_compat['theme_slug'] ); ?></code></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Parent', 'autonode-pro'); ?></th><td style="padding:10px 14px"><code><?php echo esc_html( $autonode_compat['parent_slug'] ); ?></code></td></tr>
        <tr>
            <th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Agentic Detected', 'autonode-pro'); ?></th>
            <td style="padding:10px 14px">
                <?php if ( $autonode_compat['is_agentic'] ) : ?>
                    <span class="amp-badge amp-badge-active"><?php esc_html_e( 'Yes', 'autonode-pro'); ?></span>
                <?php else : ?>
                    <span class="amp-badge amp-badge-expired"><?php esc_html_e( 'No', 'autonode-pro'); ?></span>
                <?php endif; ?>
            </td>
        </tr>
    </tbody></table>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Hook Segregation - Zero Conflicts', 'autonode-pro'); ?></h3></div>
    <table class="amp-table widefat"><thead><tr>
        <th><?php esc_html_e( 'Hook', 'autonode-pro'); ?></th>
        <th><?php esc_html_e( 'Theme Uses', 'autonode-pro'); ?></th>
        <th><?php esc_html_e( 'Plugin Uses', 'autonode-pro'); ?></th>
        <th><?php esc_html_e( 'Safe?', 'autonode-pro'); ?></th>
    </tr></thead><tbody>
    <?php
    $autonode_rows = array(
        array( 'wp_enqueue_scripts', __( 'Frontend CSS/JS (lines 1373-47)', 'autonode-pro'), __( 'Never - admin only', 'autonode-pro'), '' ),
        array( 'admin_enqueue_scripts', __( 'Never', 'autonode-pro'), __( 'Our pages only (hook-gated)', 'autonode-pro'), '' ),
        array( 'rest_api_init', __( 'Never', 'autonode-pro'), __( 'autonode/v1/* routes', 'autonode-pro'), '' ),
        array( 'wp_head', __( 'Critical CSS, resource hints, animation fixes', 'autonode-pro'), __( 'Never', 'autonode-pro'), '' ),
        array( 'body_class', __( 'Frontend: page-optimized, no-tailwind', 'autonode-pro'), __( 'Never (uses admin_body_class)', 'autonode-pro'), '' ),
        array( 'admin_body_class', __( 'Never', 'autonode-pro'), __( 'Adds autonode-page on our pages only', 'autonode-pro'), '' ),
        array( 'script_loader_tag', __( 'Defers tailwind-cdn, page-specific-js', 'autonode-pro'), __( 'Never uses these handles', 'autonode-pro'), '' ),
        array( 'wp_footer', __( 'Tailwind cleanup, loaded class', 'autonode-pro'), __( 'Never', 'autonode-pro'), '' ),
        array( 'wp_insert_post', __( 'Never', 'autonode-pro'), __( 'Webhook dispatcher (background cron)', 'autonode-pro'), '' ),
        array( 'wp_is_application_passwords_available', $autonode_compat['app_pass_active'] ? __( '__return_true (security risk)', 'autonode-pro') : __( 'Not used', 'autonode-pro'), __( 'Not filtered', 'autonode-pro'), $autonode_compat['app_pass_active'] ? '' : '' ),
    );
    foreach ( $autonode_rows as list( $autonode_hook, $autonode_theme, $autonode_plugin, $autonode_status ) ) :
        $autonode_warn = str_contains( $autonode_status, '' );
    ?>
    <tr <?php if ( $autonode_warn ) echo 'style="background:rgba(245,158,11,0.05)"'; ?>>
        <td><code><?php echo esc_html( $autonode_hook ); ?></code></td>
        <td><small style="color:var(--amp-muted)"><?php echo esc_html( $autonode_theme ); ?></small></td>
        <td><small style="color:var(--amp-muted)"><?php echo esc_html( $autonode_plugin ); ?></small></td>
        <td><span class="amp-badge <?php echo esc_attr( $autonode_warn ? 'amp-badge-warn' : 'amp-badge-active' ); ?>"><?php echo esc_html( $autonode_status ); ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>

<?php if ( $autonode_compat['app_pass_active'] ) : ?>
<div class="amp-card" style="border-color:rgba(245,158,11,0.4)">
    <div class="amp-card-header"><h3 style="color:var(--amp-yellow)"><?php esc_html_e( 'Application Passwords Exposed', 'autonode-pro'); ?></h3></div>
    <div class="amp-alert amp-alert-warn">
        <?php 
            printf( 
                /* translators: 1: functions.php filename, 2: filter code example */
                esc_html__( 'Your theme\'s %1$s line 337 has: %2$s. This allows WP REST API access with any valid username/password from any IP.', 'autonode-pro'), 
                '<code>functions.php</code>', 
                '<br><code>add_filter(\'wp_is_application_passwords_available\',\'__return_true\');</code>' 
            ); 
        ?>
    </div>
    <div class="amp-grid-3" style="margin-top:16px">
        <div class="amp-step">
            <span class="amp-step-num">1</span>
            <div>
                <strong><?php esc_html_e( 'Audit existing App Passwords', 'autonode-pro'); ?></strong>
                <p><?php esc_html_e( 'Users -> Profile -> Application Passwords -> revoke any unrecognised entries.', 'autonode-pro'); ?></p>
            </div>
        </div>
        <div class="amp-step">
            <span class="amp-step-num">2</span>
            <div>
                <strong><?php esc_html_e( 'Migrate n8n to this plugin', 'autonode-pro'); ?></strong>
                <p><?php esc_html_e( 'Replace the WordPress Application Password credential in n8n with a plugin API key (Header Auth).', 'autonode-pro'); ?></p>
            </div>
        </div>
        <div class="amp-step">
            <span class="amp-step-num">3</span>
            <div>
                <strong><?php esc_html_e( 'Remove the theme filter', 'autonode-pro'); ?></strong>
                <p><?php esc_html_e( 'Once migrated, comment out line 337 in functions.php. This plugin does NOT need Application Passwords.', 'autonode-pro'); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Handle Namespace - No Clashes', 'autonode-pro'); ?></h3></div>
    <div class="amp-grid-2">
        <div>
            <h4 style="font-size:12px;color:var(--amp-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px"><?php esc_html_e( 'Theme Handles', 'autonode-pro'); ?></h4>
            <?php foreach ( array( 'parent-style', 'child-style', 'agentic-fonts', 'agentic-global', 'font-awesome', 'font-awesome-local', 'agentic-mobile-fix', 'agentic-{page-slug}', 'page-specific-css', 'page-specific-js' ) as $autonode_h ) : ?>
            <span class="amp-scope-tag" style="background:rgba(59,130,246,.1);color:#93c5fd;border-color:rgba(59,130,246,.2)"><?php echo esc_html( $autonode_h ); ?></span>
            <?php endforeach; ?>
        </div>
        <div>
            <h4 style="font-size:12px;color:var(--amp-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px"><?php esc_html_e( 'Plugin Handles (admin only)', 'autonode-pro'); ?></h4>
            <?php foreach ( array( 'amp-cm', 'autonode-chart', 'autonode-admin' ) as $autonode_h ) : ?>
            <span class="amp-scope-tag"><?php echo esc_html( $autonode_h ); ?></span>
            <?php endforeach; ?>
            <p style="margin-top:10px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'All admin-only, all prefixed amp-cm. Zero frontend bleed.', 'autonode-pro'); ?></p>
        </div>
    </div>
</div>
</div>

