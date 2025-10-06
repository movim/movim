var Notif = {
    inhibed: false,
    focused: false,
    call_status: null,
    tab_counter1: 0,
    tab_counter2: 0,
    tab_counter1_key: 'chat',
    tab_counter2_key: 'news',
    document_title_init: null,
    document_title: document.title,
    notifs_key: '',
    favicon: null,

    audioCall: null,

    incomingMessage: function () {
        if (NOTIFICATION_CHAT) {
            // From https://free-mobi.org/ringtones/sms/sms-sound-2
            var tone = new Audio(BASE_URI + 'theme/audio/message.ogg');
            tone.play();
        }
    },
    incomingCall: function () {
        if (NOTIFICATION_CALL) {
            // From https://pixabay.com/sound-effects/ringtone-020-365650/
            Notif.audioCall = new Audio(BASE_URI + 'theme/audio/call.opus');
            Notif.audioCall.addEventListener('ended', function () {
                this.currentTime = 0;
                this.play();
            }, false);
            Notif.audioCall.play();
        }
    },
    incomingCallAnswer: function () {
        if (Notif.audioCall) {
            Notif.audioCall.pause();
            Notif.audioCall.currentTime = 0;
        }
    },
    inhibit: function (sec) {
        Notif.inhibed = true;

        if (sec == null) sec = 5;

        setTimeout(function () {
            Notif.inhibed = false;
        },
            sec * 1000);
    },
    refresh: function (keys) {
        var counters = document.querySelectorAll('.counter');
        for (i = 0; i < counters.length; i++) {
            var n = counters[i];
            if (n.dataset.key != null
                && keys[n.dataset.key] != null) {
                if (keys[n.dataset.key] > 100) keys[n.dataset.key] = '+100';
                n.innerHTML = keys[n.dataset.key];
            }
        }

        for (var key in keys) {
            var counter = keys[key];
            Notif.setTab(key, counter);
        }

        Notif.displayTab();
    },
    counter: function (key, counter) {
        var counters = document.querySelectorAll('.counter');
        for (i = 0; i < counters.length; i++) {
            var n = counters[i];
            if (n.dataset.key != null
                && n.dataset.key == key) {
                var htmlCounter = String(counter);
                if (counter > 100) htmlCounter = '+100';
                if (counter == 0) htmlCounter = '';
                n.innerHTML = htmlCounter;
            }
        }

        Notif.setTab(key, counter);
        Notif.displayTab();
    },
    setTab: function (key, counter) {
        if (Notif.tab_counter1_key == key) {
            Notif.tab_counter1 = counter;
        }
        if (Notif.tab_counter2_key == key) {
            Notif.tab_counter2 = counter;
        }
    },
    setTitle: function (title) {
        Notif.document_title = title;
        Notif.displayTab();
    },
    setCallStatus: function (status) {
        Notif.call_status = status;
        Notif.displayTab();
    },
    displayTab: function () {
        document.title = (Notif.call_status != null)
            ? Notif.call_status + ' | '
            : '';

        if (Notif.tab_counter1 == 0 && Notif.tab_counter2 == 0) {
            MovimFavicon.counter(0, 0);
            document.title += Notif.document_title;

            if (typeof window.electron !== 'undefined')
                window.electron.notification(false);

            if (typeof window.rambox !== 'undefined')
                window.rambox.setUnreadCount(0);
        } else {
            document.title +=
                Notif.tab_counter1
                + '∣'
                + Notif.tab_counter2
                + ' • '
                + Notif.document_title;

            MovimFavicon.counter(Notif.tab_counter1, Notif.tab_counter2);

            if (typeof window.electron !== 'undefined')
                window.electron.notification(Notif.tab_counter1 + Notif.tab_counter2);

            if (typeof window.rambox !== 'undefined')
                window.rambox.setUnreadCount(Notif.tab_counter1 + Notif.tab_counter2);
        }
    },
    current: function (key) {
        Notif.notifs_key = key;
        Notif_ajaxCurrent(Notif.notifs_key);
    },
    snackbar: function (html, time) {
        if (Notif.inhibed == true) return;

        target = document.getElementById('snackbar');

        if (target) {
            target.innerHTML = html;
        }

        setTimeout(function () {
            Notif.snackbarClear();
        }, time * 1000);
    },
    snackbarClear: function () {
        target = document.getElementById('snackbar');
        target.innerHTML = '';
    },
    desktop: function (title, body, picture, action, execute, force) {
        if (!force && (Notif.inhibed == true
            || Notif.focused
            || typeof Notification === 'undefined')) return;

        if (Notification.permission === 'granted') {
            Notif.checkPushSubscription();

            var notification = new Notification(
                title,
                { icon: picture, body: body, tag: action }
            );

            if (action !== null) {
                notification.onclick = function () {
                    window.location.href = action;
                    Notif.snackbarClear();
                    this.close();
                }
            }

            if (execute !== null) {
                notification.onclick = function () {
                    eval(execute);
                    Notif.snackbarClear();
                    this.close();
                }
            }
        } else if (Notification.permission !== 'denied') {
            Notif_ajaxRequest();
        }
    },
    request: function () {
        Notification.requestPermission().then((permission) => {
            (permission == 'granted')
                ? Notif_ajaxRequestGranted()
                : Notif_ajaxRequestDenied();
        });
    },
    focus: function () {
        if (Notif.focused == false) {
            Notif.focused = true;
            Notif.current(Notif.notifs_key);
        }
    },
    checkPushSubscription() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistration(BASE_URI + 'sw.js').then((registration) => {
                if (!registration) return;

                registration.pushManager.getSubscription().then((pushSubscription) => {
                    if (pushSubscription == null) {
                        // Register the push notification subcription
                        registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: VAPID_PUBLIC_KEY
                        }).then(function (subscription) {
                            Notif.registerPushSubscription(subscription);
                        }).catch(function (e) {
                            console.error('Unable to subscribe to push', e);
                        });
                    } else {
                        Notif_ajaxHttpTouchPushSubscription(pushSubscription.endpoint);
                    }
                });
            });
        }
    },
    registerPushSubscription(subscription) {
        Notif_ajaxRegisterPushSubscrition(
            subscription.endpoint,
            MovimUtils.arrayBufferToBase64(subscription.getKey('auth')),
            MovimUtils.arrayBufferToBase64(subscription.getKey('p256dh')),
            window.navigator.userAgent
        );
    }
}

Notif.document_title_init = document.title;

if (typeof MovimWebsocket != 'undefined') {
    MovimWebsocket.attach(function () {
        Notif.document_title = Notif.document_title_init;
        Notif.tab_counter1 = Notif.tab_counter2 = 0;
        Notif_ajaxGet();
        Notif.current(Notif.notifs_key);

        MovimEvents.registerWindow('blur', 'notifs', () => {
            Notif.focused = false;
            Notif_ajaxCurrent('blurred');
        });

        MovimEvents.registerWindow('focus', 'notifs', () => Notif.focus());
    });
}
