<?php
/*  webhooks.php  */
defined( 'ABSPATH' ) || exit;
?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title"><?php esc_html_e( 'Webhooks', 'autonode' ); ?></h1>
        <span class="amp-badge-pill"><?php printf( esc_html__( '%d active', 'autonode' ), count( array_filter( $webhooks, fn( $w ) => $w['active'] ) ) ); ?></span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Register Webhook', 'autonode' ); ?></h3></div>
    <div class="amp-alert amp-alert-info">
        <?php 
            printf( 
                /* translators: %s: cron command example */
                esc_html__( 'Webhooks fire via WP-Cron. For instant delivery add a real server cron: %s', 'autonode' ), 
                '<code>*/1 * * * * php /path/to/wp-cron.php</code>' 
            ); 
        ?>
    </div>
    <form id="amp-create-webhook-form">
        <div class="amp-form-grid">
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Label *', 'autonode' ); ?></label>
                <input type="text" name="label" placeholder="<?php esc_attr_e( 'e.g. n8n Post Published', 'autonode' ); ?>" class="amp-input" required>
            </div>
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Target URL *', 'autonode' ); ?></label>
                <input type="url" name="target_url" placeholder="https://your-n8n.com/webhook/" class="amp-input" required>
            </div>
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Signing Secret', 'autonode' ); ?></label>
                <input type="password" name="secret" placeholder="<?php esc_attr_e( 'Optional - used for X-autonode-Signature', 'autonode' ); ?>" class="amp-input">
            </div>
        </div>
        <div class="amp-form-grid">
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Events', 'autonode' ); ?></label>
                <div class="amp-checkbox-grid">
                    <?php foreach ( \AutoNode\Webhook_Manager::EVENTS as $autonode_ev ) : ?>
                    <label class="amp-checkbox-item">
                        <input type="checkbox" name="events[]" value="<?php echo esc_attr( $autonode_ev ); ?>" <?php echo in_array( $autonode_ev, array( 'post.published', 'page.published' ), true ) ? 'checked' : ''; ?>>
                        <code><?php echo esc_html( $autonode_ev ); ?></code>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="amp-form-group">
                <label><?php esc_html_e( 'Post Types', 'autonode' ); ?></label>
                <div class="amp-checkbox-grid">
                    <label class="amp-checkbox-item"><input type="checkbox" name="post_types[]" value="post" checked> <?php esc_html_e( 'Posts', 'autonode' ); ?></label>
                    <label class="amp-checkbox-item"><input type="checkbox" name="post_types[]" value="page" checked> <?php esc_html_e( 'Pages', 'autonode' ); ?></label>
                </div>
            </div>
        </div>
        <div class="amp-form-actions"><button type="submit" class="amp-btn amp-btn-primary"><?php esc_html_e( 'Register Webhook', 'autonode' ); ?></button></div>
    </form>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3><?php esc_html_e( 'Registered Webhooks', 'autonode' ); ?></h3></div>
    <?php if ( empty( $webhooks ) ) : ?>
        <p class="amp-empty"><?php esc_html_e( 'No webhooks yet. Register one above.', 'autonode' ); ?></p>
    <?php else : ?>
    <table class="amp-table">
        <thead><tr>
            <th><?php esc_html_e( 'Label', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'URL', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Events', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Active', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Fires', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Last Fire', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Actions', 'autonode' ); ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ( $webhooks as $autonode_wh ) :
            $autonode_events = json_decode( $autonode_wh['events'], true ) ?: array();
        ?>
        <tr data-id="<?php echo (int) $autonode_wh['id']; ?>">
            <td><strong><?php echo esc_html( $autonode_wh['label'] ); ?></strong></td>
            <td><code style="font-size:11px" title="<?php echo esc_attr( $autonode_wh['target_url'] ); ?>"><?php echo esc_html( substr( $autonode_wh['target_url'], 0, 40 ) ); ?></code></td>
            <td>
                <?php 
                    foreach ( $autonode_events as $autonode_ev ) {
                        echo '<span class="amp-scope-tag">' . esc_html( $autonode_ev ) . '</span>';
                    }
                ?>
            </td>
            <td>
                <label class="amp-toggle">
                    <input type="checkbox" class="amp-wh-toggle" data-id="<?php echo (int) $autonode_wh['id']; ?>" <?php checked( (bool) $autonode_wh['active'] ); ?>>
                    <span class="amp-toggle-slider"></span>
                </label>
                <?php 
                    if ( $autonode_wh['last_status'] && $autonode_wh['last_status'] >= 400 ) {
                        echo '<span class="amp-badge amp-badge-revoked" style="margin-left:5px">HTTP ' . esc_html( (int) $autonode_wh['last_status'] ) . '</span>';
                    } elseif ( $autonode_wh['last_status'] ) {
                        echo '<span class="amp-badge amp-badge-active" style="margin-left:5px">HTTP ' . esc_html( (int) $autonode_wh['last_status'] ) . '</span>';
                    }
                ?>
            </td>
            <td>
                <?php echo esc_html( number_format( (int) $autonode_wh['fire_count'] ) ); ?>
                <?php if ( $autonode_wh['fail_count'] > 0 ) : ?>
                    <span class="amp-muted">/ <?php echo esc_html( (int) $autonode_wh['fail_count'] ); ?> <?php esc_html_e( 'fails', 'autonode' ); ?></span>
                <?php endif; ?>
            </td>
            <td><small><?php echo $autonode_wh['last_fired_at'] ? esc_html( human_time_diff( strtotime( $autonode_wh['last_fired_at'] ) ) . ' ' . __( 'ago', 'autonode' ) ) : esc_html__( 'Never', 'autonode' ); ?></small></td>
            <td>
                <button class="amp-btn amp-btn-ghost-sm amp-view-log" data-id="<?php echo (int) $autonode_wh['id']; ?>" data-label="<?php echo esc_attr( $autonode_wh['label'] ); ?>"><?php esc_html_e( 'Log', 'autonode' ); ?></button>
                <button class="amp-btn amp-btn-ghost-sm amp-test-wh" data-id="<?php echo (int) $autonode_wh['id']; ?>"><?php esc_html_e( 'Test', 'autonode' ); ?></button>
                <button class="amp-btn amp-btn-danger-sm amp-del-wh" data-id="<?php echo (int) $autonode_wh['id']; ?>"><?php esc_html_e( 'Delete', 'autonode' ); ?></button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="amp-card">
    <div class="amp-card-header">
        <h3><?php esc_html_e( 'Recent Deliveries', 'autonode' ); ?></h3>
        <span class="amp-badge-pill" id="amp-delivery-count"></span>
    </div>
    <div id="amp-delivery-log">
        <p class="amp-empty"><?php esc_html_e( 'Click "View Log" on a webhook above to see its delivery history.', 'autonode' ); ?></p>
    </div>
</div>

<script>
jQuery(function($){
    $(document).on('click','.amp-view-log',function(){
        var id=$(this).data('id'), label=$(this).data('label');
        $('#amp-delivery-log').html('<p class="amp-empty"><?php esc_html_e( 'Loading...', 'autonode' ); ?></p>');
        $.get(ampCM.apiBase+'/webhooks/'+id+'/deliveries',{},{},function(){}).then(function(){}).catch(function(){});
        // Use admin AJAX since we need admin auth
        $.ajax({
            url: ampCM.apiBase+'/webhooks/'+id+'/deliveries',
            headers: { 'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>' },
            success: function(r) {
                if(!r.success && !r.deliveries) { $('#amp-delivery-log').html('<p class="amp-empty"><?php esc_html_e( 'No data.', 'autonode' ); ?></p>'); return; }
                var rows = r.data ? r.data.deliveries : r.deliveries;
                if(!rows||!rows.length){$('#amp-delivery-log').html('<p class="amp-empty">No deliveries yet for '+$('<b>').text(label).prop('outerHTML')+'.</p>');return;}
                $('#amp-delivery-count').text(rows.length);
                var html='<table class="amp-table"><thead><tr><th><?php esc_html_e( 'Time', 'autonode' ); ?></th><th><?php esc_html_e( 'Event', 'autonode' ); ?></th><th><?php esc_html_e( 'Attempt', 'autonode' ); ?></th><th><?php esc_html_e( 'Status', 'autonode' ); ?></th><th><?php esc_html_e( 'Duration', 'autonode' ); ?></th><th><?php esc_html_e( 'Error', 'autonode' ); ?></th></tr></thead><tbody>';
                rows.forEach(function(row){
                    var ok=row.http_status>=200&&row.http_status<300;
                    html+='<tr><td><small>'+row.created_at.slice(0,19)+'</small></td>';
                    html+='<td><code style="font-size:11px">'+row.event+'</code></td>';
                    html+='<td style="text-align:center">'+row.attempt+'</td>';
                    html+='<td><span class="amp-http-status '+(ok?'amp-2xx':'amp-4xx')+'">'+(row.http_status||'')+'</span></td>';
                    html+='<td><small>'+(row.duration_ms?row.duration_ms+'ms':'')+'</small></td>';
                    html+='<td><small style="color:var(--amp-red)">'+(row.error_message||'')+'</small></td></tr>';
                });
                html+='</tbody></table>';
                $('#amp-delivery-log').html(html);
            },
            error: function(){ $('#amp-delivery-log').html('<p class="amp-empty"><?php esc_html_e( 'Could not load delivery log.', 'autonode' ); ?></p>'); }
        });
    });
});
</script>
<div id="amp-test-modal" class="amp-modal" style="display:none">
    <div class="amp-modal-box">
        <div class="amp-modal-header"><h3><?php esc_html_e( 'Webhook Test Result', 'autonode' ); ?></h3><button class="amp-modal-close"></button></div>
        <pre id="amp-test-result" class="amp-code-block" style="white-space:pre-wrap"></pre>
    </div>
</div>
</div>

