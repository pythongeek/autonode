<?php
defined( 'ABSPATH' ) || exit;
$autonode_hourly_json = wp_json_encode( $hourly );
$autonode_hits_json   = wp_json_encode( array_map( fn( $r ) => (int) $r['hits'], $hourly ) );
$autonode_err_json    = wp_json_encode( array_map( fn( $r ) => (int) $r['errors'], $hourly ) );
$autonode_ms_json     = wp_json_encode( array_map( fn( $r ) => $r['total_ms'] && $r['hits'] ? round( $r['total_ms'] / $r['hits'], 1 ) : 0, $hourly ) );
?>
<div class="amp-wrap">
<div class="amp-header">
    <div class="amp-header-left">
        <h1 class="amp-page-title">
            <svg class="amp-logo-animate" width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#28CCCD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            AutoNode WP
        </h1>
        <span class="amp-badge-pill">v<?php echo esc_html( AUTONODE_VERSION ); ?></span>
        <?php $autonode_sp = \AutoNode\Rankmath_Handler::active_plugin(); ?>
        <span class="amp-badge-pill amp-pill-<?php echo esc_attr( $autonode_sp ); ?>">
            <?php echo 'rankmath' === $autonode_sp ? ' Rank Math' : ( 'yoast' === $autonode_sp ? ' Yoast' : ' No SEO Plugin' ); ?>
        </span>
        <?php if ( $compat['is_agentic'] ) : ?>
        <span class="amp-badge-pill amp-pill-ok"> Agentic Pro </span>
        <?php endif; ?>
    </div>
    <div class="amp-header-right">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-keys' ) ); ?>" class="amp-btn amp-btn-primary">+ New API Key</a>
    </div>
</div>

<div class="amp-card amp-hero-card">
    <div class="amp-card-body">
        <h2 style="margin-top:0; color:var(--amp-primary);">🚀 The Enterprise Bridge for n8n-First Automation</h2>
        <p style="font-size:16px; color:var(--amp-dim);">
            AutoNode WP is the missing link between AI intelligence and WordPress execution. 
            Scale your content kingdom with zero manual entry and industrial-grade security.
        </p>
        <div class="amp-badge-pill amp-pill-ok">Your WordPress, now with Superpowers</div>
    </div>
</div>

<!-- Stats -->
<div class="amp-grid-stats">
    <?php
    $autonode_cards = array(
        array( 'Requests Today', number_format( $summary['requests_today'] ),  'amp-icon-blue', '<span class="dashicons dashicons-chart-bar"></span>' ),
        array( 'Requests (7d)',  number_format( $summary['requests_week'] ),   'amp-icon-purple', '<span class="dashicons dashicons-chart-line"></span>' ),
        array( 'Errors (24h)',   number_format( $summary['errors_24h'] ),      'amp-icon-red', '<span class="dashicons dashicons-warning"></span>' ),
        array( 'Avg Response',   $summary['avg_response_ms'] . 'ms',           'amp-icon-green', '<span class="dashicons dashicons-clock"></span>' ),
        array( 'Active Keys',    $active_keys,                                  'amp-icon-teal', '<span class="dashicons dashicons-admin-network"></span>' ),
        array( 'WordPress',      get_bloginfo( 'version' ),                     'amp-icon-orange', '<span class="dashicons dashicons-wordpress"></span>' ),
    );
    foreach ( $autonode_cards as list( $autonode_label, $autonode_val, $autonode_cls, $autonode_icon ) ) : ?>
    <div class="amp-stat-card">
        <div class="amp-stat-icon <?php echo esc_attr( $autonode_cls ); ?>"><?php echo wp_kses_post( $autonode_icon ); ?></div>
        <div class="amp-stat-body">
            <span class="amp-stat-value"><?php echo esc_html( $autonode_val ); ?></span>
            <span class="amp-stat-label"><?php echo esc_html( $autonode_label ); ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts -->
