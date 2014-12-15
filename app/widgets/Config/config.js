var Config = {
    load : function() {
        /*document.querySelector('form #nav_color a[type=button]').onclick = function() {
            document.querySelector('input[name=color]').value = '32434D';
        }

        document.querySelector('form #nav_color input[name=color]').onchange = function() {
            document.querySelector('nav').style.backgroundColor = '#'+this.value;
        }

        document.querySelector('form #font_size a[type=button]').onclick = function() {
            var slide = document.querySelector('input[name=size]');
            slide.value = 14;
            slide.onchange();
        }
        
        document.querySelector('form #font_size input[name=size]').onchange = function() {
            document.body.style.fontSize = this.value+'px';
            document.querySelector('#currentsize').innerHTML = this.value+'px';
        }*/
    }
}

movim_add_onload(function() {
    Config.load();
});
