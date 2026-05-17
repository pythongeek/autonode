/* AMP Agency Content Manager Pro — Admin JS v4.1 (Corrected) */
/* global ampCM, jQuery */
jQuery(document).ready(function ($) {
    'use strict';

    // Safety check to ensure the localization script loaded
    if (typeof ampCM === 'undefined') {
        console.error('AMP CM: Localization object (ampCM) is missing.');
        return;
    }

    /* —— Toast ————————————————————————————————————————————————————— */
    $('head').append('<style>.amp-toast{position:fixed;bottom:24px;right:24px;padding:11px 18px;border-radius:8px;font-size:13px;font-weight:600;opacity:0;z-index:999999;max-width:340px;box-shadow:0 8px 30px rgba(0,0,0,.4);transition:opacity .3s;pointer-events:none}.amp-toast.show{opacity:1}.amp-toast-success{background:#22c55e;color:#fff}.amp-toast-error{background:#ef4444;color:#fff}.amp-toast-info{background:#28CCCD;color:#0f1117}</style>');
    function toast(msg, type) {
        type = type || 'success';
        var el = $('<div class="amp-toast amp-toast-' + type + '">' + msg + '</div>').appendTo('body');
        setTimeout(function(){ el.addClass('show'); }, 10);
        setTimeout(function(){ el.removeClass('show'); setTimeout(function(){ el.remove(); }, 300); }, 3200);
    }

    /* —— Scope Presets ————————————————————————————————————————————— */
    $(document).on('click', '.amp-preset-btn', function (e) {
        e.preventDefault();
        $('.amp-preset-btn').removeClass('active');
        $(this).addClass('active');
        var preset = $(this).data('preset');
        var $checks = $('[name="scopes[]"]');
        if (preset === 'full_access') { $checks.prop('checked', false); return; }
        var scopes = (ampCM.presets && ampCM.presets[preset]) ? ampCM.presets[preset] : [];
        $checks.prop('checked', false);
        $checks.each(function () { if (scopes.indexOf($(this).val()) !== -1) $(this).prop('checked', true); });
    });

    /* —— Create Key ———————————————————————————————————————————————— */
    $(document).on('submit', '#amp-create-key-form', function (e) {
        e.preventDefault(); 
        var $form = $(this);
        var $btn  = $('#amp-create-key-btn').prop('disabled', true).text('Generating…');
        var scopes = $('[name="scopes[]"]:checked').map(function () { return $(this).val(); }).get();
        
        var payload = {
            action: 'autonode_create_key', 
            nonce: ampCM.nonce,
            label: $('[name=label]').val(), 
            description: $('[name=description]').val(),
            environment: $('[name=environment]').val(), 
            expires_at: $('[name=expires_at]').val(),
            ip_whitelist: $('[name=ip_whitelist]').val(),
            'scopes[]': scopes,
            preset: $('.amp-preset-btn.active').data('preset') || '',
        };

        $.post(ampCM.ajaxUrl, payload)
        .done(function (res) {
            if (res.success) {
                var raw = res.data.raw_key;
                $('#amp-raw-key').text(raw);
                $('#amp-n8n-val').text('Bearer ' + raw);
                $('#amp-new-key-result').slideDown(250);
                $form[0].reset();
                $('[name="scopes[]"]').prop('checked', false);
                toast('API key created.', 'success');
                $('html,body').animate({ scrollTop: $('#amp-new-key-result').offset().top - 80 }, 400);
            } else { 
                toast((res.data && res.data.message) || 'Error', 'error'); 
            }
        }).fail(function () { 
            toast('Request failed.', 'error');
        }).always(function () { 
            $btn.prop('disabled', false).text('Generate API Key'); 
        });
    });

    /* —— Dismiss new key ———————————————————————————————————————————— */
    $(document).on('click', '#amp-dismiss-key', function (e) {
        e.preventDefault();
        if (confirm('Have you saved the key? It cannot be retrieved.')) {
            $('#amp-new-key-result').slideUp();
            setTimeout(function () { location.reload(); }, 200);
        }
    });

    /* —— Copy buttons —————————————————————————————————————————————— */
    $(document).on('click', '.amp-copy-btn', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var $el = $('#' + target);
        var text = ($el.is('textarea, input') ? $el.val() : $el.text()).trim();
        var $btn = $(this);
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                var orig = $btn.text();
                $btn.text('✅ Copied!');
                setTimeout(function () { $btn.text(orig); }, 2000);
            }).catch(function () { prompt('Copy:', text); });
        } else { prompt('Copy:', text); }
    });

    /* —— Revoke key ———————————————————————————————————————————————— */
    $(document).on('click', '.amp-revoke-btn', function (e) {
        e.preventDefault();
        var $btn = $(this), id = $btn.data('id'), label = $btn.data('label');
        if (!confirm('Revoke "' + label + '"? This cannot be undone.')) return;
        $btn.prop('disabled', true).text('Revoking…');
        $.post(ampCM.ajaxUrl, { action: 'autonode_revoke_key', nonce: ampCM.nonce, key_id: id })
            .done(function (res) {
                if (res.success) {
                    $btn.closest('.amp-key-row').addClass('amp-row-revoked');
                    $btn.closest('td').html('<span class="amp-badge amp-badge-revoked">Revoked</span>');
                    toast('Key revoked.', 'info');
                } else { toast('Failed.', 'error'); $btn.prop('disabled', false).text('Revoke'); }
            }).fail(function () { toast('Failed.', 'error'); $btn.prop('disabled', false).text('Revoke'); });
    });

    /* —— Show revoked toggle ———————————————————————————————————————— */
    $(document).on('change', '#amp-show-revoked', function () {
        this.checked ? $('.amp-row-revoked').show() : $('.amp-row-revoked').hide();
    });

    /* —— Search API Keys ————————————————————————————————————————————— */
    $(document).on('input', '#amp-key-search', function () {
        var q = $(this).val().toLowerCase();
        $('.amp-key-row').each(function () {
            var label = $(this).data('label') || '';
            $(this).toggle(label.indexOf(q) !== -1);
        });
    });

    /* —— Search Webhooks ———————————————————————————————————————————— */
    $(document).on('input', '#amp-wh-search', function () {
        var q = $(this).val().toLowerCase();
        $('[data-id]').each(function () {
            var label = $(this).data('label') || '';
            var row = $(this).closest('tr').length ? $(this).closest('tr') : $(this);
            row.toggle(label.indexOf(q) !== -1);
        });
    });

    /* —— Settings form —————————————————————————————————————————————— */
    $(document).on('submit', '#amp-settings-form', function (e) {
        e.preventDefault();
        var $btn = $(this).find('[type=submit]').prop('disabled', true).text('Saving…');
        var $st  = $('#amp-save-status');
        var formData = $(this).serializeArray();
        var data = { action: 'autonode_save_settings', nonce: ampCM.nonce };
        $.each(formData, function(i, field){
            data[field.name] = field.value;
        });
        // Checkboxes
        $(this).find('input[type=checkbox]').each(function(){
            data[this.name] = this.checked ? 1 : 0;
        });

        $.post(ampCM.ajaxUrl, data).done(function (res) {
            if (res.success) { $st.html('<span style="color:var(--amp-green);font-weight:600">✓ Saved</span>'); toast('Settings saved.', 'success'); }
            else { $st.html('<span style="color:var(--amp-red)">Error</span>'); }
        }).fail(function () { $st.html('<span style="color:var(--amp-red)">Failed</span>'); }
        ).always(function () { $btn.prop('disabled', false).text('Save Settings'); setTimeout(function(){ $st.html(''); }, 3000); });
    });

    /* —— Create Webhook ———————————————————————————————————————————— */
    $(document).on('submit', '#amp-create-webhook-form', function (e) {
        e.preventDefault();
        var $btn = $(this).find('[type=submit]').prop('disabled', true).text('Registering…');
        var events = $('[name="events[]"]:checked').map(function () { return $(this).val(); }).get();
        var types  = $('[name="post_types[]"]:checked').map(function () { return $(this).val(); }).get();
        $.post(ampCM.ajaxUrl, {
            action: 'autonode_create_webhook', nonce: ampCM.nonce,
            label: $('[name=label]').val(), target_url: $('[name=target_url]').val(),
            secret: $('[name=secret]').val(), 'events[]': events, 'post_types[]': types,
        }).done(function (res) {
            if (res.success) { toast('Webhook registered.', 'success'); setTimeout(function(){ location.reload(); }, 600); }
            else { toast((res.data && res.data.message) || 'Error', 'error'); $btn.prop('disabled', false).text('Register Webhook'); }
        }).fail(function () { toast('Failed.', 'error'); $btn.prop('disabled', false).text('Register Webhook'); });
    });

    /* —— Toggle webhook active —————————————————————————————————————— */
    $(document).on('change', '.amp-wh-toggle', function () {
        var id = $(this).data('id'), active = this.checked ? 1 : 0;
        $.post(ampCM.ajaxUrl, { action: 'autonode_toggle_webhook', nonce: ampCM.nonce, id: id, active: active })
            .done(function (res) { if (res.success) toast(active ? 'Webhook enabled.' : 'Webhook disabled.', 'info'); });
    });

    /* —— Delete webhook ———————————————————————————————————————————— */
    $(document).on('click', '.amp-del-wh', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (!confirm('Delete this webhook?')) return;
        $.post(ampCM.ajaxUrl, { action: 'autonode_delete_webhook', nonce: ampCM.nonce, id: id })
            .done(function (res) {
                if (res.success) { $('[data-id=' + id + ']').closest('tr').fadeOut(300, function(){ $(this).remove(); }); toast('Deleted.', 'info'); }
            });
    });

    /* —— Test webhook —————————————————————————————————————————————— */
    $(document).on('click', '.amp-test-wh', function (e) {
        e.preventDefault();
        var id = $(this).data('id'), $mo = $('#amp-test-modal'), $res = $('#amp-test-result');
        $res.css('color', 'var(--amp-muted)').text('Sending test…');
        $mo.show();
        $.post(ampCM.ajaxUrl, { action: 'autonode_test_webhook', nonce: ampCM.nonce, id: id })
            .done(function (res) {
                if (res.success) {
                    var ok = res.data.success;
                    $res.css('color', ok ? 'var(--amp-green)' : 'var(--amp-red)').text(JSON.stringify(res.data, null, 2));
                    toast(ok ? 'Test delivered (HTTP ' + res.data.status + ')' : 'Test failed.', ok ? 'success' : 'error');
                } else { $res.css('color', 'var(--amp-red)').text(JSON.stringify(res.data, null, 2)); }
            }).fail(function () { $res.css('color', 'var(--amp-red)').text('AJAX request failed.'); });
    });

    /* —— Modal close ———————————————————————————————————————————————— */
    $(document).on('click', '.amp-modal-close', function (e) { e.preventDefault(); $('#amp-test-modal').hide(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') $('#amp-test-modal').hide(); });
    $(document).on('click', '#amp-test-modal', function (e) { if ($(e.target).is(this)) $(this).hide(); });

    /* —— Rotate Key ———————————————————————————————————————————————— */
    $(document).on('click', '.amp-rotate-btn', function (e) {
        e.preventDefault();
        var $btn = $(this), id = $btn.data('id'), label = $btn.data('label');
        if (!confirm('Rotate key "' + label + '"?\n\nThe old key stops working immediately. A new key will be shown once — save it before dismissing.')) return;
        $btn.prop('disabled', true).text('Rotating…');
        $.post(ampCM.ajaxUrl, { action: 'autonode_rotate_key', nonce: ampCM.nonce, key_id: id })
            .done(function (res) {
                if (res.success) {
                    var raw = res.data.raw_key;
                    var html = '<div class="amp-new-key-box" style="margin-top:12px">' +
                        '<div class="amp-new-key-header">✅ Key Rotated — New Key:</div>' +
                        '<div class="amp-key-display-row"><code id="amp-rotated-key-' + id + '">' + raw + '</code>' +
                        '<button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-rotated-key-' + id + '">📋 Copy</button></div>' +
                        '<div class="amp-key-display-row" style="margin-top:8px"><span style="font-size:12px;color:var(--amp-muted)">n8n value:</span>' +
                        '<code id="amp-rotated-n8n-' + id + '">Bearer ' + raw + '</code>' +
                        '<button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-rotated-n8n-' + id + '">📋 Copy</button></div>' +
                        '<p style="font-size:12px;color:var(--amp-yellow);margin-top:8px">⚠️ Update your n8n credential immediately.</p></div>';
                    $btn.closest('tr').find('td:last').prepend(html);
                    $btn.remove();
                    $btn.closest('tr').find('.amp-key-prefix').text(res.data.prefix + '…');
                } else {
                    alert('Rotate failed: ' + (res.data && res.data.message ? res.data.message : 'Unknown error'));
                    $btn.prop('disabled', false).text('Rotate');
                }
            }).fail(function () {
                alert('Request failed.');
                $btn.prop('disabled', false).text('Rotate');
            });
    });

    /* —— Clear Logs ———————————————————————————————————————————————— */
    $(document).on('click', '#amp-clear-logs-btn', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete ALL activity and delivery logs? This cannot be undone.')) return;
        var $btn = $(this).prop('disabled', true).text('Clearing...');
        $.post(ampCM.ajaxUrl, { action: 'autonode_clear_logs', nonce: ampCM.nonce })
            .done(function (res) {
                if (res.success) { toast('Logs cleared.', 'info'); setTimeout(function(){ location.reload(); }, 600); }
                else { toast('Clear failed.', 'error'); $btn.prop('disabled', false).text('Clear All Logs'); }
            }).fail(function () { toast('Failed.', 'error'); $btn.prop('disabled', false).text('Clear All Logs'); });
    });

    /* —— Unblock IP (Settings) —————————————————————————————————————— */
    $(document).on('click', '.amp-unblock-ip', function() {
        var ip = $(this).data('ip');
        if (!confirm('Unblock ' + ip + '?')) return;
        $.post(ampCM.ajaxUrl, { action: 'autonode_unblock_ip', nonce: ampCM.nonce, ip: ip }).done(function(r) {
            if (r.success) { toast('IP unblocked.', 'success'); setTimeout(function() { location.reload(); }, 600); }
        });
    });

    /* —— n8n Download ————————————————————————————————————————————— */
    $(document).on('click', '#amp-download-n8n', function (e) {
        e.preventDefault();
        var config = {
            name: 'AutoNode @ ' + window.location.hostname,
            api_url: ampCM.apiBase,
            site_url: window.location.origin,
            instructions: 'Add this to your n8n Global Variables or use in HTTP Request nodes.',
            timestamp: new Date().toISOString()
        };
        var blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
        var url  = window.URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href     = url;
        a.download = 'autonode-config.json';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        toast('Config downloaded.', 'success');
    });

    /* —— Charts (Dashboard) ———————————————————————————————————————— */
    if (typeof Chart !== 'undefined' && ampCM.chart) {
        var cData = ampCM.chart;
        var cOpts = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { labels: { color: 'rgba(255,255,255,.7)', font: { size: 11 } } },
                tooltip: { backgroundColor: '#1a1f36', titleColor: '#fff', bodyColor: 'rgba(255,255,255,.8)', borderColor: 'rgba(40,204,205,.3)', borderWidth: 1 }
            },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,.5)', font: { size: 10 }, maxTicksLimit: 12 }, grid: { color: 'rgba(255,255,255,.05)' } },
                y: { ticks: { color: 'rgba(255,255,255,.5)', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,.05)' }, beginAtZero: true }
            }
        };

        var labs = cData.hourly.map(function(r) { return r.bucket_hour.slice(11, 16); });
        var rc = document.getElementById('amp-req-chart');
        if (rc) {
            window.ampReqChart = new Chart(rc, {
                type: 'bar',
                data: {
                    labels: labs,
                    datasets: [
                        { label: 'Requests', data: cData.hits, backgroundColor: 'rgba(40,204,205,.6)', borderColor: '#28CCCD', borderWidth: 1, borderRadius: 3 },
                        { label: 'Errors', data: cData.errs, backgroundColor: 'rgba(239,68,68,.5)', borderColor: '#ef4444', borderWidth: 1, borderRadius: 3 }
                    ]
                },
                options: cOpts
            });
        }

        var mc = document.getElementById('amp-ms-chart');
        if (mc) {
            window.ampMsChart = new Chart(mc, {
                type: 'line',
                data: {
                    labels: labs,
                    datasets: [
                        { label: 'Avg ms', data: cData.ms, fill: true, backgroundColor: 'rgba(199,125,255,.15)', borderColor: '#c77dff', borderWidth: 2, pointBackgroundColor: '#c77dff', pointRadius: 2, tension: .4 }
                    ]
                },
                options: cOpts
            });
        }

        $(document).on('click', '.amp-chart-btn', function() {
            $('.amp-chart-btn').removeClass('active');
            $(this).addClass('active');
            $.post(ampCM.ajaxUrl, { action: 'autonode_get_chart_data', nonce: ampCM.nonce, hours: this.dataset.hours }, function(res) {
                if (!res.success || !window.ampReqChart) return;
                var d = res.data.hourly, l = d.map(function(r) { return r.bucket_hour.slice(11, 16); });
                window.ampReqChart.data.labels = l;
                window.ampReqChart.data.datasets[0].data = d.map(function(r) { return parseInt(r.hits); });
                window.ampReqChart.data.datasets[1].data = d.map(function(r) { return parseInt(r.errors); });
                window.ampReqChart.update();
                window.ampMsChart.data.labels = l;
                window.ampMsChart.data.datasets[0].data = d.map(function(r) { return r.total_ms && r.hits ? Math.round(r.total_ms / r.hits * 10) / 10 : 0; });
                window.ampMsChart.update();
            });
        });
    }

    /* —— Webhook Deliveries (Webhooks) —————————————————————————————— */
    $(document).on('click', '.amp-view-log', function() {
        var id = $(this).data('id'), label = $(this).data('label');
        $('#amp-delivery-log').html('<p class="amp-empty">Loading...</p>');
        $.ajax({
            url: ampCM.apiBase + '/webhooks/' + id + '/deliveries',
            headers: { 'X-WP-Nonce': ampCM.restNonce },
            success: function(r) {
                var rows = r.data ? r.data.deliveries : r.deliveries;
                if (!rows || !rows.length) { $('#amp-delivery-log').html('<p class="amp-empty">No deliveries yet for ' + $('<b>').text(label).prop('outerHTML') + '.</p>'); return; }
                $('#amp-delivery-count').text(rows.length);
                var html = '<table class="amp-table widefat"><thead><tr><th>Time</th><th>Event</th><th>Attempt</th><th>Status</th><th>Duration</th><th>Error</th></tr></thead><tbody>';
                rows.forEach(function(row) {
                    var ok = row.http_status >= 200 && row.http_status < 300;
                    html += '<tr><td><small>' + row.created_at.slice(0, 19) + '</small></td>';
                    html += '<td><code style="font-size:11px">' + row.event + '</code></td>';
                    html += '<td style="text-align:center">' + row.attempt + '</td>';
                    html += '<td><span class="amp-http-status ' + (ok ? 'amp-2xx' : 'amp-4xx') + '">' + (row.http_status || '') + '</span></td>';
                    html += '<td><small>' + (row.duration_ms ? row.duration_ms + 'ms' : '') + '</small></td>';
                    html += '<td><small style="color:var(--amp-red)">' + (row.error_message || '') + '</small></td></tr>';
                });
                html += '</tbody></table>';
                $('#amp-delivery-log').html(html);
            },
            error: function() { $('#amp-delivery-log').html('<p class="amp-empty">Could not load delivery log.</p>'); }
        });
    });

    /* —— UI Mode Toggle (Settings) —————————————————————————————————— */
    $(document).on('change', '[name=ui_mode]', function() {
        var mode = $(this).val();
        if (mode === 'dark') { $('body').addClass('amp-mode-dark'); }
        else { $('body').removeClass('amp-mode-dark'); }
    });

    /* —— Test Connection (Settings) ————————————————————————————————— */
    $(document).on('click', '#amp-test-conn-btn', function() {
        var $btn = $(this).prop('disabled', true).text('Testing...');
        var key = $('#amp-test-key').val();
        var $res = $('#amp-test-result');
        $res.hide().text('');
        $.ajax({
            url: ampCM.apiBase + '/ping',
            headers: { 'Authorization': 'Bearer ' + key },
            success: function(r) {
                $res.css('color', 'var(--amp-green)').text('✅ Success!\n' + JSON.stringify(r, null, 2)).fadeIn();
                toast('Connection successful.', 'success');
            },
            error: function(r) {
                var msg = r.responseJSON && r.responseJSON.message ? r.responseJSON.message : 'Unauthorized';
                $res.css('color', 'var(--amp-red)').text('❌ Failed: ' + msg).fadeIn();
                toast('Connection failed.', 'error');
            },
            always: function() { $btn.prop('disabled', false).text('Test Connection'); }
        });
    });

    /* —— Copy Workflow (n8n Templates) —————————————————————————————— */
    $(document).on('click', '.amp-copy-wf', function(e) {
        e.preventDefault();
        var target = $(this).data('target'), text = $('#' + target).text().trim(), $btn = $(this);
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                var orig = $btn.text();
                $btn.text('✅ Copied JSON');
                setTimeout(function() { $btn.text(orig); }, 2000);
            });
        }
    });

    /* —— Export Settings ———————————————————————————————————————————— */
    $(document).on('click', '#amp-export-settings', function(e) {
        e.preventDefault();
        var $btn = $(this).prop('disabled', true).text('Exporting…');
        $.post(ampCM.ajaxUrl, { action: 'autonode_export_settings', nonce: ampCM.nonce }, function() {
            $btn.prop('disabled', false).text('Export Settings');
        }).fail(function() {
            $btn.prop('disabled', false).text('Export Settings');
            toast('Export failed.', 'error');
        });
    });

    /* —— Import Settings ———————————————————————————————————————————— */
    $(document).on('change', '#amp-import-settings-file', function() {
        var file = this.files[0];
        if (!file) return;
        var $status = $('#amp-import-status');
        var formData = new FormData();
        formData.append('action', 'autonode_import_settings');
        formData.append('nonce', ampCM.nonce);
        formData.append('settings_file', file);
        $status.show().css('color', 'var(--amp-dim)').text('Importing…');
        $.ajax({
            url: ampCM.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $status.css('color', 'var(--amp-green)').text('Imported ' + res.data.imported + ' setting(s). Reloading…');
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    $status.css('color', 'var(--amp-red)').text((res.data && res.data.message) || 'Import failed.');
                }
            },
            error: function() {
                $status.css('color', 'var(--amp-red)').text('Upload failed.');
            }
        });
    });

});