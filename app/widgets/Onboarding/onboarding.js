var Onboarding = {
    check: function() {
        if (typeof DesktopNotification === 'undefined') {
            Onboarding.setNotifications();
        }

        if (localStorage.getItem('onboardingPublic') === null) {
            Onboarding_ajaxAskPublic();
        } else if (localStorage.getItem('onboardingNotifications') === null) {
            Onboarding_ajaxAskNotifications();
        } else if (localStorage.getItem('onboardingPopups') === null) {
            Onboarding_ajaxAskPopups();
        }
    },

    enableNotifications: function() {
        DesktopNotification.requestPermission(function (status) {
            if (DesktopNotification.permission !== status) {
                DesktopNotification.permission = status;
            }
        });
    },

    setNotifications: function() {
        localStorage.setItem('onboardingNotifications', true);
    },

    setPublic: function() {
        localStorage.setItem('onboardingPublic', true);
    },

    setPopups: function() {
        localStorage.setItem('onboardingPopups', true);
        window.open('?popuptest', '', 'width=100,height=100,status=0,titlebar=0,toolbar=0,menubar=0');
    }
}

MovimWebsocket.attach(function() {
    Onboarding.check();
});
