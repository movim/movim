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
    status = document.querySelectorAll('#profile #status');
    console.log(status);
    return status.value;
}

/*function getStatusText()
{
    var dest = document.getElementById('profilestatustext');
    var stuff = dest.value;
    return stuff;
}

function getStatusShow()
{
    return document.querySelector('#presenceimage').className;
}

function showPresence(n) {
    var list = document.querySelector('#presencelist');
    if(list.style.display == "block") {
        list.style.display = "none";
        n.style.backgroundColor = "transparent";
        n.style.color = "#4E4E4E";
    } else {
        list.style.display = "block";
        n.style.backgroundColor = "#444";
        n.style.color = "white";
    }
}

function closePresence() {
    document.querySelector('#presencelist').style.display = "none";
    document.querySelector('#presencebutton').style.backgroundColor = "transparent";
}*/
