function postStart() {
    if(localStorage.postStart == 1) {
        Presence_ajaxSetStatus('boot');
        Presence_ajaxConfigGet();
        Presence_ajaxServerCapsGet();
        Presence_ajaxBookmarksGet();
        localStorage.postStart = 0;
    }
}

movim_add_onload(function()
{
    postStart();
});
