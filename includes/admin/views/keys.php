<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title">API Keys</h1>
        <span class="amp-badge-pill"><?php echo count(array_filter($keys,fn($k)=>!$k['revoked'])); ?> active</span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3>Create New API Key</h3></div>
    <div class="amp-alert amp-alert-info"> The raw key is shown <strong>once only</strong>. Store it immediately in n8n or your password manager.</div>
    <form id="amp-create-key-form">
        <div class="amp-form-grid">
            <div class="amp-form-group"><label>Label *</label><input type="text" name="label" placeholder="e.g. n8n Production" required class="amp-input"></div>
            <div class="amp-form-group"><label>Description</label><input type="text" name="description" placeholder="What is this key used for?" class="amp-input"></div>
            <div class="amp-form-group"><label>Environment</label>
                <select name="environment" class="amp-select">
                    <option value="production">Production</option>
                    <option value="staging">Staging</option>
                    <option value="development">Development</option>
                </select>
            </div>
            <div class="amp-form-group"><label>Expires (optional)</label><input type="datetime-local" name="expires_at" class="amp-input"></div>
            <div class="amp-form-group amp-form-full"><label>IP Whitelist <small> one CIDR per line, blank = any IP</small></label>
                <textarea name="ip_whitelist" rows="3" class="amp-input amp-textarea" placeholder="203.0.113.0/24&#10;198.51.100.42"></textarea>
            </div>
        </div>

        <div class="amp-form-group amp-form-full">
            <label>Scope Preset</label>
            <div class="amp-preset-row">
                <button type="button" class="amp-preset-btn" data-preset="readonly"> Read Only</button>
                <button type="button" class="amp-preset-btn active" data-preset="writer"> Content Writer</button>
                <button type="button" class="amp-preset-btn" data-preset="full_access"> Full Access</button>
            </div>
        </div>

        <div class="amp-form-group amp-form-full">
            <label>Permissions</label>
            <div class="amp-scopes-grid">
                <?php
                $autonode_groups = array(
                    'Posts'    => array( 'posts:read', 'posts:write', 'posts:delete', 'posts:publish' ),
                    'Pages'    => array( 'pages:read', 'pages:write', 'pages:delete' ),
                    'SEO'      => array( 'seo:read', 'seo:write' ),
                    'Media'    => array( 'media:read', 'media:write', 'media:delete' ),
                    'Taxonomy' => array( 'taxonomy:read', 'taxonomy:write' ),
                    'Advanced' => array( 'bulk:write', 'webhooks:read', 'webhooks:write', 'analytics:read', 'keys:read', 'system:read' ),
                );
                $autonode_default_writer = \AutoNode\Api_Auth::PRESET_SCOPES['writer'];
                foreach ( $autonode_groups as $autonode_group => $autonode_scopes ) : ?>
                <div class="amp-scope-group">
                    <div class="amp-scope-group-label"><?php echo esc_html( $autonode_group ); ?></div>
                    <?php foreach ( $autonode_scopes as $autonode_scope ) : ?>
                    <label class="amp-scope-item">
                        <input type="checkbox" name="scopes[]" value="<?php echo esc_attr( $autonode_scope ); ?>" <?php echo in_array( $autonode_scope, $autonode_default_writer, true ) ? 'checked' : ''; ?>>
                        <span class="amp-scope-tag"><?php echo esc_html( $autonode_scope ); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="amp-form-actions">
            <button type="submit" class="amp-btn amp-btn-primary" id="amp-create-key-btn">Generate API Key</button>
        </div>
    </form>

    <div id="amp-new-key-result" style="display:none" class="amp-new-key-box">
        <div class="amp-new-key-header"><strong> Key Created  Copy it now!</strong></div>
        <div class="amp-key-display-row">
            <code id="amp-raw-key"></code>
            <button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-raw-key"> Copy</button>
        </div>
        <div class="amp-key-display-row" style="margin-top:8px">
            <span style="font-size:12px;color:var(--amp-muted)">n8n value:</span>
            <code id="amp-n8n-val"></code>
            <button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-n8n-val"> Copy</button>
        </div>
        <p style="font-size:12px;color:var(--amp-yellow);margin:10px 0 0"> This key is shown once. After dismissal it cannot be retrieved.</p>
        <button class="amp-btn amp-btn-secondary" style="margin-top:12px" id="amp-dismiss-key">I've saved it  Dismiss</button>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header">
        <h3>All API Keys</h3>
        <label class="amp-toggle-wrap"><input type="checkbox" id="amp-show-revoked"> Show revoked</label>
    </div>
    <table class="amp-table amp-keys-table">
        <thead><tr><th>Key</th><th>Label</th><th>Env</th><th>Scopes</th><th>Requests</th><th>Last Used</th><th>Expires</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php if (empty($keys)) : ?>
            <tr><td colspan="9" class="amp-empty-cell">No keys yet.</td></tr>
        <?php else : ?>
        <?php foreach ( $keys as $autonode_k ) :
            $autonode_scopes    = json_decode( $autonode_k['scopes'], true ) ?: array();
            $autonode_is_active = ! $autonode_k['revoked'] && ( ! $autonode_k['expires_at'] || strtotime( $autonode_k['expires_at'] ) > time() );
        ?>
        <tr class="amp-key-row <?php echo $autonode_k['revoked'] ? 'amp-row-revoked' : ''; ?>" data-revoked="<?php echo (int) $autonode_k['revoked']; ?>">
            <td><code class="amp-key-prefix"><?php echo esc_html( $autonode_k['key_prefix'] ); ?></code></td>
            <td><strong><?php echo esc_html( $autonode_k['label'] ); ?></strong><?php if ( $autonode_k['description'] ) { echo '<br><small class="amp-muted">' . esc_html( substr( $autonode_k['description'], 0, 50 ) ) . '</small>'; } ?></td>
            <td><span class="amp-env-badge amp-env-<?php echo esc_attr( $autonode_k['environment'] ); ?>"><?php echo esc_html( $autonode_k['environment'] ); ?></span></td>
            <td><div class="amp-scopes-compact"><?php if ( empty( $autonode_scopes ) ) { echo '<span class="amp-scope-tag amp-scope-all">All</span>'; } else { foreach ( array_slice( $autonode_scopes, 0, 3 ) as $autonode_s ) { echo '<span class="amp-scope-tag">' . esc_html( $autonode_s ) . '</span>'; } if ( count( $autonode_scopes ) > 3 ) { echo '<span class="amp-muted">+' . ( count( $autonode_scopes ) - 3 ) . '</span>'; } } ?></div></td>
            <td><?php echo esc_html( number_format( (int) $autonode_k['total_requests'] ) ); ?></td>
            <td><small><?php echo $autonode_k['last_used_at'] ? esc_html( human_time_diff( strtotime( $autonode_k['last_used_at'] ) ) . ' ago' ) : ''; ?></small></td>
            <td><small><?php echo $autonode_k['expires_at'] ? esc_html( substr( $autonode_k['expires_at'], 0, 10 ) ) : ''; ?></small></td>
            <td><?php if ( $autonode_k['revoked'] ) { echo '<span class="amp-badge amp-badge-revoked">Revoked</span>'; } elseif ( ! $autonode_is_active ) { echo '<span class="amp-badge amp-badge-expired">Expired</span>'; } else { echo '<span class="amp-badge amp-badge-active">Active</span>'; } ?></td>
            <td>
                <?php if ( ! $autonode_k['revoked'] ) : ?>
                <button class="amp-btn amp-btn-ghost-sm amp-rotate-btn" data-id="<?php echo (int) $autonode_k['id']; ?>" data-label="<?php echo esc_attr( $autonode_k['label'] ); ?>" style="margin-right:4px" title="Generate new secret, keep same ID/scopes"> Rotate</button>
                <button class="amp-btn amp-btn-danger-sm amp-revoke-btn" data-id="<?php echo (int) $autonode_k['id']; ?>" data-label="<?php echo esc_attr( $autonode_k['label'] ); ?>">Revoke</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<script>
