function fillExample(login, pass) {
    document.querySelector('#login').value = login;
    document.querySelector('#pass').value = pass;
}

movim_add_onload(function()
{
    if(localStorage.username != null)
        document.querySelector('#login').value = localStorage.username;

    form = document.querySelector('form[name="login"]');
    
    form.onsubmit = function(e) {
        e.preventDefault();

        var button = this.querySelector('input[type=submit]');
        button.className = 'button color orange icon yes';
        button.value = button.dataset.loading;
        
        eval(this.dataset.action);

        localStorage.username = document.querySelector('#login').value;
    }; 
});
