var Onboarding = {
    check: function() {
        if (localStorage.getItem('onboardingPublic') === null) {
            Onboarding_ajaxAskPublic();
        }
    },

    setPublic: function() {
        localStorage.setItem('onboardingPublic', true);
    }
}

MovimWebsocket.attach(function() {
    Onboarding.check();
});
