function getStatusText()
{
    var dest = document.getElementById('statusText');
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
    } else {
        list.style.display = "block";
        n.style.backgroundColor = "#444";
    }
}

function closePresence() {
    document.querySelector('#presencelist').style.display = "none";
    document.querySelector('#presencebutton').style.backgroundColor = "transparent";
}
