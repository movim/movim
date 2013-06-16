function loginButtonSet(val, loading) {
    document.querySelector('#submit').innerHTML = val;
}

function fillExample(login, pass) {
    document.querySelector('#login').value = login;
    document.querySelector('#pass').value = pass;
}
