var DesktopNotification = Notification;

var Notification = {
    inhibed : false,
    focused : false,
    tab_counter1 : 0,
    tab_counter2 : 0,
    tab_counter1_key : 'chat',
    tab_counter2_key : 'news',
    document_title_init : null,
    document_title : document.title,
    notifs_key : '',
    favicon : null,

    audioCall : null,

    incomingMessage : function ()
    {
        if (NOTIFICATION_CHAT) {
            // From https://free-mobi.org/ringtones/sms/sms-sound-2
            var tone = new Audio('theme/audio/message.ogg');
            tone.play();
        }
    },
    incomingCall : function ()
    {
        if (NOTIFICATION_CALL) {
            // From https://www.zedge.net/ringtone/466d15be-8fa0-32a1-b3dc-62c12a86b6da
            Notification.audioCall = new Audio('theme/audio/call.ogg');
            Notification.audioCall.addEventListener('ended', function() {
                this.currentTime = 0;
                this.play();
            }, false);
            Notification.audioCall.play();
        }
    },
    incomingAnswer : function ()
    {
        if (Notification.audioCall) {
            Notification.audioCall.pause();
            Notification.audioCall.currentTime = 0;
        }
    },
    inhibit : function(sec) {
        Notification.inhibed = true;

        if (sec == null) sec = 5;

        setTimeout(function() {
                Notification.inhibed = false;
            },
            sec*1000);
    },
    refresh : function(keys) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if (n.dataset.key != null
            && keys[n.dataset.key] != null) {
                if (keys[n.dataset.key] > 100) keys[n.dataset.key] = '+100';
                n.innerHTML = keys[n.dataset.key];
            }
        }

        for(var key in keys) {
            var counter = keys[key];
            Notification.setTab(key, counter);
        }

        Notification.displayTab();
    },
    counter : function(key, counter) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if (n.dataset.key != null
            && n.dataset.key == key) {
                var htmlCounter = String(counter);
                if (counter > 100) htmlCounter = '+100';
                if (counter == 0) htmlCounter = '';
                n.innerHTML = htmlCounter;
            }
        }

        Notification.setTab(key, counter);
        Notification.displayTab();
    },
    setTab : function(key, counter) {
        if (Notification.tab_counter1_key == key) {
            Notification.tab_counter1 = counter;
        }
        if (Notification.tab_counter2_key == key) {
            Notification.tab_counter2 = counter;
        }
    },
    setTitle : function (title) {
        Notification.document_title = title;
        Notification.displayTab();
    },
    displayTab : function() {
        if (Notification.tab_counter1 == 0 && Notification.tab_counter2 == 0) {
            MovimFavicon.counter(0, 0);
            document.title = Notification.document_title;

            if (typeof window.electron !== 'undefined')
                window.electron.notification(false);

            if (typeof window.rambox !== 'undefined')
                window.rambox.setUnreadCount(0);
        } else {
            document.title =
                Notification.tab_counter1
                + '∣'
                + Notification.tab_counter2
                + ' • '
                + Notification.document_title;

            MovimFavicon.counter(Notification.tab_counter1, Notification.tab_counter2);

            if (typeof window.electron !== 'undefined')
                window.electron.notification(Notification.tab_counter1 + Notification.tab_counter2);

            if (typeof window.rambox !== 'undefined')
                window.rambox.setUnreadCount(Notification.tab_counter1 + Notification.tab_counter2);
        }
    },
    current : function(key) {
        Notification.notifs_key = key;
        Notification_ajaxCurrent(Notification.notifs_key);
    },
    snackbar : function(html, time) {
        if (Notification.inhibed == true) return;

        target = document.getElementById('snackbar');

        if (target) {
            target.innerHTML = html;
        }

        setTimeout(function() {
            Notification.snackbarClear();
        }, time*1000);
    },
    snackbarClear : function() {
        target = document.getElementById('snackbar');
        target.innerHTML = '';
    },
    desktop : function(title, body, picture, action, execute, force) {
        if (!force && (Notification.inhibed == true
        || Notification.focused
        || typeof DesktopNotification === 'undefined')) return;

        if (DesktopNotification.permission === 'granted') {
            Notification.checkPushSubscription();

            var notification = new DesktopNotification(
                title,
                { icon: picture, body: body, tag: action }
            );

            if (action !== null) {
                notification.onclick = function() {
                    window.location.href = action;
                    Notification.snackbarClear();
                    this.close();
                }
            }

            if (execute !== null) {
                notification.onclick = function() {
                    eval(execute);
                    Notification.snackbarClear();
                    this.close();
                }
            }
        } else if (DesktopNotification.permission !== 'denied') {
            Notification_ajaxRequest();
        }
    },
    request : function() {
        DesktopNotification.requestPermission().then((permission) => {
            (permission == 'granted')
                ? Notification_ajaxRequestGranted()
                : Notification_ajaxRequestDenied();
        });
    },
    focus : function() {
        if (Notification.focused == false) {
            Notification.focused = true;
            Notification.current(Notification.notifs_key);
        }
    },
    checkPushSubscription() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistration('sw.js').then((registration) => {
                if (!registration) return;

                registration.pushManager.getSubscription().then((pushSubscription) => {
                    if (pushSubscription == null) {
                        // Register the push notification subcription
                        registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: VAPID_PUBLIC_KEY
                        }).then(function(subscription) {
                            Notification.registerPushSubscription(subscription);
                        }).catch(function(e) {
                            console.error('Unable to subscribe to push', e);
                        });
                    } else {
                        Notification_ajaxHttpTouchPushSubscription(pushSubscription.endpoint);
                    }
                });
            });
        }
    },
    registerPushSubscription(subscription) {
        Notification_ajaxRegisterPushSubscrition(
            subscription.endpoint,
            MovimUtils.arrayBufferToBase64(subscription.getKey('auth')),
            MovimUtils.arrayBufferToBase64(subscription.getKey('p256dh')),
            window.navigator.userAgent
        );
    }
}

Notification.document_title_init = document.title;

if (typeof MovimWebsocket != 'undefined') {
    MovimWebsocket.attach(function() {
        Notification.document_title = Notification.document_title_init;
        Notification.tab_counter1 = Notification.tab_counter2 = 0;
        Notification_ajaxGet();
        Notification.current(Notification.notifs_key);

        window.addEventListener('blur', function() {
            Notification.focused = false;
            Notification_ajaxCurrent('blurred');
        });

        movimAddFocus(function() {
            Notification.focus();
        });
    });
}
