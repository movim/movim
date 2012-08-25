function showPresence(n)
{
    n.style.display = 'none';
    buttons = document.querySelectorAll('.presence_button.merged');
    for(i = 0; i < buttons.length; i++) {
        buttons[i].style.display = 'inline';
    }
}

function getStatusText()
{
    status = document.querySelector('#profile #status').value;
    return encodeURIComponent(status);
}

movim_add_onload(function()
{
    movim_textarea_autoheight(document.querySelector('#profile #status'));
});
