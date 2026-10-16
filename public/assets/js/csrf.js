/**
 * Protección CSRF en el cliente.
 * Añade el token (meta "csrf-token") a:
 *  - peticiones fetch / XMLHttpRequest (jQuery.ajax) que modifican datos hacia el mismo origen
 *  - formularios con method="post"
 */
(function () {
    'use strict';

    var SAFE = ['GET', 'HEAD', 'OPTIONS'];

    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function sameOrigin(url) {
        try {
            return new URL(url, window.location.href).origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }

    if (window.fetch) {
        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            var isRequest = typeof Request !== 'undefined' && input instanceof Request;
            var url = isRequest ? input.url : String(input);
            var method = ((init && init.method) || (isRequest && input.method) || 'GET').toUpperCase();

            if (SAFE.indexOf(method) === -1 && sameOrigin(url)) {
                init = init || {};
                var headers = new Headers(init.headers || (isRequest ? input.headers : undefined));
                if (!headers.has('X-CSRF-Token')) {
                    headers.set('X-CSRF-Token', token());
                }
                init.headers = headers;
            }
            return originalFetch.call(this, input, init);
        };
    }

    if (window.XMLHttpRequest) {
        var open = XMLHttpRequest.prototype.open;
        var send = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function (method, url) {
            this._csrfMethod = String(method).toUpperCase();
            this._csrfUrl = String(url);
            return open.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function () {
            if (SAFE.indexOf(this._csrfMethod) === -1 && sameOrigin(this._csrfUrl)) {
                try {
                    this.setRequestHeader('X-CSRF-Token', token());
                } catch (e) { /* ya enviada o no permitida */ }
            }
            return send.apply(this, arguments);
        };
    }

    function addTokenToForms(root) {
        var forms = (root || document).querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') { continue; }
            if (form.querySelector('input[name="_csrf"]')) { continue; }
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf';
            input.value = token();
            form.appendChild(input);
        }
    }

    document.addEventListener('DOMContentLoaded', function () { addTokenToForms(document); });
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (form && form.tagName === 'FORM') {
            addTokenToForms(form.parentNode || document);
        }
    }, true);
})();
