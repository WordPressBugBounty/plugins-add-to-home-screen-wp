// wp-content/plugins/add-to-home-screen-wp/athswp-pwa.js
jQuery(document).ready(function($) {
    // Register service worker
    if ('serviceWorker' in navigator && window.ATHSWP_SW_URL) {
        navigator.serviceWorker.register(window.ATHSWP_SW_URL, { scope: '/' })
            .then(reg => {
                console.log('Service Worker registered');
            })
            .catch(err => {
                console.error('Service Worker registration failed:', err);
            });
    }

    // Initialize spinner
    const spinner = $('<div class="athswp-spinner"><div class="spinner-inner"></div></div>');
    $('body').append(spinner);
    var spinnerColor = typeof ATHSWP_SPINNER_COLOR !== 'undefined' ? ATHSWP_SPINNER_COLOR : '#000000';
    $('.spinner-inner').css({
        'border': '4px solid ' + spinnerColor,
        'border-top': '4px solid transparent'
    });

    // Show spinner and navigate
    function showSpinnerAndNavigate(url) {
        spinner.fadeIn(300);
        setTimeout(() => {
            window.location.href = url;
        }, 500);
    }

    // Handle PWA navigation
    if (window.navigator.standalone === true || window.matchMedia("(display-mode: standalone)").matches) {
        if (typeof ATHSWP_ENABLE_SPINNER !== 'undefined' && ATHSWP_ENABLE_SPINNER === 'true') {
            $(document).on('click', 'a:not(#adminmenu a)', function(e) {
                const url = $(this).attr('href');
                if (url && !url.startsWith('#') && !$(this).attr('target') && 
                    (!url.includes('://') || url.includes(window.location.hostname))) {
                    e.preventDefault();
                    showSpinnerAndNavigate(url);
                }
            });

            $('#adminmenu a').on('click', function(e) {
                const $this = $(this);
                const url = $this.attr('href');
                const $parent = $this.parent('li');
                if ($parent.hasClass('menu-top') && $parent.find('.wp-submenu').length && !$parent.hasClass('wp-menu-open')) {
                    return;
                }
                if (url && !url.startsWith('#') && !$this.attr('target')) {
                    e.preventDefault();
                    showSpinnerAndNavigate(url);
                }
            });

            $(window).on('load', function() {
                spinner.fadeOut(300);
            });

            $(window).on('pageshow', function(event) {
                if (event.originalEvent.persisted) {
                    spinner.fadeOut(300);
                }
            });

            // Pull-to-refresh
            function isInSidebar(target) {
                while (target) {
                    if (target.id === 'adminmenuwrap' || target.id === 'adminmenu' || 
                        target.classList && (target.classList.contains('wp-submenu') || target.classList.contains('wp-menu-name'))) {
                        return true;
                    }
                    target = target.parentNode;
                }
                return false;
            }

            // Vérifier si le target est dans un élément éditable
            function isInEditable(target) {
                while (target) {
                    const tagName = target.tagName.toUpperCase();
                    if (['INPUT', 'TEXTAREA'].includes(tagName) || target.contentEditable === 'true') {
                        return true;
                    }
                    target = target.parentNode;
                }
                return false;
            }

            let startY = 0;
            let pullDistance = 0;
            let pullStartTime = 0;
            const pullThreshold = 100; // Seuil de distance en pixels
            const minPullDuration = 250; // Durée minimale en millisecondes pour déclencher le rechargement
            let isPulling = false;

            document.addEventListener('touchstart', function(e) {
                if (isInSidebar(e.target) || isInEditable(e.target)) {
                    return; // Ignore si geste commence dans la sidebar ou dans un élément éditable
                }
                // Vérifier si l'utilisateur est presque au sommet de la page
                setTimeout(() => {
                    if (window.scrollY <= 5) { // Tolérance de 5 pixels pour contourner les délais de rendu
                        startY = e.touches[0].pageY;
                        pullStartTime = Date.now(); // Enregistrer le temps de début du geste
                        isPulling = true; // Activer le mode "pull"
                    } else {
                        isPulling = false; // Désactiver si pas au sommet
                    }
                }, 50); // Délai de 50ms pour s'assurer que scrollY est à jour
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (isInSidebar(e.target) || isInEditable(e.target)) {
                    return; // Ignore si geste se déplace dans la sidebar ou dans un élément éditable
                }
                if (!isPulling) return; // Sortir si le geste n'a pas commencé au sommet

                pullDistance = e.touches[0].pageY - startY;
                if (pullDistance > 0 && window.scrollY <= 5) { // Tolérance de 5 pixels
                    e.preventDefault();
                    const opacity = Math.min(pullDistance / pullThreshold, 1);
                    spinner.css('opacity', opacity).fadeIn(0);
                } else {
                    isPulling = false; // Désactiver si l'utilisateur n'est plus au sommet
                    spinner.fadeOut(300);
                }
            }, { passive: false });

            document.addEventListener('touchend', function() {
                if (isPulling) {
                    const pullDuration = Date.now() - pullStartTime; // Calculer la durée du geste
                    if (pullDistance >= pullThreshold && pullDuration >= minPullDuration) {
                        // Déclencher le rechargement si la distance et la durée sont suffisantes
                        spinner.css('opacity', 1).fadeIn(300);
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Annuler si la durée ou la distance est insuffisante
                        spinner.fadeOut(300);
                    }
                }
                pullDistance = 0;
                startY = 0;
                pullStartTime = 0;
                isPulling = false;
            }, { passive: true });
        }
    }
});