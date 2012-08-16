function enterMovim(url) {
    window.location.href = url;
}

function loginButtonSet(val) {
    document.querySelector('#submit').value = val;
    document.querySelector('#submit').className = 'button icon loading';
}
