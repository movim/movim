function enterMovim(url) {
    window.location.href = url;
}

function loginButtonSet(val, loading) {
    document.querySelector('#submit').innerHTML = val;
    if(loading)
        document.querySelector('#submit').className = 'button icon loading';
    else
        document.querySelector('#submit').className = 'button icon yes';
}
