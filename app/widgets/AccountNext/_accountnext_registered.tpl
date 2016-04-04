<div class="placeholder icon clipboard">
    <h4>{$c->__('create.successfull')}</h4>

    <h2 id="username">username@server.com</h2>

    <a class="button color" onclick="remoteUnregister(); MovimWebsocket.attach(function() {movim_redirect('{$c->route('disconnect')}')});" href="#">
        {$c->__('page.login')}
    </a>
</div>
