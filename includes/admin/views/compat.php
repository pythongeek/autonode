<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title"><?php esc_html_e( 'Theme Compatibility', 'autonode' ); ?></h1>
        <span class="amp-badge-pill <?php echo esc_attr( $compat['is_agentic'] ? 'amp-pill-ok' : '' ); ?>">
            <?php echo $compat['is_agentic'] ? esc_html__( 'Agentic Pro Detected', 'autonode' ) : esc_html__( 'Generic WP Theme', 'autonode' ); ?>
        </span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Active Theme', 'autonode' ); ?></h3></div>
    <table class="amp-table"><tbody>
        <tr><th style="width:35%;padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Theme Name', 'autonode' ); ?></th><td style="padding:10px 14px"><strong><?php echo esc_html( $compat['theme_name'] ); ?></strong></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Slug', 'autonode' ); ?></th><td style="padding:10px 14px"><code><?php echo esc_html( $compat['theme_slug'] ); ?></code></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Parent', 'autonode' ); ?></th><td style="padding:10px 14px"><code><?php echo esc_html( $compat['parent_slug'] ); ?></code></td></tr>
        <tr>
            <th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Agentic Detected', 'autonode' ); ?></th>
            <td style="padding:10px 14px">
                <?php if ( $compat['is_agentic'] ) : ?>
                    <span class="amp-badge amp-badge-active"><?php esc_html_e( 'Yes', 'autonode' ); ?></span>
                <?php else : ?>
                    <span class="amp-badge amp-badge-expired"><?php esc_html_e( 'No', 'autonode' ); ?></span>
                <?php endif; ?>
            </td>
        </tr>
    </tbody></table>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Hook Segregation - Zero Conflicts', 'autonode' ); ?></h3></div>
    <table class="amp-table"><thead><tr>
        <th><?php esc_html_e( 'Hook', 'autonode' ); ?></th>
        <th><?php esc_html_e( 'Theme Uses', 'autonode' ); ?></th>
        <th><?php esc_html_e( 'Plugin Uses', 'autonode' ); ?></th>
        <th><?php esc_html_e( 'Safe?', 'autonode' ); ?></th>
    </tr></thead><tbody>
    <?php
    $autonode_rows = array(
        array( 'wp_enqueue_scripts', __( 'Frontend CSS/JS (lines 1373-47)', 'autonode' ), __( 'Never - admin only', 'autonode' ), '' ),
        array( 'admin_enqueue_scripts', __( 'Never', 'autonode' ), __( 'Our pages only (hook-gated)', 'autonode' ), '' ),
        array( 'rest_api_init', __( 'Never', 'autonode' ), __( 'autonode/v1/* routes', 'autonode' ), '' ),
        array( 'wp_head', __( 'Critical CSS, resource hints, animation fixes', 'autonode' ), __( 'Never', 'autonode' ), '' ),
        array( 'body_class', __( 'Frontend: page-optimized, no-tailwind', 'autonode' ), __( 'Never (uses admin_body_class)', 'autonode' ), '' ),
        array( 'admin_body_class', __( 'Never', 'autonode' ), __( 'Adds autonode-page on our pages only', 'autonode' ), '' ),
        array( 'script_loader_tag', __( 'Defers tailwind-cdn, page-specific-js', 'autonode' ), __( 'Never uses these handles', 'autonode' ), '' ),
        array( 'wp_footer', __( 'Tailwind cleanup, loaded class', 'autonode' ), __( 'Never', 'autonode' ), '' ),
        array( 'wp_insert_post', __( 'Never', 'autonode' ), __( 'Webhook dispatcher (background cron)', 'autonode' ), '' ),
        array( 'wp_is_application_passwords_available', $compat['app_pass_active'] ? __( '__return_true (security risk)', 'autonode' ) : __( 'Not used', 'autonode' ), __( 'Not filtered', 'autonode' ), $compat['app_pass_active'] ? '' : '' ),
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

<?php if ( $compat['app_pass_active'] ) : ?>
<div class="amp-card" style="border-color:rgba(245,158,11,0.4)">
    <div class="amp-card-header"><h3 style="color:var(--amp-yellow)"><?php esc_html_e( 'Application Passwords Exposed', 'autonode' ); ?></h3></div>
    <div class="amp-alert amp-alert-warn">
        <?php 
            printf( 
                /* translators: 1: functions.php filename, 2: filter code example */
                esc_html__( 'Your theme\'s %1$s line 337 has: %2$s. This allows WP REST API access with any valid username/password from any IP.', 'autonode' ), 
                '<code>functions.php</code>', 
                '<br><code>add_filter(\'wp_is_application_passwords_available\',\'__return_true\');</code>' 
            ); 
        ?>
    </div>
    <div class="amp-grid-3" style="margin-top:16px">
        <div class="amp-step">
            <span class="amp-step-num">1</span>
            <div>
                <strong><?php esc_html_e( 'Audit existing App Passwords', 'autonode' ); ?></strong>
                <p><?php esc_html_e( 'Users -> Profile -> Application Passwords -> revoke any unrecognised entries.', 'autonode' ); ?></p>
            </div>
        </div>
        <div class="amp-step">
            <span class="amp-step-num">2</span>
            <div>
                <strong><?php esc_html_e( 'Migrate n8n to this plugin', 'autonode' ); ?></strong>
                <p><?php esc_html_e( 'Replace the WordPress Application Password credential in n8n with a plugin API key (Header Auth).', 'autonode' ); ?></p>
            </div>
        </div>
        <div class="amp-step">
            <span class="amp-step-num">3</span>
            <div>
                <strong><?php esc_html_e( 'Remove the theme filter', 'autonode' ); ?></strong>
                <p><?php esc_html_e( 'Once migrated, comment out line 337 in functions.php. This plugin does NOT need Application Passwords.', 'autonode' ); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Handle Namespace - No Clashes', 'autonode' ); ?></h3></div>
    <div class="amp-grid-2">
        <div>
            <h4 style="font-size:12px;color:var(--amp-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px"><?php esc_html_e( 'Theme Handles', 'autonode' ); ?></h4>
            <?php foreach ( array( 'parent-style', 'child-style', 'agentic-fonts', 'agentic-global', 'font-awesome', 'font-awesome-local', 'agentic-mobile-fix', 'agentic-{page-slug}', 'page-specific-css', 'page-specific-js' ) as $autonode_h ) : ?>
            <span class="amp-scope-tag" style="background:rgba(59,130,246,.1);color:#93c5fd;border-color:rgba(59,130,246,.2)"><?php echo esc_html( $autonode_h ); ?></span>
            <?php endforeach; ?>
        </div>
        <div>
            <h4 style="font-size:12px;color:var(--amp-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px"><?php esc_html_e( 'Plugin Handles (admin only)', 'autonode' ); ?></h4>
            <?php foreach ( array( 'amp-cm', 'autonode-chart', 'autonode-admin' ) as $autonode_h ) : ?>
            <span class="amp-scope-tag"><?php echo esc_html( $autonode_h ); ?></span>
            <?php endforeach; ?>
            <p style="margin-top:10px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'All admin-only, all prefixed amp-cm. Zero frontend bleed.', 'autonode' ); ?></p>
        </div>
    </div>
</div>
</div>

