/**
 * @brief Open the URLs in the default browser
 */
if(typeof require !== 'undefined') {
    document.addEventListener('click', function(event) {
        if(event.target.target == '_blank'
        || (event.target.hostname != null && event.target.hostname != BASE_HOST)) {
            event.preventDefault();
            var shell = require('electron').shell;
            shell.openExternal(event.target.href);
        }
    });
}
