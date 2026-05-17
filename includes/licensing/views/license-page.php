<?php defined( 'ABSPATH' ) || exit;
$status = $status ?? [];
$autonode_is_license_valid = $status['is_valid'] ?? false;
?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title"><?php esc_html_e( 'License Activation', 'autonode-pro'); ?></h1>
        <span class="amp-badge-pill amp-pill-<?php echo $autonode_is_license_valid ? 'green' : 'red'; ?>">
            <?php echo $autonode_is_license_valid ? esc_html__( 'Active', 'autonode-pro') : esc_html__( 'Inactive', 'autonode-pro'); ?>
        </span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'License Status', 'autonode-pro'); ?></h3></div>
    <div class="amp-card-body">
        <?php if ( $autonode_is_license_valid ) : ?>
            <div class="amp-alert amp-alert-success">
                <strong><?php esc_html_e( 'Your license is active.', 'autonode-pro'); ?></strong>
            </div>
            <table class="amp-table" style="margin-top:16px">
                <tr><td><?php esc_html_e( 'Purchase Code', 'autonode-pro'); ?></td><td><code><?php echo esc_html( $status['purchase_code'] ?? '' ); ?></code></td></tr>
                <tr><td><?php esc_html_e( 'Item', 'autonode-pro'); ?></td><td><?php echo esc_html( $status['item_name'] ?: 'AutoNode WP Pro' ); ?></td></tr>
                <tr><td><?php esc_html_e( 'Buyer', 'autonode-pro'); ?></td><td><?php echo esc_html( $status['buyer'] ?: __( 'N/A', 'autonode-pro') ); ?></td></tr>
                <tr><td><?php esc_html_e( 'Activated', 'autonode-pro'); ?></td><td><?php echo $status['activated_at'] ? esc_html( human_time_diff( $status['activated_at'] ) . ' ' . __( 'ago', 'autonode-pro') ) : esc_html__( 'N/A', 'autonode-pro'); ?></td></tr>
                <tr><td><?php esc_html_e( 'Support Until', 'autonode-pro'); ?></td><td><?php echo $status['supported_until'] ? esc_html( $status['supported_until'] ) : esc_html__( 'N/A', 'autonode-pro'); ?></td></tr>
            </table>
            <div style="margin-top:20px">
                <button type="button" id="amp-deactivate-license" class="amp-btn amp-btn-danger button"><?php esc_html_e( 'Deactivate License', 'autonode-pro'); ?></button>
            </div>
        <?php else : ?>
            <?php if ( ( $status['status'] ?? '' ) === 'pending' ) : ?>
                <div class="amp-alert amp-alert-warning">
                    <strong><?php esc_html_e( 'License verification pending.', 'autonode-pro'); ?></strong>
                    <?php
					/* translators: %d: number of days */
					printf( esc_html__( ' Grace period: %d days remaining.', 'autonode-pro'), (int) ( $status['grace_remaining'] ?? 0 ) );
					?>
                </div>
            <?php else : ?>
                <div class="amp-alert amp-alert-info">
                    <?php esc_html_e( 'Enter your CodeCanyon purchase code to activate AutoNode Pro.', 'autonode-pro'); ?>
                </div>
            <?php endif; ?>

            <form id="amp-activate-license-form" style="margin-top:20px">
                <div class="amp-form-grid">
                    <div class="amp-form-group">
                        <label><?php esc_html_e( 'Purchase Code', 'autonode-pro'); ?></label>
                        <input type="text" name="purchase_code" class="amp-input" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" style="font-family:var(--amp-mono)" required>
                        <p class="amp-help-text"><?php esc_html_e( 'Found in your CodeCanyon download page or purchase confirmation email.', 'autonode-pro'); ?></p>
                    </div>
                    <div class="amp-form-group">
                        <label><?php esc_html_e( 'Buyer Email (optional)', 'autonode-pro'); ?></label>
                        <input type="email" name="buyer_email" class="amp-input" placeholder="you@example.com">
                    </div>
                </div>
                <div style="margin-top:16px">
                    <button type="submit" id="amp-activate-btn" class="amp-btn amp-btn-primary button button-primary"><?php esc_html_e( 'Activate License', 'autonode-pro'); ?></button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="amp-card" style="margin-top:20px">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Where to Find Your Purchase Code', 'autonode-pro'); ?></h3></div>
    <div class="amp-card-body">
        <ol style="margin:0;padding-left:20px;line-height:1.8">
            <li><?php esc_html_e( 'Log in to your CodeCanyon account.', 'autonode-pro'); ?></li>
            <li><?php esc_html_e( 'Go to "Downloads" in your profile.', 'autonode-pro'); ?></li>
            <li><?php esc_html_e( 'Find AutoNode WP Pro and click the download icon.', 'autonode-pro'); ?></li>
            <li><?php esc_html_e( 'Select "License Certificate & Purchase Code".', 'autonode-pro'); ?></li>
            <li><?php esc_html_e( 'Copy the purchase code (format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).', 'autonode-pro'); ?></li>
        </ol>
    </div>
</div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#amp-activate-license-form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#amp-activate-btn').prop('disabled', true).text('<?php echo esc_js( __( 'Activating...', 'autonode-pro') ); ?>');
        $.post(ampCM.ajaxUrl, {
            action: 'autonode_activate_license',
            nonce: ampCM.nonce,
            purchase_code: $('[name=purchase_code]').val(),
            buyer_email: $('[name=buyer_email]').val()
        }).done(function(res) {
            if (res.success) {
                alert('<?php echo esc_js( __( 'License activated successfully!', 'autonode-pro') ); ?>');
                location.reload();
            } else {
                alert(res.data?.message || '<?php echo esc_js( __( 'Activation failed.', 'autonode-pro') ); ?>');
            }
        }).fail(function() {
            alert('<?php echo esc_js( __( 'Request failed. Please try again.', 'autonode-pro') ); ?>');
        }).always(function() {
            $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Activate License', 'autonode-pro') ); ?>');
        });
    });

    $('#amp-deactivate-license').on('click', function() {
        if (!confirm('<?php echo esc_js( __( 'Deactivate this license? You can reactivate it later.', 'autonode-pro') ); ?>')) return;
        var $btn = $(this).prop('disabled', true).text('<?php echo esc_js( __( 'Deactivating...', 'autonode-pro') ); ?>');
        $.post(ampCM.ajaxUrl, {
            action: 'autonode_deactivate_license',
            nonce: ampCM.nonce
        }).done(function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.data?.message || '<?php echo esc_js( __( 'Deactivation failed.', 'autonode-pro') ); ?>');
            }
        }).fail(function() {
            alert('<?php echo esc_js( __( 'Request failed.', 'autonode-pro') ); ?>');
        }).always(function() {
            $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Deactivate License', 'autonode-pro') ); ?>');
        });
    });
});
</script>
