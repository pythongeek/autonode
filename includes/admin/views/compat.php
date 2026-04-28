<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title">Theme Compatibility</h1>
        <span class="amp-badge-pill amp-pill-<?php echo $compat['is_agentic']?'ok':''; ?>"><?php echo $compat['is_agentic']?' Agentic Pro Detected':'Generic WP Theme'; ?></span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3>Active Theme</h3></div>
    <table class="amp-table"><tbody>
        <tr><th style="width:35%;padding:10px 14px;font-size:12px;color:var(--amp-muted)">Theme Name</th><td style="padding:10px 14px"><strong><?php echo esc_html($compat['theme_name']); ?></strong></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)">Slug</th><td style="padding:10px 14px"><code><?php echo esc_html($compat['theme_slug']); ?></code></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)">Parent</th><td style="padding:10px 14px"><code><?php echo esc_html($compat['parent_slug']); ?></code></td></tr>
        <tr><th style="padding:10px 14px;font-size:12px;color:var(--amp-muted)">Agentic Detected</th><td style="padding:10px 14px"><?php echo $compat['is_agentic']?'<span class="amp-badge amp-badge-active">Yes</span>':'<span class="amp-badge amp-badge-expired">No</span>'; ?></td></tr>
    </tbody></table>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3>Hook Segregation  Zero Conflicts</h3></div>
    <table class="amp-table"><thead><tr><th>Hook</th><th>Theme Uses</th><th>Plugin Uses</th><th>Safe?</th></tr></thead><tbody>
    <?php
    $autonode_rows = array(
        array( 'wp_enqueue_scripts', 'Frontend CSS/JS (lines 137347)', 'Never  admin only', '' ),
        array( 'admin_enqueue_scripts', 'Never', 'Our pages only (hook-gated)', '' ),
        array( 'rest_api_init', 'Never', 'autonode/v1/* routes', '' ),
        array( 'wp_head', 'Critical CSS, resource hints, animation fixes', 'Never', '' ),
        array( 'body_class', 'Frontend: page-optimized, no-tailwind', 'Never (uses admin_body_class)', '' ),
        array( 'admin_body_class', 'Never', 'Adds autonode-page on our pages only', '' ),
        array( 'script_loader_tag', 'Defers tailwind-cdn, page-specific-js', 'Never uses these handles', '' ),
        array( 'wp_footer', 'Tailwind cleanup, loaded class', 'Never', '' ),
        array( 'wp_insert_post', 'Never', 'Webhook dispatcher (background cron)', '' ),
        array( 'wp_is_application_passwords_available', $compat['app_pass_active'] ? '__return_true (security risk)' : 'Not used', 'Not filtered', $compat['app_pass_active'] ? '' : '' ),
    );
    foreach ( $autonode_rows as list( $autonode_hook, $autonode_theme, $autonode_plugin, $autonode_status ) ) :
        $autonode_warn = str_contains( $autonode_status, '' );
    ?>
    <tr <?php echo $autonode_warn ? 'style="background:rgba(245,158,11,0.05)"' : ''; ?>>
        <td><code><?php echo esc_html( $autonode_hook ); ?></code></td>
        <td><small style="color:var(--amp-muted)"><?php echo esc_html( $autonode_theme ); ?></small></td>
        <td><small style="color:var(--amp-muted)"><?php echo esc_html( $autonode_plugin ); ?></small></td>
        <td><span class="amp-badge <?php echo $autonode_warn ? 'amp-badge-warn' : 'amp-badge-active'; ?>"><?php echo esc_html( $autonode_status ); ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
</div>

<?php if ($compat['app_pass_active']) : ?>
<div class="amp-card" style="border-color:rgba(245,158,11,0.4)">
    <div class="amp-card-header"><h3 style="color:var(--amp-yellow)"> Application Passwords Exposed</h3></div>
    <div class="amp-alert amp-alert-warn">Your theme's <code>functions.php</code> line 337 has:<br><code>add_filter('wp_is_application_passwords_available','__return_true');</code><br>This allows WP REST API access with any valid username/password from any IP.</div>
    <div class="amp-grid-3" style="margin-top:16px">
        <div class="amp-step"><span class="amp-step-num">1</span><div><strong>Audit existing App Passwords</strong><p>Users  Profile  Application Passwords  revoke any unrecognised entries.</p></div></div>
        <div class="amp-step"><span class="amp-step-num">2</span><div><strong>Migrate n8n to this plugin</strong><p>Replace the WordPress Application Password credential in n8n with a plugin API key (Header Auth).</p></div></div>
        <div class="amp-step"><span class="amp-step-num">3</span><div><strong>Remove the theme filter</strong><p>Once migrated, comment out line 337 in <code>functions.php</code>. This plugin does NOT need Application Passwords.</p></div></div>
    </div>
</div>
<?php endif; ?>

<div class="amp-card">
    <div class="amp-card-header"><h3>Handle Namespace  No Clashes</h3></div>
    <div class="amp-grid-2">
        <div><h4 style="font-size:12px;color:var(--amp-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Theme Handles</h4>
            <?php foreach ( array( 'parent-style', 'child-style', 'agentic-fonts', 'agentic-global', 'font-awesome', 'font-awesome-local', 'agentic-mobile-fix', 'agentic-{page-slug}', 'page-specific-css', 'page-specific-js' ) as $autonode_h ) : ?>
            <span class="amp-scope-tag" style="background:rgba(59,130,246,.1);color:#93c5fd;border-color:rgba(59,130,246,.2)"><?php echo esc_html( $autonode_h ); ?></span>
            <?php endforeach; ?>
        </div>
        <div><h4 style="font-size:12px;color:var(--amp-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Plugin Handles (admin only)</h4>
            <?php foreach ( array( 'amp-cm', 'autonode-chart', 'autonode-admin' ) as $autonode_h ) : ?>
            <span class="amp-scope-tag"><?php echo esc_html( $autonode_h ); ?></span>
            <?php endforeach; ?>
            <p style="margin-top:10px;font-size:12px;color:var(--amp-muted)">All admin-only, all prefixed <code>amp-cm</code>. Zero frontend bleed.</p>
        </div>
    </div>
</div>
</div>

