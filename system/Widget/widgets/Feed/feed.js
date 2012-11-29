function showPosts(n, me) {
    feed = document.querySelector('#feedcontent');
    posts = feed.children;

    for(i = 0; i < posts.length; i++) {
        if(me == true) {
            if(posts.item(i).className.split(' ', 2)[1] != 'me')
                posts.item(i).style.display = 'none';
        }
        else
            posts.item(i).style.display = 'block';

    }
    
    tabs = n.parentNode.children;

    for(i = 0; i < tabs.length; i++)
        tabs[i].className = '';
    n.className = 'on';
}

function getFeedMessage() {
    var text = document.querySelector('#feedmessagecontent');
    message = text.value;
    text.value = '';
    text.blur();
    return encodeURIComponent(message);
}

function frameHeight(n) {
    if(n.className == 'button tiny icon add merged left') {
        n.className = 'button tiny icon rm merged left';
    document.querySelector('iframe#feedmessagecontent-frame').style.height = '400px';
    } else {
        n.className = 'button tiny icon add merged left';
        document.querySelector('iframe#feedmessagecontent-frame').style.height = '50px';
    }
}

function richText(n) {
    if(n.className == 'button tiny icon yes merged right') {
        n.className = 'button tiny icon no merged right';
        document.querySelector('.menueditor').style.display = 'block';
    } else {
        n.className = 'button tiny icon yes merged right';
        document.querySelector('.menueditor').style.display = 'none';
    }
}
