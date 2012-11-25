function showLogoutList() {
    show = document.getElementById('logoutlist');
    
    hideNotifsList();

    if(show.style.display == 'block')
        show.style.display = 'none';
    else
        show.style.display = 'block';
}

function hideLogoutList() {
    document.getElementById('logoutlist').style.display = 'none';
}
