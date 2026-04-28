<?php defined( 'ABSPATH' ) || exit;
$s = $settings;
$autonode_cron = \AutoNode\Cron_Health::status();
$autonode_blocks = \AutoNode\Brute_Force::list_blocks();
?>
<div class="amp-wrap">
<div class="amp-wrap">
<div class="amp-header"><div class="amp-header-left"><h1 class="amp-page-title"><?php esc_html_e( 'Settings', 'autonode' ); ?></h1></div></div>
<div class="amp-settings-layout">

<!-- Left column: settings form -->
<div>
    <form id="amp-settings-form">
        <div class="amp-card">
            <div class="amp-card-header"><h3><?php esc_html_e( 'Security', 'autonode' ); ?></h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Require HTTPS', 'autonode' ); ?></strong><p><?php esc_html_e( 'Reject API requests over plain HTTP.', 'autonode' ); ?></p></div>
                <label class="amp-toggle"><input type="checkbox" name="require_https" <?php checked( ! empty( $s['require_https'] ) ); ?>><span class="amp-toggle-slider"></span></label>
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Brute-Force Limit', 'autonode' ); ?></strong><p><?php esc_html_e( 'Failed auth attempts per IP before blocking (per window).', 'autonode' ); ?></p></div>
                <input type="number" name="brute_force_limit" value="<?php echo (int) ( $s['brute_force_limit'] ?? 10 ); ?>" min="3" max="100" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Brute-Force Window (s)', 'autonode' ); ?></strong><p><?php esc_html_e( 'How long to count failures before resetting.', 'autonode' ); ?></p></div>
                <input type="number" name="brute_force_window" value="<?php echo (int) ( $s['brute_force_window'] ?? 300 ); ?>" min="60" max="3600" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Whitelisted Emails (Full HTML Access)', 'autonode' ); ?></strong><p><?php esc_html_e( 'One email per line. Users with these emails can push ANY raw HTML via API.', 'autonode' ); ?></p></div>
                <textarea name="whitelisted_emails" class="amp-input" rows="3" style="width:100%;max-width:300px;resize:vertical"><?php echo esc_textarea( $s['whitelisted_emails'] ?? '' ); ?></textarea>
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3><?php esc_html_e( 'Rate Limiting', 'autonode' ); ?></h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Max Requests per Window', 'autonode' ); ?></strong><p><?php esc_html_e( 'Per API key. n8n workflows should stay under this.', 'autonode' ); ?></p></div>
                <input type="number" name="rate_limit" value="<?php echo (int) ( $s['rate_limit'] ?? 120 ); ?>" min="1" max="10000" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Window Size (seconds)', 'autonode' ); ?></strong></div>
                <input type="number" name="rate_window_sec" value="<?php echo (int) ( $s['rate_window_sec'] ?? 60 ); ?>" min="10" max="3600" class="amp-input" style="max-width:90px">
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3><?php esc_html_e( 'Webhooks', 'autonode' ); ?></h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Enable Webhooks', 'autonode' ); ?></strong></div>
                <label class="amp-toggle"><input type="checkbox" name="enable_webhooks" <?php checked( ! empty( $s['enable_webhooks'] ) ); ?>><span class="amp-toggle-slider"></span></label>
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Timeout (ms)', 'autonode' ); ?></strong><p><?php esc_html_e( 'HTTP timeout per webhook delivery attempt.', 'autonode' ); ?></p></div>
                <input type="number" name="webhook_timeout_ms" value="<?php echo (int) ( $s['webhook_timeout_ms'] ?? 5000 ); ?>" min="1000" max="30000" step="500" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Max Retry Attempts', 'autonode' ); ?></strong><p><?php esc_html_e( 'Retries on failure: 1min, 5min, 15min delays.', 'autonode' ); ?></p></div>
                <input type="number" name="max_retry_attempts" value="<?php echo (int) ( $s['max_retry_attempts'] ?? 3 ); ?>" min="0" max="5" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Flatten n8n Payloads', 'autonode' ); ?></strong><p><?php esc_html_e( 'Optimize JSON structure for n8n to avoid complicated expressions.', 'autonode' ); ?></p></div>
                <label class="amp-toggle"><input type="checkbox" name="flatten_n8n_webhooks" <?php checked( ! empty( $s['flatten_n8n_webhooks'] ) ); ?>><span class="amp-toggle-slider"></span></label>
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3><?php esc_html_e( 'Access Control', 'autonode' ); ?></h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Menu Access Capability', 'autonode' ); ?></strong><p><?php esc_html_e( 'Minimum capability required to access the AutoNode menu and dashboard.', 'autonode' ); ?></p></div>
                <select name="min_capability" class="amp-select" style="max-width:180px">
                    <option value="manage_options" <?php selected( $s['min_capability'] ?? 'manage_options', 'manage_options' ); ?>><?php esc_html_e( 'Administrator', 'autonode' ); ?></option>
                    <option value="edit_others_posts" <?php selected( $s['min_capability'] ?? 'manage_options', 'edit_others_posts' ); ?>><?php esc_html_e( 'Editor (edit_others_posts)', 'autonode' ); ?></option>
                    <option value="publish_posts" <?php selected( $s['min_capability'] ?? 'manage_options', 'publish_posts' ); ?>><?php esc_html_e( 'Author (publish_posts)', 'autonode' ); ?></option>
                </select>
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3><?php esc_html_e( 'Media & Storage', 'autonode' ); ?></h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Max Sideload Size (MB)', 'autonode' ); ?></strong><p><?php esc_html_e( 'Maximum file size allowed for URL sideloading and base64 uploads.', 'autonode' ); ?></p></div>
                <input type="number" name="max_sideload_size" value="<?php echo (int) ( $s['max_sideload_size'] ?? 20 ); ?>" min="1" max="500" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong><?php esc_html_e( 'Log Retention (days)', 'autonode' ); ?></strong><p><?php esc_html_e( 'Activity log + analytics pruned daily.', 'autonode' ); ?></p></div>
                <input type="number" name="log_retention_days" value="<?php echo (int) ( $s['log_retention_days'] ?? 90 ); ?>" min="7" max="365" class="amp-input" style="max-width:90px">
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;margin-top:4px">
            <button type="submit" class="amp-btn amp-btn-primary"><?php esc_html_e( 'Save Settings', 'autonode' ); ?></button>
            <span id="amp-save-status"></span>
        </div>
    </form>
