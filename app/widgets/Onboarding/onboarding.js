var Onboarding = {
    check: function() {
        if(localStorage.getItem('onboardingPublic') === null) {
            Onboarding_ajaxAskPublic();
        } else if(localStorage.getItem('onboardingNotifications') === null) {
            Onboarding_ajaxAskNotifications();
        }
    },

    enableNotifications: function() {
        DesktopNotification.requestPermission(function (status) {
            if(DesktopNotification.permission !== status) {
                DesktopNotification.permission = status;
            }
        });

        Onboarding.check();
    },

    setNotifications: function() {
        localStorage.setItem('onboardingNotifications', true);
    },

    setPublic: function() {
        localStorage.setItem('onboardingPublic', true);
    }
}

MovimWebsocket.attach(function() {
    Onboarding.check();
});

