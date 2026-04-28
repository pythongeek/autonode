<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title"><?php esc_html_e( 'Activity Log', 'autonode' ); ?></h1>
        <span class="amp-badge-pill"><?php printf( esc_html__( '%d entries', 'autonode' ), count( $logs ) ); ?></span>
    </div>
    <div class="amp-header-right">
        <button id="amp-clear-logs-btn" class="amp-btn amp-btn-danger"><?php esc_html_e( 'Clear All Logs', 'autonode' ); ?></button>
    </div>
</div>
<div class="amp-card" style="padding:14px 20px;margin-bottom:16px">
    <form method="get" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <input type="hidden" name="page" value="autonode-logs">
        <?php
        $autonode_status_filter = isset( $_GET['status'] ) ? absint( wp_unslash( $_GET['status'] ) ) : 0;
        ?>
        <select name="status" class="amp-select" style="max-width:160px;padding:7px 10px;font-size:12px" onchange="this.form.submit()">
            <option value=""><?php esc_html_e( 'All Statuses', 'autonode' ); ?></option>
            <?php foreach ( array( 200, 201, 400, 401, 403, 404, 429, 500 ) as $autonode_s ) : ?>
            <option value="<?php echo esc_attr( $autonode_s ); ?>" <?php selected( $autonode_status_filter, $autonode_s ); ?>>HTTP <?php echo esc_html( $autonode_s ); ?></option>
            <?php endforeach; ?>
        </select>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-logs' ) ); ?>" class="amp-btn amp-btn-ghost" style="padding:6px 12px;font-size:12px"><?php esc_html_e( 'Clear', 'autonode' ); ?></a>
    </form>
</div>
<div class="amp-card" style="padding:0;overflow:hidden">
    <table class="amp-table amp-log-table">
        <thead><tr>
            <th><?php esc_html_e( 'Time (UTC)', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Key', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Action', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Object', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Method', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Status', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'Duration', 'autonode' ); ?></th>
            <th><?php esc_html_e( 'IP', 'autonode' ); ?></th>
        </tr></thead>
        <tbody>
        <?php if ( empty( $logs ) ) : ?>
            <tr><td colspan="8" class="amp-empty-cell"><?php esc_html_e( 'No log entries.', 'autonode' ); ?></td></tr>
        <?php else : ?>
            <?php
            foreach ( $logs as $autonode_log ) :
                $autonode_st  = (int) $autonode_log['http_status'];
                $autonode_cls = $autonode_st >= 500 ? 'amp-5xx' : ( $autonode_st >= 400 ? 'amp-4xx' : ( $autonode_st >= 300 ? 'amp-3xx' : 'amp-2xx' ) );
            ?>
            <tr>
                <td><small class="amp-mono"><?php echo esc_html( substr( $autonode_log['created_at'], 0, 19 ) ); ?></small></td>
                <td><?php if ( $autonode_log['key_id'] ) : ?><span class="amp-mono amp-muted">#<?php echo esc_html( $autonode_log['key_id'] ); ?></span><?php endif; ?></td>
                <td><code class="amp-action-code"><?php echo esc_html( $autonode_log['action'] ); ?></code></td>
                <td><?php
                if ( $autonode_log['object_type'] && $autonode_log['object_id'] ) :
                    $autonode_el = get_edit_post_link( (int) $autonode_log['object_id'] );
                    if ( $autonode_el ) {
                        printf( '<a href="%s" target="_blank" class="amp-link">%s</a>', esc_url( $autonode_el ), esc_html( $autonode_log['object_type'] . ' #' . $autonode_log['object_id'] ) );
                    } else {
                        printf( '<span class="amp-muted">%s</span>', esc_html( $autonode_log['object_type'] . ' #' . $autonode_log['object_id'] ) );
                    }
                endif;
                ?></td>
                <td><span class="amp-method amp-method-<?php echo esc_attr( strtolower( $autonode_log['method'] ) ); ?>"><?php echo esc_html( $autonode_log['method'] ); ?></span></td>
                <td><span class="amp-http-status <?php echo esc_attr( $autonode_cls ); ?>"><?php echo esc_html( $autonode_st ?: '' ); ?></span></td>
                <td><small><?php echo $autonode_log['duration_ms'] ? esc_html( (int) $autonode_log['duration_ms'] . 'ms' ) : ''; ?></small></td>
                <td><small class="amp-mono amp-muted"><?php echo esc_html( $autonode_log['ip_address'] ); ?></small></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <div style="display:flex;gap:8px;padding:14px 20px;border-top:1px solid var(--amp-border)">
        <?php
        $autonode_off = isset( $_GET['offset'] ) ? absint( wp_unslash( $_GET['offset'] ) ) : 0;
        ?>
        <?php if ( $autonode_off > 0 ) : ?><a href="<?php echo esc_url( add_query_arg( 'offset', max( 0, $autonode_off - 100 ) ) ); ?>" class="amp-btn amp-btn-ghost" style="font-size:12px"><?php esc_html_e( 'Prev', 'autonode' ); ?></a><?php endif; ?>
        <?php if ( count( $logs ) === 100 ) : ?><a href="<?php echo esc_url( add_query_arg( 'offset', $autonode_off + 100 ) ); ?>" class="amp-btn amp-btn-ghost" style="font-size:12px"><?php esc_html_e( 'Next', 'autonode' ); ?></a><?php endif; ?>
    </div>
</div>
</div>

