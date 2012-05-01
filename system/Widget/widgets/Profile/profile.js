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
    status = document.querySelector('#profile #status');
    console.log(status.value);
    return encodeURIComponent(status.value);
}