jQuery(document).ready(function($) {
    // Direct PHP injection to bypass the "ampCM is not defined" error completely
    var safeAjaxUrl = '<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>';
    var safeNonce   = '<?php echo esc_js( wp_create_nonce( "autonode_admin" ) ); ?>';

    // Unbind any old, cached listeners to prevent double-firing
    $('#amp-create-key-form').off('submit').on('submit', function(e) {
        e.preventDefault(); // Stop the blank page reload!
        
        var $btn = $('#amp-create-key-btn').prop('disabled', true).text('Generating...');
        var scopes = $('[name="scopes[]"]:checked').map(function () { return $(this).val(); }).get();
        
        var payload = {
            action: 'autonode_create_key', 
            nonce: safeNonce,
            label: $('[name=label]').val(), 
            description: $('[name=description]').val(),
            environment: $('[name=environment]').val(), 
            expires_at: $('[name=expires_at]').val(),
            ip_whitelist: $('[name=ip_whitelist]').val(),
            'scopes[]': scopes,
            preset: $('.amp-preset-btn.active').data('preset') || ''
        };

        $.post(safeAjaxUrl, payload)
        .done(function(res) {
            if (res.success) {
                var raw = res.data.raw_key;
                $('#amp-raw-key').text(raw);
                $('#amp-n8n-val').text('Bearer ' + raw);
                $('#amp-new-key-result').slideDown(250);
                $('#amp-create-key-form')[0].reset();
                $('[name="scopes[]"]').prop('checked', false);
                $('html,body').animate({ scrollTop: $('#amp-new-key-result').offset().top - 80 }, 400);
            } else {
                alert("Server Error: " + (res.data && res.data.message ? res.data.message : "Unknown error inside plugin."));
            }
        })
        .fail(function(xhr, status, error) {
            alert("AJAX Request Failed!\n\nStatus: " + status + "\nError: " + error + "\n\nServer Response: " + xhr.responseText);
        })
        .always(function() {
            $btn.prop('disabled', false).text('Generate API Key');
        });
    });
});
</script>
