function showPosts(n, me) {
    feed = document.querySelector('#feedcontent');
    posts = feed.children;

    for(i = 0; i < posts.length; i++) {
        console.log(posts.item(i).className);
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
