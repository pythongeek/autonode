/* AMP Agency Content Manager Pro â€” Admin JS v4.1 (Corrected) */
/* global ampCM, jQuery */
jQuery(document).ready(function ($) {
    'use strict';

    // Safety check to ensure the localization script loaded
    if (typeof ampCM === 'undefined') {
        console.error('AMP CM: Localization object (ampCM) is missing.');
        return;
    }

    /* â”€â”€ Toast â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $('head').append('<style>.amp-toast{position:fixed;bottom:24px;right:24px;padding:11px 18px;border-radius:8px;font-size:13px;font-weight:600;opacity:0;z-index:999999;max-width:340px;box-shadow:0 8px 30px rgba(0,0,0,.4);transition:opacity .3s;pointer-events:none}.amp-toast.show{opacity:1}.amp-toast-success{background:#22c55e;color:#fff}.amp-toast-error{background:#ef4444;color:#fff}.amp-toast-info{background:#28CCCD;color:#0f1117}</style>');
    function toast(msg, type) {
        type = type || 'success';
        var el = $('<div class="amp-toast amp-toast-' + type + '">' + msg + '</div>').appendTo('body');
        setTimeout(function(){ el.addClass('show'); }, 10);
        setTimeout(function(){ el.removeClass('show'); setTimeout(function(){ el.remove(); }, 300); }, 3200);
    }

    /* â”€â”€ Scope Presets â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
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

    /* â”€â”€ Create Key â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    /* â”€â”€ Create Key (WITH DEBUG LOGGING) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('submit', '#amp-create-key-form', function (e) {
        // 1. Force the browser to stop the normal HTML form submission immediately
        e.preventDefault(); 
        
        console.log('[AMP DEBUG] Form submit intercepted successfully.');

        // 2. Verify localization object exists
        if (typeof ampCM === 'undefined') {
            console.error('[AMP DEBUG] FATAL: ampCM localization object is missing! The server did not load it.');
            alert('Plugin configuration missing. Check browser console.');
            return;
        }

        var $form = $(this);
        var $btn  = $('#amp-create-key-btn').prop('disabled', true).text('Generatingâ€¦');
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

        console.log('[AMP DEBUG] Sending AJAX POST request to:', ampCM.ajaxUrl);
        console.log('[AMP DEBUG] Payload being sent:', payload);

        $.post(ampCM.ajaxUrl, payload)
        .done(function (res) {
            console.log('[AMP DEBUG] AJAX Response Received:', res);
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
                console.error('[AMP DEBUG] Server rejected the request:', res.data);
                toast((res.data && res.data.message) || 'Error', 'error'); 
            }
        }).fail(function (xhr, status, error) { 
            console.error('[AMP DEBUG] AJAX Request FAILED.');
            console.error('[AMP DEBUG] HTTP Status:', status);
            console.error('[AMP DEBUG] Error Thrown:', error);
            console.error('[AMP DEBUG] Raw Server Response:', xhr.responseText);
            toast('Request failed. Check console.', 'error');
        }).always(function () { 
            console.log('[AMP DEBUG] AJAX cycle completed.');
            $btn.prop('disabled', false).text('Generate API Key'); 
        });
    });

    /* â”€â”€ Dismiss new key â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '#amp-dismiss-key', function (e) {
        e.preventDefault();
        if (confirm('Have you saved the key? It cannot be retrieved.')) {
            $('#amp-new-key-result').slideUp();
            setTimeout(function () { location.reload(); }, 200);
        }
    });

    /* â”€â”€ Copy buttons â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '.amp-copy-btn', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var text = $('#' + target).text().trim();
        var $btn = $(this);
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                var orig = $btn.text();
                $btn.text('âœ… Copied!');
                setTimeout(function () { $btn.text(orig); }, 2000);
            }).catch(function () { prompt('Copy:', text); });
        } else { prompt('Copy:', text); }
    });

    /* â”€â”€ Revoke key â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '.amp-revoke-btn', function (e) {
        e.preventDefault();
        var $btn = $(this), id = $btn.data('id'), label = $btn.data('label');
        if (!confirm('Revoke "' + label + '"? This cannot be undone.')) return;
        $btn.prop('disabled', true).text('Revokingâ€¦');
        $.post(ampCM.ajaxUrl, { action: 'autonode_revoke_key', nonce: ampCM.nonce, key_id: id })
            .done(function (res) {
                if (res.success) {
                    $btn.closest('.amp-key-row').addClass('amp-row-revoked');
                    $btn.closest('td').html('<span class="amp-badge amp-badge-revoked">Revoked</span>');
                    toast('Key revoked.', 'info');
                } else { toast('Failed.', 'error'); $btn.prop('disabled', false).text('Revoke'); }
            }).fail(function () { toast('Failed.', 'error'); $btn.prop('disabled', false).text('Revoke'); });
    });

    /* â”€â”€ Show revoked toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('change', '#amp-show-revoked', function () {
        this.checked ? $('.amp-row-revoked').show() : $('.amp-row-revoked').hide();
    });

    /* â”€â”€ Settings form â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('submit', '#amp-settings-form', function (e) {
        e.preventDefault();
        var $btn = $(this).find('[type=submit]').prop('disabled', true).text('Savingâ€¦');
        var $st  = $('#amp-save-status');
        $.post(ampCM.ajaxUrl, {
            action: 'autonode_save_settings', nonce: ampCM.nonce,
            rate_limit: $('[name=rate_limit]').val(), rate_window_sec: $('[name=rate_window_sec]').val(),
            log_retention_days: $('[name=log_retention_days]').val(),
            require_https: $('[name=require_https]').is(':checked') ? 1 : 0,
            enable_webhooks: $('[name=enable_webhooks]').is(':checked') ? 1 : 0,
            webhook_timeout_ms: $('[name=webhook_timeout_ms]').val(),
        }).done(function (res) {
            if (res.success) { $st.html('<span style="color:var(--amp-green);font-weight:600">âœ“ Saved</span>'); toast('Settings saved.', 'success'); }
            else { $st.html('<span style="color:var(--amp-red)">Error</span>'); }
        }).fail(function () { $st.html('<span style="color:var(--amp-red)">Failed</span>'); }
        ).always(function () { $btn.prop('disabled', false).text('Save Settings'); setTimeout(function(){ $st.html(''); }, 3000); });
    });

    /* â”€â”€ Create Webhook â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('submit', '#amp-create-webhook-form', function (e) {
        e.preventDefault();
        var $btn = $(this).find('[type=submit]').prop('disabled', true).text('Registeringâ€¦');
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

    /* â”€â”€ Toggle webhook active â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('change', '.amp-wh-toggle', function () {
        var id = $(this).data('id'), active = this.checked ? 1 : 0;
        $.post(ampCM.ajaxUrl, { action: 'autonode_toggle_webhook', nonce: ampCM.nonce, id: id, active: active })
            .done(function (res) { if (res.success) toast(active ? 'Webhook enabled.' : 'Webhook disabled.', 'info'); });
    });

    /* â”€â”€ Delete webhook â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '.amp-del-wh', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (!confirm('Delete this webhook?')) return;
        $.post(ampCM.ajaxUrl, { action: 'autonode_delete_webhook', nonce: ampCM.nonce, id: id })
            .done(function (res) {
                if (res.success) { $('[data-id=' + id + ']').closest('tr').fadeOut(300, function(){ $(this).remove(); }); toast('Deleted.', 'info'); }
            });
    });

    /* â”€â”€ Test webhook â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '.amp-test-wh', function (e) {
        e.preventDefault();
        var id = $(this).data('id'), $mo = $('#amp-test-modal'), $res = $('#amp-test-result');
        $res.css('color', 'var(--amp-muted)').text('Sending testâ€¦');
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

    /* â”€â”€ Modal close â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '.amp-modal-close', function (e) { e.preventDefault(); $('#amp-test-modal').hide(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') $('#amp-test-modal').hide(); });
    $(document).on('click', '#amp-test-modal', function (e) { if ($(e.target).is(this)) $(this).hide(); });

    /* â”€â”€ Rotate Key â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
    $(document).on('click', '.amp-rotate-btn', function (e) {
        e.preventDefault();
        var $btn = $(this), id = $btn.data('id'), label = $btn.data('label');
        if (!confirm('Rotate key "' + label + '"?\n\nThe old key stops working immediately. A new key will be shown once â€” save it before dismissing.')) return;
        $btn.prop('disabled', true).text('Rotatingâ€¦');
        $.post(window.ampCM ? ampCM.ajaxUrl : ajaxurl, { action: 'autonode_rotate_key', nonce: ampCM.nonce, key_id: id })
            .done(function (res) {
                if (res.success) {
                    var raw = res.data.raw_key;
                    var html = '<div class="amp-new-key-box" style="margin-top:12px">' +
                        '<div class="amp-new-key-header">âœ… Key Rotated â€” New Key:</div>' +
                        '<div class="amp-key-display-row"><code id="amp-rotated-key-' + id + '">' + raw + '</code>' +
                        '<button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-rotated-key-' + id + '">ðŸ“‹ Copy</button></div>' +
                        '<div class="amp-key-display-row" style="margin-top:8px"><span style="font-size:12px;color:var(--amp-muted)">n8n value:</span>' +
                        '<code id="amp-rotated-n8n-' + id + '">Bearer ' + raw + '</code>' +
                        '<button class="amp-btn amp-btn-ghost amp-copy-btn" data-target="amp-rotated-n8n-' + id + '">ðŸ“‹ Copy</button></div>' +
                        '<p style="font-size:12px;color:var(--amp-yellow);margin-top:8px">âš ï¸ Update your n8n credential immediately.</p></div>';
                    $btn.closest('tr').find('td:last').prepend(html);
                    $btn.remove();
                    $btn.closest('tr').find('.amp-key-prefix').text(res.data.prefix + 'â€¦');
                } else {
                    alert('Rotate failed: ' + (res.data && res.data.message ? res.data.message : 'Unknown error'));
                    $btn.prop('disabled', false).text('Rotate');
                }
            }).fail(function () {
                alert('Request failed.');
                $btn.prop('disabled', false).text('Rotate');
            });
    });

});