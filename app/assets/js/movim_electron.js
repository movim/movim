/**
 * @brief Open the URLs in the default browser
 */
if(typeof require !== 'undefined') {
    document.addEventListener('click', function(event) {
        if(event.target.target == '_blank') {
            event.preventDefault();
            var shell = require('electron').shell;
            shell.openExternal(event.target.href);
        }
    });

    const remote = require('electron').remote;
    const Menu = remote.Menu;
    const MenuItem = remote.MenuItem;

    var menu = new Menu();
    menu.append(new MenuItem({ label: "Cut", accelerator: "CmdOrCtrl+X", selector: "cut:" }));
    menu.append(new MenuItem({ label: "Select All", accelerator: "CmdOrCtrl+A", selector: "selectAll:" }));
    menu.append(new MenuItem({ label: "Copy", accelerator: "CmdOrCtrl+C", selector: "copy:" }));
    menu.append(new MenuItem({ label: "Paste", accelerator: "CmdOrCtrl+V", selector: "paste:" }));

    window.addEventListener('contextmenu', function (e) {
      e.preventDefault();
      menu.popup(remote.getCurrentWindow());
    }, false);
    /*
    var Menu = require("menu");

    var template = [{
        label: "Application",
        submenu: [
            { label: "About Application", selector: "orderFrontStandardAboutPanel:" },
            { type: "separator" },
            { label: "Quit", accelerator: "Command+Q", click: function() { app.quit(); }}
        ]}, {
        label: "Edit",
        submenu: [
            { label: "Undo", accelerator: "CmdOrCtrl+Z", selector: "undo:" },
            { label: "Redo", accelerator: "Shift+CmdOrCtrl+Z", selector: "redo:" },
            { type: "separator" },
            { label: "Cut", accelerator: "CmdOrCtrl+X", selector: "cut:" },
            { label: "Copy", accelerator: "CmdOrCtrl+C", selector: "copy:" },
            { label: "Paste", accelerator: "CmdOrCtrl+V", selector: "paste:" },
            { label: "Select All", accelerator: "CmdOrCtrl+A", selector: "selectAll:" }
        ]}
    ];

    Menu.setApplicationMenu(Menu.buildFromTemplate(template));*/

}
