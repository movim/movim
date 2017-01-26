var Onboarding = {
    check: function() {
        if(localStorage.getItem('onboardingPublic') === null) {
            Onboarding_ajaxAskPublic();
        } else if(localStorage.getItem('onboardingNotifications') === null) {
            Onboarding_ajaxAskNotifications();
        }
    },

    disableNotifications: function() {
        localStorage.setItem('onboardingNotifications', true);
        Onboarding.check();
    },

    enableNotifications: function() {
        localStorage.setItem('onboardingNotifications', true);

        DesktopNotification.requestPermission(function (status) {
            if(DesktopNotification.permission !== status) {
                DesktopNotification.permission = status;
            }
        });

        Onboarding.check();
    },

    setPublic: function() {
        localStorage.setItem('onboardingPublic', true);
        Onboarding.check();
    }
}

MovimWebsocket.attach(function() {
    Onboarding.check();
});

