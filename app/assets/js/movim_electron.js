/**
 * @brief Open the URLs in the default browser
 */
if (typeof window.electron !== 'undefined') {
    document.addEventListener('click', function(event) {
        if (event.target.target == '_blank'
        || (event.target.hostname != null && event.target.hostname != BASE_HOST)) {
            event.preventDefault();
            window.electron.openExternal(event.target.href);
        }
    });
}