</div>

<!-- Right column: system info + cron health + blocks -->
<div>

    <!-- System Info -->
    <div class="amp-card">
        <div class="amp-card-header"><h3><?php esc_html_e( 'System Info', 'autonode' ); ?></h3></div>
        <table class="amp-table">
            <tbody>
                <tr>
                    <th style="width:45%;padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Plugin', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px"><?php echo esc_html( AUTONODE_VERSION ); ?></td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'DB Schema', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px">v<?php echo (int) get_option( 'autonode_db_version' ); ?></td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'WordPress', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'PHP', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px"><?php echo esc_html( PHP_VERSION ); ?></td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'SEO Plugin', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px">
                        <?php $autonode_sp = \AutoNode\Rankmath_Handler::active_plugin(); ?>
                        <span class="amp-badge amp-badge-<?php echo 'none' !== $autonode_sp ? 'active' : 'expired'; ?>"><?php echo esc_html( ucfirst( $autonode_sp ) ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'Theme', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px">
                        <?php echo esc_html( $compat['theme_name'] ); ?>
                        <?php if ( $compat['is_agentic'] ) echo '<span class="amp-badge amp-badge-active" style="margin-left:6px">' . esc_html__( 'Compat', 'autonode' ) . '</span>'; ?>
                    </td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'HTTPS', 'autonode' ); ?></th>
                    <td style="padding:9px 14px;font-size:13px">
                        <?php echo is_ssl() ? '<span class="amp-badge amp-badge-active">' . esc_html__( 'Yes', 'autonode' ) . '</span>' : '<span class="amp-badge amp-badge-revoked">' . esc_html__( 'No', 'autonode' ) . '</span>'; ?>
                    </td>
                </tr>
                <tr>
                    <th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'API Base', 'autonode' ); ?></th>
                    <td style="padding:9px 14px"><code style="font-size:11px"><?php echo esc_html( get_site_url() . '/wp-json/' . AUTONODE_NS ); ?></code></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cron Health -->
    <div class="amp-card" style="<?php echo 'stale' === $autonode_cron['status'] ? 'border-color:rgba(245,158,11,0.4)' : ''; ?>">
        <div class="amp-card-header">
            <h3><?php esc_html_e( 'Cron Health', 'autonode' ); ?></h3>
            <?php if ( 'ok' === $autonode_cron['status'] || 'real_cron' === $autonode_cron['status'] ) : ?>
                <span class="amp-badge amp-badge-active"> <?php echo 'real_cron' === $autonode_cron['status'] ? esc_html__( 'Server Cron', 'autonode' ) : esc_html__( 'Healthy', 'autonode' ); ?></span>
            <?php else : ?>
                <span class="amp-badge amp-badge-warn"><?php esc_html_e( 'Stale', 'autonode' ); ?></span>
            <?php endif; ?>
        </div>
        <p style="font-size:13px;color:var(--amp-dim);margin-bottom:14px"><?php echo esc_html( $autonode_cron['message'] ); ?></p>

        <?php if ( 'stale' === $autonode_cron['status'] ) : ?>
        <div class="amp-alert amp-alert-warn">
            <strong><?php esc_html_e( 'Fix:', 'autonode' ); ?></strong> <?php esc_html_e( 'Add a real server cron to your hosting control panel:', 'autonode' ); ?><br>
            <code style="display:block;margin-top:6px">*/5 * * * * php <?php echo esc_html( ABSPATH ); ?>wp-cron.php > /dev/null 2>&1</code>
        </div>
        <?php endif; ?>

        <table class="amp-table" style="margin-top:10px">
            <thead><tr>
                <th><?php esc_html_e( 'Hook', 'autonode' ); ?></th>
                <th><?php esc_html_e( 'Next Run', 'autonode' ); ?></th>
                <th><?php esc_html_e( 'Status', 'autonode' ); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $autonode_cron['scheduled_hooks'] as $autonode_hook ) : ?>
            <tr>
                <td><code style="font-size:11px"><?php echo esc_html( $autonode_hook['hook'] ); ?></code></td>
                <td><small><?php echo esc_html( $autonode_hook['next_run'] ); ?></small></td>
                <td>
                    <?php if ( $autonode_hook['scheduled'] ) : ?>
                        <span class="amp-badge amp-badge-active"></span>
                    <?php else : ?>
                        <span class="amp-badge amp-badge-revoked"><?php esc_html_e( 'Missing', 'autonode' ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Brute Force / Blocked IPs -->
    <div class="amp-card">
        <div class="amp-card-header">
            <h3><?php esc_html_e( 'Blocked IPs', 'autonode' ); ?></h3>
            <span class="amp-badge-pill"><?php printf( esc_html__( '%d active', 'autonode' ), count( $autonode_blocks ) ); ?></span>
        </div>
        <?php if ( empty( $autonode_blocks ) ) : ?>
            <p class="amp-empty"><?php esc_html_e( 'No IPs currently blocked. Brute-force protection is active.', 'autonode' ); ?></p>
        <?php else : ?>
        <table class="amp-table">
            <thead><tr>
                <th><?php esc_html_e( 'IP Address', 'autonode' ); ?></th>
                <th><?php esc_html_e( 'Failed Auth Hits', 'autonode' ); ?></th>
                <th><?php esc_html_e( 'Blocked Until', 'autonode' ); ?></th>
                <th><?php esc_html_e( 'Action', 'autonode' ); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $autonode_blocks as $autonode_b ) : ?>
            <tr>
                <td><code class="amp-mono"><?php echo esc_html( $autonode_b['ip_address'] ); ?></code></td>
                <td><?php echo (int) $autonode_b['hits']; ?></td>
                <td><small><?php echo $autonode_b['blocked_until'] ? esc_html( human_time_diff( strtotime( $autonode_b['blocked_until'] ) ) . ' ' . __( 'remaining', 'autonode' ) ) : ''; ?></small></td>
                <td><button class="amp-btn amp-btn-ghost-sm amp-unblock-ip" data-ip="<?php echo esc_attr( $autonode_b['ip_address'] ); ?>"><?php esc_html_e( 'Unblock', 'autonode' ); ?></button></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Connection test -->
    <div class="amp-card">
        <div class="amp-card-header"><h3><?php esc_html_e( 'Live Connection Test', 'autonode' ); ?></h3></div>
        <p style="font-size:12px;color:var(--amp-muted);margin-bottom:10px"><?php esc_html_e( 'Paste your API key to test from browser.', 'autonode' ); ?></p>
        <input type="password" id="amp-test-key" placeholder="ampcm_" class="amp-input" style="margin-bottom:10px">
        <button class="amp-btn amp-btn-secondary" id="amp-test-conn-btn"><?php esc_html_e( 'Test Connection', 'autonode' ); ?></button>
        <pre id="amp-conn-result" class="amp-code-block" style="display:none;margin-top:12px;white-space:pre-wrap;font-size:11px"></pre>
    </div>