<div class="amp-grid-2">
    <div class="amp-card">
        <div class="amp-card-header">
            <h3>Request Volume (24h)</h3>
            <div class="amp-chart-btns">
                <button class="amp-chart-btn active" data-hours="24">24h</button>
                <button class="amp-chart-btn" data-hours="48">48h</button>
                <button class="amp-chart-btn" data-hours="168">7d</button>
            </div>
        </div>
        <div style="height:200px;position:relative"><canvas id="amp-req-chart"></canvas></div>
    </div>
    <div class="amp-card">
        <div class="amp-card-header"><h3>Response Time (ms)</h3></div>
        <div style="height:200px;position:relative"><canvas id="amp-ms-chart"></canvas></div>
    </div>
</div>

<!-- Bottom Row -->
<div class="amp-grid-2">
    <div class="amp-card">
        <div class="amp-card-header"><h3>Top Endpoints (7d)</h3></div>
        <?php if ( empty( $top_ep ) ) : ?>
            <p class="amp-empty">No data yet. Make API requests to see analytics.</p>
        <?php else : ?>
        <table class="amp-table">
            <thead><tr><th>Endpoint</th><th>Method</th><th>Hits</th><th>Avg ms</th></tr></thead>
            <tbody>
            <?php foreach ( $top_ep as $autonode_ep ) : ?>
            <tr>
                <td><code><?php echo esc_html( $autonode_ep['endpoint'] ); ?></code></td>
                <td><span class="amp-method amp-method-<?php echo esc_attr( strtolower( $autonode_ep['method'] ) ); ?>"><?php echo esc_html( $autonode_ep['method'] ); ?></span></td>
                <td><strong><?php echo esc_html( number_format( (int) $autonode_ep['hits'] ) ); ?></strong></td>
                <td><?php echo $autonode_ep['avg_ms'] ? esc_html( round( (float) $autonode_ep['avg_ms'], 1 ) . 'ms' ) : ''; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="amp-card">
        <div class="amp-card-header">
            <h3>Active API Keys</h3>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-keys' ) ); ?>" class="amp-link">Manage </a>
        </div>
        <?php $autonode_active = array_filter( $keys, fn( $k ) => ! $k['revoked'] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
        <?php if ( empty( $autonode_active ) ) : ?>
            <p class="amp-empty"><a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-keys' ) ); ?>">Create your first API key </a></p>
        <?php else : ?>
        <table class="amp-table">
            <thead><tr><th>Key</th><th>Label</th><th>Requests</th><th>Last Used</th></tr></thead>
            <tbody>
            <?php foreach ( array_slice( $autonode_active, 0, 6 ) as $autonode_k ) : ?>
            <tr>
                <td><code class="amp-key-prefix"><?php echo esc_html( $autonode_k['key_prefix'] ); ?></code></td>
                <td><span class="amp-env-dot amp-env-<?php echo esc_attr( $autonode_k['environment'] ); ?>"></span><?php echo esc_html( $autonode_k['label'] ); ?></td>
                <td><?php echo esc_html( number_format( (int) $autonode_k['total_requests'] ) ); ?></td>
                <td><small><?php echo $autonode_k['last_used_at'] ? esc_html( human_time_diff( strtotime( $autonode_k['last_used_at'] ) ) . ' ago' ) : 'Never'; ?></small></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- n8n Quick Connect -->
<div class="amp-card">
    <div class="amp-card-header"><h3> n8n Quick Connect</h3></div>
    <div class="amp-grid-3">
        <div class="amp-step"><span class="amp-step-num">1</span>
            <div><strong>Add Header Auth Credential</strong><p>In n8n  Credentials  Header Auth</p>
            <code>Name: Authorization<br>Value: Bearer ampcm_&lt;key&gt;</code></div>
        </div>
        <div class="amp-step"><span class="amp-step-num">2</span>
            <div><strong>Set Base URL</strong><p>HTTP Request node URL:</p>
            <code><?php echo esc_html( get_site_url() . '/wp-json/' . AUTONODE_NS ); ?></code></div>
        </div>
        <div class="amp-step"><span class="amp-step-num">3</span>
            <div><strong>POST /posts with inline SEO</strong>
            <p>Send title, content, categories, tags, seo fields in one call.</p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=autonode-docs' ) ); ?>" class="amp-link">Full Docs </a></div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    var cOpts={responsive:true,maintainAspectRatio:false,interaction:{intersect:false,mode:'index'},plugins:{legend:{labels:{color:'rgba(255,255,255,.7)',font:{size:11}}},tooltip:{backgroundColor:'#1a1f36',titleColor:'#fff',bodyColor:'rgba(255,255,255,.8)',borderColor:'rgba(40,204,205,.3)',borderWidth:1}},scales:{x:{ticks:{color:'rgba(255,255,255,.5)',font:{size:10},maxTicksLimit:12},grid:{color:'rgba(255,255,255,.05)'}},y:{ticks:{color:'rgba(255,255,255,.5)',font:{size:11}},grid:{color:'rgba(255,255,255,.05)'},beginAtZero:true}}};
    var h=<?php echo wp_json_encode( $hourly ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output for JS context ?>,hits=<?php echo wp_json_encode( array_map( fn( $r ) => (int) $r['hits'], $hourly ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>,errs=<?php echo wp_json_encode( array_map( fn( $r ) => (int) $r['errors'], $hourly ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>,ms=<?php echo wp_json_encode( array_map( fn( $r ) => $r['total_ms'] && $r['hits'] ? round( $r['total_ms'] / $r['hits'], 1 ) : 0, $hourly ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
    var labs=h.map(function(r){return r.bucket_hour.slice(11,16)});
    var rc=document.getElementById('amp-req-chart');
    if(rc) window.ampReqChart=new Chart(rc,{type:'bar',data:{labels:labs,datasets:[{label:'Requests',data:hits,backgroundColor:'rgba(40,204,205,.6)',borderColor:'#28CCCD',borderWidth:1,borderRadius:3},{label:'Errors',data:errs,backgroundColor:'rgba(239,68,68,.5)',borderColor:'#ef4444',borderWidth:1,borderRadius:3}]},options:cOpts});
    var mc=document.getElementById('amp-ms-chart');
    if(mc) window.ampMsChart=new Chart(mc,{type:'line',data:{labels:labs,datasets:[{label:'Avg ms',data:ms,fill:true,backgroundColor:'rgba(199,125,255,.15)',borderColor:'#c77dff',borderWidth:2,pointBackgroundColor:'#c77dff',pointRadius:2,tension:.4}]},options:cOpts});
    document.querySelectorAll('.amp-chart-btn').forEach(function(btn){
        btn.addEventListener('click',function(){
            document.querySelectorAll('.amp-chart-btn').forEach(function(b){b.classList.remove('active')});
            this.classList.add('active');
            jQuery.post(ampCM.ajaxUrl,{action:'autonode_get_chart_data',nonce:ampCM.nonce,hours:this.dataset.hours},function(res){
                if(!res.success||!window.ampReqChart)return;
                var d=res.data.hourly,l=d.map(function(r){return r.bucket_hour.slice(11,16)});
                window.ampReqChart.data.labels=l;
                window.ampReqChart.data.datasets[0].data=d.map(function(r){return parseInt(r.hits)});
                window.ampReqChart.data.datasets[1].data=d.map(function(r){return parseInt(r.errors)});
                window.ampReqChart.update();
                window.ampMsChart.data.labels=l;
                window.ampMsChart.data.datasets[0].data=d.map(function(r){return r.total_ms&&r.hits?Math.round(r.total_ms/r.hits*10)/10:0});
                window.ampMsChart.update();
            });
        });
    });
});
</script>

