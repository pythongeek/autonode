<?php defined( 'ABSPATH' ) || exit;
$s = $settings;
$autonode_cron = \AutoNode\Cron_Health::status();
$autonode_blocks = \AutoNode\Brute_Force::list_blocks();
?>
<div class="amp-wrap">
<div class="amp-header"><div class="amp-header-left"><h1 class="amp-page-title">Settings</h1></div></div>
<div class="amp-settings-layout">

<!-- Left column: settings form -->
<div>
    <form id="amp-settings-form">
        <div class="amp-card">
            <div class="amp-card-header"><h3>Security</h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Require HTTPS</strong><p>Reject API requests over plain HTTP.</p></div>
                <label class="amp-toggle"><input type="checkbox" name="require_https" <?php checked(!empty($s['require_https'])); ?>><span class="amp-toggle-slider"></span></label>
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Brute-Force Limit</strong><p>Failed auth attempts per IP before blocking (per window).</p></div>
                <input type="number" name="brute_force_limit" value="<?php echo (int)($s['brute_force_limit']??10); ?>" min="3" max="100" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Brute-Force Window (s)</strong><p>How long to count failures before resetting.</p></div>
                <input type="number" name="brute_force_window" value="<?php echo (int)($s['brute_force_window']??300); ?>" min="60" max="3600" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Whitelisted Emails (Full HTML Access)</strong><p>One email per line. Users with these emails can push ANY raw HTML via API.</p></div>
                <textarea name="whitelisted_emails" class="amp-input" rows="3" style="width:100%;max-width:300px;resize:vertical"><?php echo esc_textarea($s['whitelisted_emails']??''); ?></textarea>
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3>Rate Limiting</h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Max Requests per Window</strong><p>Per API key. n8n workflows should stay under this.</p></div>
                <input type="number" name="rate_limit" value="<?php echo (int)($s['rate_limit']??120); ?>" min="1" max="10000" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Window Size (seconds)</strong></div>
                <input type="number" name="rate_window_sec" value="<?php echo (int)($s['rate_window_sec']??60); ?>" min="10" max="3600" class="amp-input" style="max-width:90px">
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3>Webhooks</h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Enable Webhooks</strong></div>
                <label class="amp-toggle"><input type="checkbox" name="enable_webhooks" <?php checked(!empty($s['enable_webhooks'])); ?>><span class="amp-toggle-slider"></span></label>
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Timeout (ms)</strong><p>HTTP timeout per webhook delivery attempt.</p></div>
                <input type="number" name="webhook_timeout_ms" value="<?php echo (int)($s['webhook_timeout_ms']??5000); ?>" min="1000" max="30000" step="500" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Max Retry Attempts</strong><p>Retries on failure: 1min  5min  15min delays.</p></div>
                <input type="number" name="max_retry_attempts" value="<?php echo (int)($s['max_retry_attempts']??3); ?>" min="0" max="5" class="amp-input" style="max-width:90px">
            </div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Flatten n8n Payloads</strong><p>Optimize JSON structure for n8n to avoid complicated expressions.</p></div>
                <label class="amp-toggle"><input type="checkbox" name="flatten_n8n_webhooks" <?php checked(!empty($s['flatten_n8n_webhooks'])); ?>><span class="amp-toggle-slider"></span></label>
            </div>
        </div>

        <div class="amp-card">
            <div class="amp-card-header"><h3>Data Retention</h3></div>
            <div class="amp-settings-row">
                <div class="amp-settings-label"><strong>Log Retention (days)</strong><p>Activity log + analytics pruned daily.</p></div>
                <input type="number" name="log_retention_days" value="<?php echo (int)($s['log_retention_days']??90); ?>" min="7" max="365" class="amp-input" style="max-width:90px">
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;margin-top:4px">
            <button type="submit" class="amp-btn amp-btn-primary">Save Settings</button>
            <span id="amp-save-status"></span>
        </div>
    </form>
</div>

