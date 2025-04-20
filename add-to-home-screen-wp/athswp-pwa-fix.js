(function() {
    const isAndroid = /Android/i.test(navigator.userAgent);
    const isPWA = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;

    if (isAndroid && isPWA) {
        console.log("ATHSWP Pro: Applying admin bar fix for Android PWA");

        const adminBarLinks = document.querySelectorAll('#wpadminbar .menupop > a');

        adminBarLinks.forEach(link => {
            let isMenuOpen = false;

            link.addEventListener('touchstart', function(e) {
                if (!isMenuOpen) {
                    e.preventDefault();
                    console.log("ATHSWP Pro: Prevented default link click, showing submenu");

                    const parent = link.parentElement;
                    parent.classList.add('hover');

                    isMenuOpen = true;

                    document.addEventListener('touchstart', function closeMenu(event) {
                        if (!parent.contains(event.target)) {
                            parent.classList.remove('hover');
                            isMenuOpen = false;
                            document.removeEventListener('touchstart', closeMenu);
                        }
                    });
                }
            }, { passive: false });
        });
    }
})();