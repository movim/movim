var ContactsSuggestions = {
    submit: function (button) {
        button.innerHTML = '<i class="material-symbols spin">progress_activity</i>';
        button.classList.add('inactive');
    }
};

if (!MovimUtils.isMobile()) {
    ContactsSuggestions_ajaxHttpGet();
}