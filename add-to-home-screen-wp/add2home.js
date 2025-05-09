var addToHome = (function (w) {
    var nav = w.navigator,
        isIDevice = 'platform' in nav && (/iphone|ipod|ipad/gi).test(nav.platform),
        isMobileChrome = /Android/i.test(navigator.userAgent) && /Chrome\/[.0-9]*/.test(navigator.userAgent) && nav.userAgent.indexOf("Version") == -1,
        isIPad,
        isRetina,
        isSafari,
        isStandalone,
        OSVersion,
        startX = 0,
        startY = 0,
        lastVisit = 0,
        isExpired,
        isSessionActive,
        isReturningVisitor,
        balloon,
        overrideChecks,

        positionInterval,
        closeTimeout,

        options = {
            autostart: true,
            returningVisitor: false,
            animationIn: 'drop',
            animationOut: 'fade',
            startDelay: 2000,
            lifespan: 15000,
            bottomOffset: 14,
            expire: 0,
            message: '',
            touchIcon: false,
            arrow: true,
            hookOnLoad: true,
            closeButton: true,
            iterations: 100,
            enableBalloonIOS: 'on',
            installPromptAndroid: 'custom_floating_balloon',
            precomposedIcon: false,
            balloonIcon: ''
        },

        intl = {
            ar: '<span dir="rtl">قم بتثبيت هذا التطبيق على <span dir="ltr">%device:</span>انقر<span dir="ltr">%icon</span> ،<strong>ثم اضفه الى الشاشة الرئيسية.</strong></span>',
            ca_es: 'Per instal·lar aquesta aplicació al vostre %device premeu %icon i llavors <strong>Afegir a pantalla d\'inici</strong>.',
            cs_cz: 'Pro instalaci aplikace na Váš %device, stiskněte %icon a v nabídce <strong>Přidat na plochu</strong>.',
            da_dk: 'Tilføj denne side til din %device: tryk på %icon og derefter <strong>Føj til hjemmeskærm</strong>.',
            de_de: 'Installieren Sie diese App auf Ihrem %device: %icon antippen und dann <strong>Zum Home-Bildschirm</strong>.',
            el_gr: 'Εγκαταστήσετε αυτήν την Εφαρμογή στήν συσκευή σας %device: %icon μετά πατάτε <strong>Προσθήκη σε Αφετηρία</strong>.',
            en_us: 'Install this web app on your %device: tap %icon and then <strong><span class="plus" style="border:1px solid #000;padding:0px 3px 2px 3px;font-size: 12px;"> + </span> Add to Home Screen</strong>.',
            en_us_android: 'Install this web app on your %device: tap the menu then <strong>Add to homescreen</strong>.',
            es_es: 'Para instalar esta app en su %device, pulse %icon y seleccione <strong>Añadir a pantalla de inicio</strong>.',
            fi_fi: 'Asenna tämä web-sovellus laitteeseesi %device: paina %icon ja sen jälkeen valitse <strong>Lisää Koti-valikkoon</strong>.',
            fr_fr: 'Ajoutez cette application sur votre %device en cliquant sur %icon, puis <strong>Ajouter à l\'écran d\'accueil</strong>.',
            fr_fr_android: 'Ajoutez cette application sur votre %device en cliquant sur le menu, puis <strong>Ajouter à l\'écran d\'accueil</strong>.',
            he_il: '<span dir="rtl">התקן אפליקציה זו על ה-%device שלך: הקש %icon ואז <strong>הוסף למסך הבית</strong>.</span>',
            hr_hr: 'Instaliraj ovu aplikaciju na svoj %device: klikni na %icon i odaberi <strong>Dodaj u početni zaslon</strong>.',
            hu_hu: 'Telepítse ezt a web-alkalmazást az Ön %device-jára: nyomjon a %icon-ra majd a <strong>Főképernyőhöz adás</strong> gombra.',
            it_it: 'Installa questa applicazione sul tuo %device: premi su %icon e poi <strong>Aggiungi a Home</strong>.',
            ja_jp: 'このウェブアプリをあなたの%deviceにインストールするには%iconをタップして<strong>ホーム画面に追加</strong>を選んでください。',
            ko_kr: '%device에 웹앱을 설치하려면 %icon을 터치 후 "홈화면에 추가"를 선택하세요',
            nb_no: 'Installer denne appen på din %device: trykk på %icon og deretter <strong>Legg til på Hjem-skjerm</strong>',
            nl_nl: 'Installeer deze webapp op uw %device: tik %icon en dan <strong>Voeg toe aan beginscherm</strong>.',
            pl_pl: 'Aby zainstalować tę aplikacje na %device: naciśnij %icon a następnie <strong>Dodaj jako ikonę</strong>.',
            pt_br: 'Instale este aplicativo em seu %device: aperte %icon e selecione <strong>Adicionar à Tela Inicio</strong>.',
            pt_pt: 'Para instalar esta aplicação no seu %device, prima o %icon e depois em <strong>Adicionar ao ecrã principal</strong>.',
            ru_ru: 'Установите это веб-приложение на ваш %device: нажмите %icon, затем <strong>Добавить в «Домой»</strong>.',
            sv_se: 'Lägg till denna webbapplikation på din %device: tryck på %icon och därefter <strong>Lägg till på hemskärmen</strong>.',
            th_th: 'ติดตั้งเว็บแอพฯ นี้บน %device ของคุณ: แตะ %icon และ <strong>เพิ่มที่หน้าจอโฮม</strong>',
            tr_tr: 'Bu uygulamayı %device\'a eklemek için %icon simgesine sonrasında <strong>Ana Ekrana Ekle</strong> düğmesine basın.',
            uk_ua: 'Встановіть цей веб сайт на Ваш %device: натисніть %icon, а потім <strong>На початковий екран</strong>.',
            zh_cn: '您可以将此应用程式安装到您的 %device 上。请按 %icon 然后点选<strong>添加至主屏幕</strong>。',
            zh_tw: '您可以將此應用程式安裝到您的 %device 上。請按 %icon 然後點選<strong>加入主畫面螢幕</strong>。'
        };

    function init() {
        if (!isIDevice && !isMobileChrome) {
            console.log('ATHSWP: Device not compatible - isIDevice: ', isIDevice, 'isMobileChrome: ', isMobileChrome);
            return;
        }

        var now = Date.now(),
            i;

        // Réinitialiser les options pour éviter la persistance entre les pages
        options = {
            autostart: true,
            returningVisitor: false,
            animationIn: 'drop',
            animationOut: 'fade',
            startDelay: 2000,
            lifespan: 15000,
            bottomOffset: 14,
            expire: 0,
            message: '',
            touchIcon: false,
            arrow: true,
            hookOnLoad: true,
            closeButton: true,
            iterations: 100,
            enableBalloonIOS: 'on',
            installPromptAndroid: 'custom_floating_balloon',
            precomposedIcon: false,
            balloonIcon: ''
        };

        // Fusionner les options avec validation
        if (w.addToHomeConfig) {
            console.log('ATHSWP: Merging addToHomeConfig = ', w.addToHomeConfig);
            for (i in w.addToHomeConfig) {
                if (w.addToHomeConfig.hasOwnProperty(i) && i !== 'message') {
                    options[i] = w.addToHomeConfig[i];
                }
            }
            // S'assurer que installPromptAndroid est défini
            if (!options.installPromptAndroid) {
                options.installPromptAndroid = 'custom_floating_balloon';
                console.log('ATHSWP: installPromptAndroid was undefined, set to default: custom_floating_balloon');
            }
            // S'assurer que enableBalloonIOS est défini
            options.enableBalloonIOS = w.addToHomeConfig.enableBalloonIOS || 'on';
            console.log('ATHSWP: Set enableBalloonIOS to = ', options.enableBalloonIOS);
        }
        if (!options.autostart) options.hookOnLoad = false;

        isIPad = (/ipad/gi).test(nav.platform);
        isRetina = w.devicePixelRatio && w.devicePixelRatio > 1;
        isSafari = isIDevice && (/Safari/i).test(nav.appVersion) && !(/CriOS/i).test(nav.appVersion);
        isStandalone = nav.standalone || w.matchMedia('(display-mode: standalone)').matches;
        OSVersion = nav.appVersion.match(/OS (\d+_\d+)/i);
        OSVersion = OSVersion && OSVersion[1] ? +OSVersion[1].replace('_', '.') : 0;

        lastVisit = +w.localStorage.getItem('addToHome');
        isSessionActive = w.sessionStorage.getItem('addToHomeSession');
        isReturningVisitor = options.returningVisitor ? lastVisit && lastVisit + 28 * 24 * 60 * 60 * 1000 > now : true;

        if (!lastVisit) lastVisit = now;
        isExpired = isReturningVisitor && lastVisit <= now;

        console.log('ATHSWP: Init - isIDevice: ', isIDevice, 'isMobileChrome: ', isMobileChrome, 'isSafari: ', isSafari, 'isStandalone: ', isStandalone, 'isSessionActive: ', isSessionActive, 'isReturningVisitor: ', isReturningVisitor, 'isExpired: ', isExpired);

        if (options.hookOnLoad) w.addEventListener('load', loaded, false);
        else if (!options.hookOnLoad && options.autostart) loaded();
    }

    function loaded() {
        w.removeEventListener('load', loaded, false);
        console.log('ATHSWP: loaded() called - isIDevice: ', isIDevice, 'isMobileChrome: ', isMobileChrome, 'enableBalloonIOS: ', options.enableBalloonIOS, 'installPromptAndroid: ', options.installPromptAndroid, 'addToHomeMessage: ', w.addToHomeMessage, 'animationIn: ', options.animationIn, 'animationOut: ', options.animationOut, 'startDelay: ', options.startDelay, 'lifespan: ', options.lifespan, 'bottomOffset: ', options.bottomOffset, 'returningVisitor: ', options.returningVisitor, 'touchIcon: ', options.touchIcon, 'precomposedIcon: ', options.precomposedIcon);

        if (!isReturningVisitor) w.localStorage.setItem('addToHome', Date.now());
        else if (options.expire && isExpired) w.localStorage.setItem('addToHome', Date.now() + options.expire * 60000);

        if ((isIDevice && options.enableBalloonIOS !== 'on') || (isMobileChrome && options.installPromptAndroid !== 'custom_floating_balloon')) {
            console.log('ATHSWP: Skipping balloon due to configuration');
            return;
        }

        if (!overrideChecks && (!isIDevice && !isMobileChrome || isSessionActive || isStandalone)) {
            console.log('ATHSWP: Skipping balloon - isSafari: ', isSafari, 'isMobileChrome: ', isMobileChrome, 'isSessionActive: ', isSessionActive, 'isStandalone: ', isStandalone);
            return;
        }

        var touchIcon = '',
            platform = isMobileChrome ? 'Android' : nav.platform.split(' ')[0];

        balloon = document.createElement('div');
        balloon.id = 'addToHomeScreen';
        balloon.style.cssText += 'left:-9999px;-webkit-transition-property:-webkit-transform,opacity;-webkit-transition-duration:0;-webkit-transform:translate3d(0,0,0);position:' + (OSVersion < 5 ? 'absolute' : 'fixed');

        // Utiliser addToHomeMessage directement
        var message = w.addToHomeMessage || '';
        console.log('ATHSWP: Using message = ', message);
        if (!message) {
            console.error('ATHSWP: Message is empty, falling back to default');
            message = isMobileChrome ? 'Add this site to your Android now!' : 'Add this site to your iPhone now!';
        }

        // Remplacer les placeholders %device, %icon et %add
        var finalMessage = message
            .replace('%device', platform)
            .replace('%add', OSVersion >= 4.2 || isMobileChrome ? '<span class="addToHomeadd"></span>' : '<span class="addToHomePlus">+</span>')
            .replace('%icon', isMobileChrome ? '' : (OSVersion >= 4.2 ? '<span class="addToHomeShare"></span>' : '<span class="addToHomePlus">+</span>'));
        console.log('ATHSWP: Final message after replacements = ', finalMessage);

        // Remplacer options.message par finalMessage
        options.message = finalMessage;
        console.log('ATHSWP: options.message set to = ', options.message);

        if (options.balloonIcon) {
            touchIcon = '<span style="background-image:url(' + options.balloonIcon + ')" class="addToHomeTouchIcon' + (options.precomposedIcon ? ' precomposed' : '') + '"></span>';
        } else if (options.touchIcon) {
            touchIcon = isRetina ?
                document.querySelector('head link[rel^=apple-touch-icon][sizes="114x114"],head link[rel^=apple-touch-icon][sizes="144x144"],head link[rel^=apple-touch-icon]') :
                document.querySelector('head link[rel^=apple-touch-icon][sizes="57x57"],head link[rel^=apple-touch-icon]');
            if (touchIcon) {
                touchIcon = '<span style="background-image:url(' + touchIcon.href + ')" class="addToHomeTouchIcon' + (options.precomposedIcon ? ' precomposed' : '') + '"></span>';
            }
        }

        balloon.className = (OSVersion >= 7 ? 'addToHomeIOS7 ' : '') + (isIPad ? 'addToHomeIpad' : isMobileChrome ? 'addToHomeAndroid' : 'addToHomeIphone') + (touchIcon ? ' addToHomeWide' : '');
        balloon.innerHTML = touchIcon +
            finalMessage +
            (options.arrow ? '<span class="addToHomeArrow"' + (OSVersion >= 7 && isIPad && touchIcon ? ' style="margin-left:-32px"' : '') + '></span>' : '') +
            (options.closeButton ? '<span class="addToHomeClose">\u00D7</span>' : '');

        document.body.appendChild(balloon);

        if (options.closeButton) {
            var closeButton = balloon.querySelector('.addToHomeClose');
            if (closeButton) closeButton.addEventListener('click', clicked, false);
        }

        if (!isIPad && OSVersion >= 6) window.addEventListener('orientationchange', orientationCheck, false);

        setTimeout(show, options.startDelay);
    }

    function show() {
        var duration,
            iPadXShift = 208;

        if (isIPad) {
            if (OSVersion < 5) {
                startY = w.scrollY;
                startX = w.scrollX;
            } else if (OSVersion < 6) {
                iPadXShift = 160;
            } else if (OSVersion >= 7) {
                iPadXShift = 143;
            }

            balloon.style.top = startY + options.bottomOffset + 'px';
            balloon.style.left = Math.max(startX + iPadXShift - Math.round(balloon.offsetWidth / 2), 9) + 'px';

            switch (options.animationIn) {
                case 'drop':
                    duration = '0.6s';
                    balloon.style.webkitTransform = 'translate3d(0,' + -(w.scrollY + options.bottomOffset + balloon.offsetHeight) + 'px,0)';
                    break;
                case 'bubble':
                    duration = '0.6s';
                    balloon.style.opacity = '0';
                    balloon.style.webkitTransform = 'translate3d(0,' + (startY + 50) + 'px,0)';
                    break;
                default:
                    duration = '1s';
                    balloon.style.opacity = '0';
            }
        } else {
            startY = w.innerHeight + w.scrollY;

            if (OSVersion < 5 && !isMobileChrome) {
                startX = Math.round((w.innerWidth - balloon.offsetWidth) / 2) + w.scrollX;
                balloon.style.left = startX + 'px';
                balloon.style.top = startY - balloon.offsetHeight - options.bottomOffset + 'px';
            } else {
                balloon.style.left = '50%';
                balloon.style.marginLeft = -Math.round(balloon.offsetWidth / 2) - (w.orientation % 180 && OSVersion >= 6 && OSVersion < 7 && !isMobileChrome ? 40 : 0) + 'px';
                balloon.style.bottom = options.bottomOffset + 'px';
            }

            switch (options.animationIn) {
                case 'drop':
                    duration = '1s';
                    balloon.style.webkitTransform = 'translate3d(0,' + -(startY + options.bottomOffset) + 'px,0)';
                    break;
                case 'bubble':
                    duration = '0.6s';
                    balloon.style.webkitTransform = 'translate3d(0,' + (balloon.offsetHeight + options.bottomOffset + 50) + 'px,0)';
                    break;
                default:
                    duration = '1s';
                    balloon.style.opacity = '0';
            }
        }

        balloon.offsetHeight; // repaint trick
        balloon.style.webkitTransitionDuration = duration;
        balloon.style.opacity = '1';
        balloon.style.webkitTransform = 'translate3d(0,0,0)';
        balloon.addEventListener('webkitTransitionEnd', transitionEnd, false);

        closeTimeout = setTimeout(close, options.lifespan);
    }

    function manualShow(override) {
        if ((!isIDevice && !isMobileChrome) || balloon) return;

        overrideChecks = override;
        loaded();
    }

    function close() {
        clearInterval(positionInterval);
        clearTimeout(closeTimeout);
        closeTimeout = null;

        if (!balloon) return;

        var posY = 0,
            posX = 0,
            opacity = '1',
            duration = '0';

        if (options.closeButton) balloon.removeEventListener('click', clicked, false);
        if (!isIPad && OSVersion >= 6 && !isMobileChrome) window.removeEventListener('orientationchange', orientationCheck, false);

        if (OSVersion < 5 && !isMobileChrome) {
            posY = isIPad ? w.scrollY - startY : w.scrollY + w.innerHeight - startY;
            posX = isIPad ? w.scrollX - startX : w.scrollX + Math.round((w.innerWidth - balloon.offsetWidth) / 2) - startX;
        }

        balloon.style.webkitTransitionProperty = '-webkit-transform,opacity';

        switch (options.animationOut) {
            case 'drop':
                if (isIPad) {
                    duration = '0.4s';
                    opacity = '0';
                    posY += 50;
                } else {
                    duration = '0.6s';
                    posY += balloon.offsetHeight + options.bottomOffset + 50;
                }
                break;
            case 'bubble':
                if (isIPad) {
                    duration = '0.8s';
                    posY -= balloon.offsetHeight + options.bottomOffset + 50;
                } else {
                    duration = '0.4s';
                    opacity = '0';
                    posY -= 50;
                }
                break;
            default:
                duration = '0.8s';
                opacity = '0';
        }

        balloon.addEventListener('webkitTransitionEnd', transitionEnd, false);
        balloon.style.opacity = opacity;
        balloon.style.webkitTransitionDuration = duration;
        balloon.style.webkitTransform = 'translate3d(' + posX + 'px,' + posY + 'px,0)';
    }

    function clicked() {
        w.sessionStorage.setItem('addToHomeSession', '1');
        isSessionActive = true;
        close();
    }

    function transitionEnd() {
        balloon.removeEventListener('webkitTransitionEnd', transitionEnd, false);

        balloon.style.webkitTransitionProperty = '-webkit-transform';
        balloon.style.webkitTransitionDuration = '0.2s';

        if (!closeTimeout) {
            balloon.parentNode.removeChild(balloon);
            balloon = null;
            return;
        }

        if (OSVersion < 5 && !isMobileChrome && closeTimeout) positionInterval = setInterval(setPosition, options.iterations);
    }

    function setPosition() {
        var matrix = new WebKitCSSMatrix(w.getComputedStyle(balloon, null).webkitTransform),
            posY = isIPad ? w.scrollY - startY : w.scrollY + w.innerHeight - startY,
            posX = isIPad ? w.scrollX - startX : w.scrollX + Math.round((w.innerWidth - balloon.offsetWidth) / 2) - startX;

        if (posY == matrix.m42 && posX == matrix.m41) return;

        balloon.style.webkitTransform = 'translate3d(' + posX + 'px,' + posY + 'px,0)';
    }

    function reset() {
        w.localStorage.removeItem('addToHome');
        w.sessionStorage.removeItem('addToHomeSession');
    }

    function orientationCheck() {
        balloon.style.marginLeft = -Math.round(balloon.offsetWidth / 2) - (w.orientation % 180 && OSVersion >= 6 && OSVersion < 7 && !isMobileChrome ? 40 : 0) + 'px';
    }

    init();

    return {
        show: manualShow,
        close: close,
        reset: reset
    };
})(window);