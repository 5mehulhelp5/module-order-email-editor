/*
 * ETechFlow_OrderEmailEditor — admin modal + AJAX submit.
 * Pure vanilla JS — no jQuery, no Knockout, no RequireJS module. Loaded
 * directly via <script src> in sales_order_view.xml. Works in every browser
 * supported by Magento admin (Chrome/Edge/Firefox/Safari latest 2 releases).
 */
(function () {
    'use strict';

    function $(sel, ctx) { return (ctx || document).querySelector(sel); }
    function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

    function openModal(modal)  { modal.hidden = false; document.body.classList.add('eteoee-no-scroll'); var i = modal.querySelector('input[name="new_email"]'); if (i) i.focus(); }
    function closeModal(modal) { modal.hidden = true;  document.body.classList.remove('eteoee-no-scroll'); resetFeedback(modal); }

    function resetFeedback(modal) {
        var fb = modal.querySelector('.eteoee-feedback');
        if (!fb) return;
        fb.textContent = '';
        fb.classList.remove('is-success', 'is-error');
    }
    function setFeedback(modal, kind, message) {
        var fb = modal.querySelector('.eteoee-feedback');
        if (!fb) return;
        fb.classList.remove('is-success', 'is-error');
        fb.classList.add(kind === 'success' ? 'is-success' : 'is-error');
        fb.textContent = message;
    }

    function busy(form, on) {
        var btn      = form.querySelector('.eteoee-submit');
        var label    = form.querySelector('.eteoee-submit-label');
        var spinner  = form.querySelector('.eteoee-submit-spinner');
        if (btn)     btn.disabled = on;
        if (label)   label.hidden = on;
        if (spinner) spinner.hidden = !on;
    }

    function submitForm(form) {
        var modal = form.closest('.eteoee-modal');
        var url   = form.getAttribute('data-update-url');
        if (!url) return;

        resetFeedback(modal);
        busy(form, true);

        var data = new FormData(form);
        var ctrl = new AbortController();
        var timeout = setTimeout(function () { ctrl.abort(); }, 15000);

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            body: data,
            signal: ctrl.signal,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (resp) {
            clearTimeout(timeout);
            return resp.text().then(function (text) {
                var json = null;
                try { json = JSON.parse(text); } catch (e) {}
                return { ok: resp.ok, status: resp.status, json: json, text: text };
            });
        }).then(function (result) {
            busy(form, false);
            if (result.ok && result.json && result.json.success) {
                setFeedback(modal, 'success', result.json.message || 'Email updated.');
                updateInlineEmail(form, data.get('new_email'));
                setTimeout(function () { closeModal(modal); }, 1100);
                return;
            }
            var msg;
            if (result.json && result.json.message) {
                msg = result.json.message;
            } else if (result.status === 0) {
                msg = 'Request blocked (no response). Check DevTools console.';
            } else {
                msg = 'HTTP ' + result.status + ' from server. See console for the raw response.';
            }
            if (window.console) {
                console.error('[eteoee] response status:', result.status,
                              '\nbody (first 800 chars):', (result.text || '').slice(0, 800));
            }
            setFeedback(modal, 'error', msg);
        }).catch(function (err) {
            clearTimeout(timeout);
            busy(form, false);
            var msg = (err && err.name === 'AbortError') ? 'Request timed out.' : 'Network error. Try again.';
            if (window.console) console.error('[eteoee] fetch error:', err);
            setFeedback(modal, 'error', msg);
        });
    }

    /**
     * Replace the visible email anywhere on the order page. Magento's stock
     * Account Information block renders the email as plain text inside the
     * <tr> with class "data-row". We try a few selectors so this works on
     * both vanilla 2.4 and themed admins.
     */
    function updateInlineEmail(form, newEmail) {
        if (!newEmail) return;
        var orderId = form.querySelector('input[name="order_id"]');
        if (!orderId) return;
        // Generic — replace any text node that exactly matches the current email
        // inside the order_info container.
        var info = document.getElementById('order_info') || document.querySelector('.order-information');
        if (!info) return;
        var current = form.closest('.eteoee-modal').parentNode.querySelector('.eteoee-trigger-wrap');
        var currentEmail = current ? current.getAttribute('data-current-email') : null;
        if (!currentEmail) return;

        walkAndReplace(info, currentEmail, newEmail);
        if (current) current.setAttribute('data-current-email', newEmail);

        // Reflect new email inside the "Current email" line of the modal too.
        var strong = form.querySelector('.eteoee-current strong');
        if (strong) strong.textContent = newEmail;
    }
    function walkAndReplace(root, oldText, newText) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
        var node;
        while ((node = walker.nextNode())) {
            if (node.nodeValue && node.nodeValue.indexOf(oldText) !== -1) {
                node.nodeValue = node.nodeValue.split(oldText).join(newText);
            }
        }
        // Also patch href="mailto:..." links
        var anchors = root.querySelectorAll('a[href^="mailto:"]');
        for (var i = 0; i < anchors.length; i++) {
            var href = anchors[i].getAttribute('href');
            if (href && href.indexOf(oldText) !== -1) {
                anchors[i].setAttribute('href', href.split(oldText).join(newText));
            }
        }
    }

    function init() {
        $$('.eteoee-trigger').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-modal-target');
                if (!target) return;
                var modal = $(target);
                if (modal) openModal(modal);
            });
        });

        $$('.eteoee-modal').forEach(function (modal) {
            $$('[data-modal-close]', modal).forEach(function (el) {
                el.addEventListener('click', function () { closeModal(modal); });
            });
            modal.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeModal(modal);
            });
            var form = modal.querySelector('.eteoee-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    submitForm(form);
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
