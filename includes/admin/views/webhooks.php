<?php
/*  webhooks.php  */
defined( 'ABSPATH' ) || exit;
?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title">Webhooks</h1>
        <span class="amp-badge-pill"><?php echo count(array_filter($webhooks,fn($w)=>$w['active'])); ?> active</span>
    </div>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3>Register Webhook</h3></div>
    <div class="amp-alert amp-alert-info">Webhooks fire via WP-Cron. For instant delivery add a real server cron: <code>*/1 * * * * php /path/to/wp-cron.php</code></div>
    <form id="amp-create-webhook-form">
        <div class="amp-form-grid">
            <div class="amp-form-group"><label>Label *</label><input type="text" name="label" placeholder="e.g. n8n Post Published" class="amp-input" required></div>
            <div class="amp-form-group"><label>Target URL *</label><input type="url" name="target_url" placeholder="https://your-n8n.com/webhook/" class="amp-input" required></div>
            <div class="amp-form-group"><label>Signing Secret</label><input type="password" name="secret" placeholder="Optional  used for X-autonode-Signature" class="amp-input"></div>
        </div>
        <div class="amp-form-grid">
            <div class="amp-form-group">
                <label>Events</label>
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
                <label>Post Types</label>
                <div class="amp-checkbox-grid">
                    <label class="amp-checkbox-item"><input type="checkbox" name="post_types[]" value="post" checked> Posts</label>
                    <label class="amp-checkbox-item"><input type="checkbox" name="post_types[]" value="page" checked> Pages</label>
                </div>
            </div>
        </div>
        <div class="amp-form-actions"><button type="submit" class="amp-btn amp-btn-primary">Register Webhook</button></div>
    </form>
</div>

<div class="amp-card">
    <div class="amp-card-header"><h3>Registered Webhooks</h3></div>
    <?php if (empty($webhooks)) : ?>
        <p class="amp-empty">No webhooks yet. Register one above.</p>
    <?php else : ?>
    <table class="amp-table">
        <thead><tr><th>Label</th><th>URL</th><th>Events</th><th>Active</th><th>Fires</th><th>Last Fire</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ( $webhooks as $autonode_wh ) :
            $autonode_events = json_decode( $autonode_wh['events'], true ) ?: array();
        ?>
        <tr data-id="<?php echo (int) $autonode_wh['id']; ?>">
            <td><strong><?php echo esc_html( $autonode_wh['label'] ); ?></strong></td>
            <td><code style="font-size:11px" title="<?php echo esc_attr( $autonode_wh['target_url'] ); ?>"><?php echo esc_html( substr( $autonode_wh['target_url'], 0, 40 ) ); ?></code></td>
            <td><?php foreach ( $autonode_events as $autonode_ev ) { echo '<span class="amp-scope-tag">' . esc_html( $autonode_ev ) . '</span>'; } ?></td>
            <td>
                <label class="amp-toggle"><input type="checkbox" class="amp-wh-toggle" data-id="<?php echo (int) $autonode_wh['id']; ?>" <?php echo $autonode_wh['active'] ? 'checked' : ''; ?>><span class="amp-toggle-slider"></span></label>
                <?php if ( $autonode_wh['last_status'] && $autonode_wh['last_status'] >= 400 ) { echo '<span class="amp-badge amp-badge-revoked" style="margin-left:5px">HTTP ' . esc_html( (int) $autonode_wh['last_status'] ) . '</span>'; } elseif ( $autonode_wh['last_status'] ) { echo '<span class="amp-badge amp-badge-active" style="margin-left:5px">HTTP ' . esc_html( (int) $autonode_wh['last_status'] ) . '</span>'; } ?>
            </td>
            <td><?php echo esc_html( number_format( (int) $autonode_wh['fire_count'] ) ); ?><?php if ( $autonode_wh['fail_count'] > 0 ) { echo ' <span class="amp-muted">/ ' . esc_html( (int) $autonode_wh['fail_count'] ) . ' fails</span>'; } ?></td>
            <td><small><?php echo $autonode_wh['last_fired_at'] ? esc_html( human_time_diff( strtotime( $autonode_wh['last_fired_at'] ) ) . ' ago' ) : 'Never'; ?></small></td>
            <td>
                <button class="amp-btn amp-btn-ghost-sm amp-view-log" data-id="<?php echo (int) $autonode_wh['id']; ?>" data-label="<?php echo esc_attr( $autonode_wh['label'] ); ?>">Log</button>
                <button class="amp-btn amp-btn-ghost-sm amp-test-wh" data-id="<?php echo (int) $autonode_wh['id']; ?>">Test</button>
                <button class="amp-btn amp-btn-danger-sm amp-del-wh" data-id="<?php echo (int) $autonode_wh['id']; ?>">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>



<div class="amp-card">
    <div class="amp-card-header">
        <h3>Recent Deliveries</h3>
        <span class="amp-badge-pill" id="amp-delivery-count"></span>
    </div>
    <div id="amp-delivery-log">
        <p class="amp-empty">Click "View Log" on a webhook above to see its delivery history.</p>
    </div>
</div>

<script>
jQuery(function($){
    $(document).on('click','.amp-view-log',function(){
        var id=$(this).data('id'), label=$(this).data('label');
        $('#amp-delivery-log').html('<p class="amp-empty">Loading</p>');
        $.get(ampCM.apiBase+'/webhooks/'+id+'/deliveries',{},{},function(){}).then(function(){}).catch(function(){});
        // Use admin AJAX since we need admin auth
        $.ajax({
            url: ampCM.apiBase+'/webhooks/'+id+'/deliveries',
            headers: { 'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>' },
            success: function(r) {
                if(!r.success && !r.deliveries) { $('#amp-delivery-log').html('<p class="amp-empty">No data.</p>'); return; }
                var rows = r.data ? r.data.deliveries : r.deliveries;
                if(!rows||!rows.length){$('#amp-delivery-log').html('<p class="amp-empty">No deliveries yet for '+$('<b>').text(label).prop('outerHTML')+'.</p>');return;}
                $('#amp-delivery-count').text(rows.length);
                var html='<table class="amp-table"><thead><tr><th>Time</th><th>Event</th><th>Attempt</th><th>Status</th><th>Duration</th><th>Error</th></tr></thead><tbody>';
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
            error: function(){ $('#amp-delivery-log').html('<p class="amp-empty">Could not load delivery log.</p>'); }
        });
    });
});
</script>
<div id="amp-test-modal" class="amp-modal" style="display:none">
    <div class="amp-modal-box">
        <div class="amp-modal-header"><h3>Webhook Test Result</h3><button class="amp-modal-close"></button></div>
        <pre id="amp-test-result" class="amp-code-block" style="white-space:pre-wrap"></pre>
    </div>
</div>
</div>

