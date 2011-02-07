function getMessageText()
{
    chatInput = document.getElementById('chatInput');
    chat = document.getElementById('chatMessages');
    var text = chatInput.value;

    chat.innerHTML = chat.innerHTML + '<p class="message">Me: ' + text + "</p>";
    chatInput.value = "";

    return text;
}

function getDest()
{
    return document.getElementById('chatTo').value;
}