</div><!-- end right col -->
</div><!-- end settings-layout -->
</div>

<script>
jQuery(function($){
    // Settings save
    $('#amp-settings-form').on('submit',function(e){
        e.preventDefault();
        var $btn=$(this).find('[type=submit]').prop('disabled',true).text('<?php esc_html_e( 'Saving...', 'autonode' ); ?>');
        $.post(ampCM.ajaxUrl,{
            action:'autonode_save_settings',nonce:ampCM.nonce,
            rate_limit:$('[name=rate_limit]').val(),rate_window_sec:$('[name=rate_window_sec]').val(),
            log_retention_days:$('[name=log_retention_days]').val(),
            require_https:$('[name=require_https]').is(':checked')?1:0,
            enable_webhooks:$('[name=enable_webhooks]').is(':checked')?1:0,
            webhook_timeout_ms:$('[name=webhook_timeout_ms]').val(),
            max_retry_attempts:$('[name=max_retry_attempts]').val(),
            brute_force_limit:$('[name=brute_force_limit]').val(),
            brute_force_window:$('[name=brute_force_window]').val(),
            whitelisted_emails:$('[name=whitelisted_emails]').val(),
            flatten_n8n_webhooks:$('[name=flatten_n8n_webhooks]').is(':checked')?1:0,
            min_capability:$('[name=min_capability]').val(),
            max_sideload_size:$('[name=max_sideload_size]').val(),
        }).done(function(r){
            r.success?$('#amp-save-status').html('<span style="color:var(--amp-green)"> Saved</span>'):$('#amp-save-status').html('<span style="color:var(--amp-red)">Error</span>');
        }).always(function(){$btn.prop('disabled',false).text('Save Settings');setTimeout(()=>$('#amp-save-status').html(''),3000);});
    });
    // Unblock IP
    $(document).on('click','.amp-unblock-ip',function(){
        var ip=$(this).data('ip');
        if(!confirm('Unblock '+ip+'?'))return;
        $.post(ampCM.ajaxUrl,{action:'autonode_unblock_ip',nonce:ampCM.nonce,ip:ip}).done(function(r){
            if(r.success)location.reload();
        });
    });
    // Connection test
    $('#amp-test-conn-btn').on('click',function(){
        var k=$('#amp-test-key').val().trim();if(!k)return;
        var $box=$('#amp-conn-result').show().text('Testing');
        $.ajax({url:ampCM.apiBase+'/status',headers:{Authorization:'Bearer '+k},
            success:function(r){$box.css('color','var(--amp-green)').text(JSON.stringify(r,null,2));},
            error:function(x){$box.css('color','var(--amp-red)').text('<?php esc_html_e( 'Error', 'autonode' ); ?> '+x.status+': '+x.responseText);}});
    });
});
</script>

