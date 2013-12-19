function loginButtonSet(val, loading) {
    document.querySelector('#submit').innerHTML = val;
}

function fillExample(login, pass) {
    document.querySelector('#login').value = login;
    document.querySelector('#pass').value = pass;
}

movim_add_onload(function()
{
    if(localStorage.username != null)
        document.querySelector('#login').value = localStorage.username;
});
