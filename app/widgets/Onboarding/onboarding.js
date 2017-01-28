var Onboarding = {
    check: function() {
        if(localStorage.getItem('onboardingPublic') === null) {
            Onboarding_ajaxAskPublic();
        } else if(localStorage.getItem('onboardingNotifications') === null) {
            Onboarding_ajaxAskNotifications();
        } else if(localStorage.getItem('onboardingPopups') === null) {
            Onboarding_ajaxAskPopups();
        }
    },

    enableNotifications: function() {
        DesktopNotification.requestPermission(function (status) {
            if(DesktopNotification.permission !== status) {
                DesktopNotification.permission = status;
            }
        });

        //Onboarding.check();
    },

    setNotifications: function() {
        localStorage.setItem('onboardingNotifications', true);
    },

    setPublic: function() {
        localStorage.setItem('onboardingPublic', true);
    },

    setPopups: function() {
        window.open('?popuptest', '', 'width=600,height=400,status=0,titlebar=0,toolbar=0,menubar=0');
        localStorage.setItem('onboardingPopups', true);
    }
}

MovimWebsocket.attach(function() {
    Onboarding.check();
});

