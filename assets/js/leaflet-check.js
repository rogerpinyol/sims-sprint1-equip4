(function(){
    // Simple helper: if Leaflet (L) isn't loaded, show a visible message and
    // attempt to load it dynamically from CDN once more.
    function showError(msg){
        var el = document.createElement('div');
        el.style.position = 'fixed';
        el.style.right = '12px';
        el.style.bottom = '12px';
        el.style.zIndex = '99999';
        el.style.background = '#fff3cd';
        el.style.border = '1px solid #ffeeba';
        el.style.color = '#856404';
        el.style.padding = '10px 14px';
        el.style.borderRadius = '6px';
        el.style.boxShadow = '0 2px 6px rgba(0,0,0,0.1)';
        el.innerText = msg;
        document.body.appendChild(el);
    }

    function tryLoadCDN(){
        // Try multiple CDNs (jsDelivr, cdnjs, unpkg) without integrity checks
        var cdns = [
            {css: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css', js: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js'},
            {css: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css', js: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js'},
            {css: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', js: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'}
        ];

        var tried = 0;

        function tryCdn(index){
            if (index >= cdns.length) {
                showError('Leaflet no està disponible des de cap CDN. Si estàs a una xarxa restringida, permet l\'accés als CDNs o utilitza fitxers locals.');
                return;
            }
            var pair = cdns[index];
            // append css
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = pair.css;
            document.head.appendChild(css);

            // append script
            var s = document.createElement('script');
            s.src = pair.js;
            s.onload = function(){
                showError('Leaflet s\'ha carregat des del CDN: ' + pair.js + '. Si la pàgina no mostra el mapa, actualitza la pàgina.');
            };
            s.onerror = function(){
                // try next CDN
                tryCdn(index + 1);
            };
            document.body.appendChild(s);
        }

        tryCdn(0);
    }

    // Wait until DOM loaded
    document.addEventListener('DOMContentLoaded', function(){
        if (typeof L === 'undefined'){
            showError('Leaflet no està disponible: el mapa pot no mostrar-se. Intentant carregar CDN...');
            tryLoadCDN();
        }
    });
})();
