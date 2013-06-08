function showLogoutList() {
    hideNotifsList();
    
    movim_toggle_display('#logoutlist');
}

function hideLogoutList() {
    document.getElementById('logoutlist').style.display = 'none';
}
