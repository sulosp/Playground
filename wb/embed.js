(function () {
    'use strict';

    var script = document.currentScript;
    if (!script || !script.src) return;

    function widgetBaseUrl() {
        if (window.MDG_WIDGET_BASE) {
            return String(window.MDG_WIDGET_BASE).replace(/\/$/, '');
        }
        var url = new URL(script.src);
        return (url.origin + url.pathname).replace(/\/embed\.js(\?.*)?$/, '');
    }

    var base = widgetBaseUrl();
    var containers = document.querySelectorAll('.mdg-yelp-widget');

    containers.forEach(function (container) {
        if (container.getAttribute('data-mdg-embedded') === 'true') return;
        container.setAttribute('data-mdg-embedded', 'true');

        var height = container.getAttribute('data-height') || '480';
        var jsonUrl = container.getAttribute('data-json') || (base + '/yelp-reviews.json');
        var yelpUrl = container.getAttribute('data-yelp') || 'https://www.yelp.com/biz/mobile-dog-grooming-irvine-2';

        var iframe = document.createElement('iframe');
        var embedParams = new URLSearchParams({
            json: jsonUrl,
            yelp: yelpUrl
        });

        iframe.src = base + '/embed.html?' + embedParams.toString();
        iframe.title = 'Yelp Reviews';
        iframe.loading = 'lazy';
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('scrolling', 'no');
        iframe.style.cssText = 'width:100%;border:none;display:block;min-height:' + height + 'px;';

        window.addEventListener('message', function (event) {
            if (event.origin !== new URL(base).origin) return;
            if (!event.data || event.data.type !== 'mdg-yelp-widget-resize') return;
            if (event.source !== iframe.contentWindow) return;
            iframe.style.height = event.data.height + 'px';
        });

        container.appendChild(iframe);
    });
})();