<!-- Right column: system info + cron health + blocks -->
<div>

    <!-- System Info -->
    <div class="amp-card">
        <div class="amp-card-header"><h3>System Info</h3></div>
        <table class="amp-table">
            <tbody>
                <tr><th style="width:45%;padding:9px 14px;font-size:12px;color:var(--amp-muted)">Plugin</th><td style="padding:9px 14px;font-size:13px"><?php echo esc_html(AUTONODE_VERSION); ?></td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">DB Schema</th><td style="padding:9px 14px;font-size:13px">v<?php echo (int)get_option('autonode_db_version'); ?></td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">WordPress</th><td style="padding:9px 14px;font-size:13px"><?php echo esc_html(get_bloginfo('version')); ?></td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">PHP</th><td style="padding:9px 14px;font-size:13px"><?php echo esc_html(PHP_VERSION); ?></td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">SEO Plugin</th><td style="padding:9px 14px;font-size:13px">
                    <?php $autonode_sp = \AutoNode\Rankmath_Handler::active_plugin(); ?>
                    <span class="amp-badge amp-badge-<?php echo 'none' !== $autonode_sp ? 'active' : 'expired'; ?>"><?php echo esc_html( ucfirst( $autonode_sp ) ); ?></span>
                </td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">Theme</th><td style="padding:9px 14px;font-size:13px">
                    <?php echo esc_html($compat['theme_name']); ?>
                    <?php if($compat['is_agentic'])echo '<span class="amp-badge amp-badge-active" style="margin-left:6px"> Compat</span>'; ?>
                </td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">HTTPS</th><td style="padding:9px 14px;font-size:13px"><?php echo is_ssl()?'<span class="amp-badge amp-badge-active">Yes</span>':'<span class="amp-badge amp-badge-revoked">No</span>'; ?></td></tr>
                <tr><th style="padding:9px 14px;font-size:12px;color:var(--amp-muted)">API Base</th><td style="padding:9px 14px"><code style="font-size:11px"><?php echo esc_html(get_site_url().'/wp-json/'.AUTONODE_NS); ?></code></td></tr>
            </tbody>
        </table>
    </div>

    <!-- Cron Health -->
    <div class="amp-card" style="<?php echo 'stale' === $autonode_cron['status'] ? 'border-color:rgba(245,158,11,0.4)' : ''; ?>">
        <div class="amp-card-header">
            <h3> Cron Health</h3>
            <?php if ( 'ok' === $autonode_cron['status'] || 'real_cron' === $autonode_cron['status'] ) : ?>
                <span class="amp-badge amp-badge-active"> <?php echo 'real_cron' === $autonode_cron['status'] ? 'Server Cron' : 'Healthy'; ?></span>
            <?php else : ?>
                <span class="amp-badge amp-badge-warn"> Stale</span>
            <?php endif; ?>
        </div>
        <p style="font-size:13px;color:var(--amp-dim);margin-bottom:14px"><?php echo esc_html( $autonode_cron['message'] ); ?></p>

        <?php if ( 'stale' === $autonode_cron['status'] ) : ?>
        <div class="amp-alert amp-alert-warn">
            <strong>Fix:</strong> Add a real server cron to your hosting control panel:<br>
            <code style="display:block;margin-top:6px">*/5 * * * * php <?php echo esc_html(ABSPATH); ?>wp-cron.php > /dev/null 2>&1</code>
        </div>
        <?php endif; ?>

        <table class="amp-table" style="margin-top:10px">
            <thead><tr><th>Hook</th><th>Next Run</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ( $autonode_cron['scheduled_hooks'] as $autonode_hook ) : ?>
            <tr>
                <td><code style="font-size:11px"><?php echo esc_html( $autonode_hook['hook'] ); ?></code></td>
                <td><small><?php echo esc_html( $autonode_hook['next_run'] ); ?></small></td>
                <td><?php echo $autonode_hook['scheduled'] ? '<span class="amp-badge amp-badge-active"></span>' : '<span class="amp-badge amp-badge-revoked">Missing</span>'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Brute Force / Blocked IPs -->
    <div class="amp-card">
        <div class="amp-card-header">
            <h3> Blocked IPs</h3>
            <span class="amp-badge-pill"><?php echo count( $autonode_blocks ); ?> active</span>
        </div>
        <?php if ( empty( $autonode_blocks ) ) : ?>
            <p class="amp-empty">No IPs currently blocked. Brute-force protection is active.</p>
        <?php else: ?>
        <table class="amp-table">
            <thead><tr><th>IP Address</th><th>Failed Auth Hits</th><th>Blocked Until</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ( $autonode_blocks as $autonode_b ) : ?>
            <tr>
                <td><code class="amp-mono"><?php echo esc_html( $autonode_b['ip_address'] ); ?></code></td>
                <td><?php echo (int) $autonode_b['hits']; ?></td>
                <td><small><?php echo $autonode_b['blocked_until'] ? esc_html( human_time_diff( strtotime( $autonode_b['blocked_until'] ) ) . ' remaining' ) : ''; ?></small></td>
                <td><button class="amp-btn amp-btn-ghost-sm amp-unblock-ip" data-ip="<?php echo esc_attr( $autonode_b['ip_address'] ); ?>">Unblock</button></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Connection test -->
    <div class="amp-card">
        <div class="amp-card-header"><h3>Live Connection Test</h3></div>
        <p style="font-size:12px;color:var(--amp-muted);margin-bottom:10px">Paste your API key to test from browser.</p>
        <input type="password" id="amp-test-key" placeholder="ampcm_" class="amp-input" style="margin-bottom:10px">
        <button class="amp-btn amp-btn-secondary" id="amp-test-conn-btn">Test Connection</button>
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
        var $btn=$(this).find('[type=submit]').prop('disabled',true).text('Saving');
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
            error:function(x){$box.css('color','var(--amp-red)').text('Error '+x.status+': '+x.responseText);}});
    });
});
</script>

