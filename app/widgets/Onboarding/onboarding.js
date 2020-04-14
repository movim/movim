var Onboarding = {
    check: function() {
        if (localStorage.getItem('onboardingPublic') === null) {
            Onboarding_ajaxAskPublic();
        } else if (localStorage.getItem('onboardingPopups') === null) {
            Onboarding_ajaxAskPopups();
        }
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
