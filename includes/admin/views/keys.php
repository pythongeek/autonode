<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title"><?php esc_html_e( 'API Keys', 'autonode' ); ?></h1>
        <span class="amp-badge-pill"><?php printf( esc_html__( '%d active', 'autonode' ), count( array_filter( $keys, fn( $k ) => ! $k['revoked'] ) ) ); ?></span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Create New API Key', 'autonode' ); ?></h3></div>
    <div class="amp-alert amp-alert-info"><?php esc_html_e( 'The raw key is shown once only. Store it immediately in n8n or your password manager.', 'autonode' ); ?></div>
    <form id="amp-create-key-form">
        <div class="amp-form-grid">
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Label *', 'autonode' ); ?></label>
                <input type="text" name="label" placeholder="<?php esc_attr_e( 'e.g. n8n Production', 'autonode' ); ?>" required class="amp-input">
            </div>
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Description', 'autonode' ); ?></label>
                <input type="text" name="description" placeholder="<?php esc_attr_e( 'What is this key used for?', 'autonode' ); ?>" class="amp-input">
            </div>
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Environment', 'autonode' ); ?></label>
                <select name="environment" class="amp-select">
                    <option value="production"><?php esc_html_e( 'Production', 'autonode' ); ?></option>
                    <option value="staging"><?php esc_html_e( 'Staging', 'autonode' ); ?></option>
                    <option value="development"><?php esc_html_e( 'Development', 'autonode' ); ?></option>
                </select>
            </div>
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Expires (optional)', 'autonode' ); ?></label>
                <input type="datetime-local" name="expires_at" class="amp-input">
            </div>
            <div class="amp-form-group amp-form-full">
                <label><?php esc_html_e( 'IP Whitelist', 'autonode' ); ?> <small><?php esc_html_e( 'one CIDR per line, blank = any IP', 'autonode' ); ?></small></label>
                <textarea name="ip_whitelist" rows="3" class="amp-input amp-textarea" placeholder="<?php esc_attr_e( "203.0.113.0/24\n198.51.100.42", 'autonode' ); ?>"></textarea>
            </div>
        </div>

        <div class="amp-form-group amp-form-full">
            <label><?php esc_html_e( 'Scope Preset', 'autonode' ); ?></label>
            <div class="amp-preset-row">
                <button type="button" class="amp-preset-btn" data-preset="readonly"><?php esc_html_e( 'Read Only', 'autonode' ); ?></button>
                <button type="button" class="amp-preset-btn active" data-preset="writer"><?php esc_html_e( 'Content Writer', 'autonode' ); ?></button>
                <button type="button" class="amp-preset-btn" data-preset="full_access"><?php esc_html_e( 'Full Access', 'autonode' ); ?></button>
            </div>
        </div>

        <div class="amp-form-group amp-form-full">
            <label><?php esc_html_e( 'Permissions', 'autonode' ); ?></label>
            <div class="amp-scopes-grid">
                <?php
                $autonode_groups = array(
                    __( 'Posts', 'autonode' )    => array( 'posts:read', 'posts:write', 'posts:delete', 'posts:publish' ),
                    __( 'Pages', 'autonode' )    => array( 'pages:read', 'pages:write', 'pages:delete' ),
                    __( 'SEO', 'autonode' )      => array( 'seo:read', 'seo:write' ),
                    __( 'Media', 'autonode' )    => array( 'media:read', 'media:write', 'media:delete' ),
                    __( 'Taxonomy', 'autonode' ) => array( 'taxonomy:read', 'taxonomy:write' ),
                    __( 'Advanced', 'autonode' ) => array( 'bulk:write', 'webhooks:read', 'webhooks:write', 'analytics:read', 'keys:read', 'system:read' ),
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
            <button type="submit" class="amp-btn amp-btn-primary" id="amp-create-key-btn"><?php esc_html_e( 'Generate API Key', 'autonode' ); ?></button>
        </div>
    </form>

    <div id="amp-new-key-result" style="display:none" class="amp-new-key-box">
        <div class="amp-new-key-header"><strong><?php esc_html_e( 'Key Created - Copy it now!', 'autonode' ); ?></strong></div>
        <div class="amp-key-display-row">
            <code id="amp-raw-key"></code>
            <button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-raw-key"><?php esc_html_e( 'Copy', 'autonode' ); ?></button>
        </div>
        <div class="amp-key-display-row" style="margin-top:8px">
            <span style="font-size:12px;color:var(--amp-muted)"><?php esc_html_e( 'n8n value:', 'autonode' ); ?></span>
            <code id="amp-n8n-val"></code>
            <button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-n8n-val"><?php esc_html_e( 'Copy', 'autonode' ); ?></button>
        </div>
        <p style="font-size:12px;color:var(--amp-yellow);margin:10px 0 0"><?php esc_html_e( 'This key is shown once. After dismissal it cannot be retrieved.', 'autonode' ); ?></p>
        <button class="amp-btn amp-btn-secondary" style="margin-top:12px" id="amp-dismiss-key"><?php esc_html_e( "I've saved it - Dismiss", 'autonode' ); ?></button>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header">
        <h3><?php esc_html_e( 'All API Keys', 'autonode' ); ?></h3>
        <label class="amp-toggle-wrap"><input type="checkbox" id="amp-show-revoked"> <?php esc_html_e( 'Show revoked', 'autonode' ); ?></label>
    </div>
    <table class="amp-table amp-keys-table">
        <thead><tr>
            <th><?php esc_html_e( 'Key', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Label', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Env', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Scopes', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Requests', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Last Used', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Expires', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Status', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Action', 'autonode' ); ?></th>
        </tr></thead>
        <tbody>
        <?php if ( empty( $keys ) ) : ?>
            <tr><td colspan="9" class="amp-empty-cell"><?php esc_html_e( 'No keys yet.', 'autonode' ); ?></td></tr>
        <?php else : ?>
        <?php foreach ( $keys as $autonode_k ) :
            $autonode_scopes    = json_decode( $autonode_k['scopes'], true ) ?: array();
            $autonode_is_active = ! $autonode_k['revoked'] && ( ! $autonode_k['expires_at'] || strtotime( $autonode_k['expires_at'] ) > time() );
        ?>
        <tr class="amp-key-row <?php echo esc_attr( $autonode_k['revoked'] ? 'amp-row-revoked' : '' ); ?>" data-revoked="<?php echo (int) $autonode_k['revoked']; ?>">
            <td><code class="amp-key-prefix"><?php echo esc_html( $autonode_k['key_prefix'] ); ?></code></td>
            <td>
                <strong><?php echo esc_html( $autonode_k['label'] ); ?></strong>
                <?php if ( $autonode_k['description'] ) : ?>
                    <br><small class="amp-muted"><?php echo esc_html( substr( $autonode_k['description'], 0, 50 ) ); ?></small>
                <?php endif; ?>
            </td>
            <td><span class="amp-env-badge amp-env-<?php echo esc_attr( $autonode_k['environment'] ); ?>"><?php echo esc_html( $autonode_k['environment'] ); ?></span></td>
            <td>
                <div class="amp-scopes-compact">
                    <?php 
                        if ( empty( $autonode_scopes ) ) {
                            printf( '<span class="amp-scope-tag amp-scope-all">%s</span>', esc_html__( 'All', 'autonode' ) );
                        } else {
                            foreach ( array_slice( $autonode_scopes, 0, 3 ) as $autonode_s ) {
                                printf( '<span class="amp-scope-tag">%s</span>', esc_html( $autonode_s ) );
                            }
                            if ( count( $autonode_scopes ) > 3 ) {
                                printf( '<span class="amp-muted">+%d</span>', count( $autonode_scopes ) - 3 );
                            }
                        }
                    ?>
                </div>
            </td>
            <td><?php echo esc_html( number_format( (int) $autonode_k['total_requests'] ) ); ?></td>
            <td><small><?php echo $autonode_k['last_used_at'] ? esc_html( human_time_diff( strtotime( $autonode_k['last_used_at'] ) ) . ' ' . __( 'ago', 'autonode' ) ) : ''; ?></small></td>
            <td><small><?php echo $autonode_k['expires_at'] ? esc_html( substr( $autonode_k['expires_at'], 0, 10 ) ) : ''; ?></small></td>
            <td>
                <?php 
                    if ( $autonode_k['revoked'] ) {
                        echo '<span class="amp-badge amp-badge-revoked">' . esc_html__( 'Revoked', 'autonode' ) . '</span>';
                    } elseif ( ! $autonode_is_active ) {
                        echo '<span class="amp-badge amp-badge-expired">' . esc_html__( 'Expired', 'autonode' ) . '</span>';
                    } else {
                        echo '<span class="amp-badge amp-badge-active">' . esc_html__( 'Active', 'autonode' ) . '</span>';
                    }
                ?>
            </td>
            <td>
                <?php if ( ! $autonode_k['revoked'] ) : ?>
                <button class="amp-btn amp-btn-ghost-sm amp-rotate-btn" data-id="<?php echo (int) $autonode_k['id']; ?>" data-label="<?php echo esc_attr( $autonode_k['label'] ); ?>" style="margin-right:4px" title="<?php esc_attr_e( 'Generate new secret, keep same ID/scopes', 'autonode' ); ?>"> <?php esc_html_e( 'Rotate', 'autonode' ); ?></button>
                <button class="amp-btn amp-btn-danger-sm amp-revoke-btn" data-id="<?php echo (int) $autonode_k['id']; ?>" data-label="<?php echo esc_attr( $autonode_k['label'] ); ?>"><?php esc_html_e( 'Revoke', 'autonode' ); ?></button>
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
        
        var $btn = $('#amp-create-key-btn').prop('disabled', true).text('<?php esc_html_e( 'Generating...', 'autonode' ); ?>');
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
                alert("<?php esc_html_e( 'Server Error:', 'autonode' ); ?> " + (res.data && res.data.message ? res.data.message : "<?php esc_html_e( 'Unknown error inside plugin.', 'autonode' ); ?>"));
            }
        })
        .fail(function(xhr, status, error) {
            alert("<?php esc_html_e( 'AJAX Request Failed!', 'autonode' ); ?>\n\n<?php esc_html_e( 'Status:', 'autonode' ); ?> " + status + "\n<?php esc_html_e( 'Error:', 'autonode' ); ?> " + error + "\n\n<?php esc_html_e( 'Server Response:', 'autonode' ); ?> " + xhr.responseText);
        })
        .always(function() {
            $btn.prop('disabled', false).text('Generate API Key');
        });
    });
});
</script>
