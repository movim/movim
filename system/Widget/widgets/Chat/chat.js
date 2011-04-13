function getMessageText()
{
    chatInput = document.getElementById('chatInput');
    var text = chatInput.value;

    movim_prepend(['chatMessages', '<p class="message">Me: ' + text + "</p>"]);
    chatInput.value = "";

    return text;
}

function getDest()
{
    var dest = document.getElementById('chatTo');
    var stuff = dest.value;
    return stuff;
}
