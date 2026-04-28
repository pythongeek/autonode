<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title">Activity Log</h1>
        <span class="amp-badge-pill"><?php echo count( $logs ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?> entries</span>
    </div>
</div>
<div class="amp-card" style="padding:14px 20px;margin-bottom:16px">
    <form method="get" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <input type="hidden" name="page" value="autonode-logs">
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
        $autonode_status_filter = isset( $_GET['status'] ) ? absint( wp_unslash( $_GET['status'] ) ) : 0;
        ?>
        <select name="status" class="amp-select" style="max-width:160px;padding:7px 10px;font-size:12px" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach ( array( 200, 201, 400, 401, 403, 404, 429, 500 ) as $autonode_s ) : ?>
            <option value="<?php echo esc_attr( $autonode_s ); ?>" <?php selected( $autonode_status_filter, $autonode_s ); ?>>HTTP <?php echo esc_html( $autonode_s ); ?></option>
            <?php endforeach; ?>
        </select>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-logs' ) ); ?>" class="amp-btn amp-btn-ghost" style="padding:6px 12px;font-size:12px">Clear</a>
    </form>
</div>
<div class="amp-card" style="padding:0;overflow:hidden">
    <table class="amp-table amp-log-table">
        <thead><tr><th>Time (UTC)</th><th>Key</th><th>Action</th><th>Object</th><th>Method</th><th>Status</th><th>Duration</th><th>IP</th></tr></thead>
        <tbody>
        <?php if ( empty( $logs ) ) : ?>
            <tr><td colspan="8" class="amp-empty-cell">No log entries.</td></tr>
        <?php else : ?>
            <?php
            foreach ( $logs as $autonode_log ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                $autonode_st  = (int) $autonode_log['http_status'];
                $autonode_cls = $autonode_st >= 500 ? 'amp-5xx' : ( $autonode_st >= 400 ? 'amp-4xx' : ( $autonode_st >= 300 ? 'amp-3xx' : 'amp-2xx' ) );
            ?>
            <tr>
                <td><small class="amp-mono"><?php echo esc_html( substr( $autonode_log['created_at'], 0, 19 ) ); ?></small></td>
                <td><?php echo $autonode_log['key_id'] ? '<span class="amp-mono amp-muted">#' . esc_html( $autonode_log['key_id'] ) . '</span>' : ''; ?></td>
                <td><code class="amp-action-code"><?php echo esc_html( $autonode_log['action'] ); ?></code></td>
                <td><?php
                if ( $autonode_log['object_type'] && $autonode_log['object_id'] ) :
                    $autonode_el = get_edit_post_link( (int) $autonode_log['object_id'] );
                    echo $autonode_el ? '<a href="' . esc_url( $autonode_el ) . '" target="_blank" class="amp-link">' . esc_html( $autonode_log['object_type'] . ' #' . $autonode_log['object_id'] ) . '</a>' : '<span class="amp-muted">' . esc_html( $autonode_log['object_type'] . ' #' . $autonode_log['object_id'] ) . '</span>';
                else :
                    echo '';
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
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination, no state change.
        $autonode_off = isset( $_GET['offset'] ) ? absint( wp_unslash( $_GET['offset'] ) ) : 0;
        ?>
        <?php if ( $autonode_off > 0 ) : ?><a href="<?php echo esc_url( add_query_arg( 'offset', max( 0, $autonode_off - 100 ) ) ); ?>" class="amp-btn amp-btn-ghost" style="font-size:12px"> Prev</a><?php endif; ?>
        <?php if ( count( $logs ) === 100 ) : ?><a href="<?php echo esc_url( add_query_arg( 'offset', $autonode_off + 100 ) ); ?>" class="amp-btn amp-btn-ghost" style="font-size:12px">Next </a><?php endif; ?>
    </div>
</div>
</div>

