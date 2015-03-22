<section>
    <h3>{$c->__('error.title')}</h3>
    <br />
    <h4 class="gray">{$error}</h4>
</section>
<div>
    <span class="button flat oppose" onclick="remoteUnregister(); MovimWebsocket.attach(function() {movim_redirect('{$c->route('login')}')});">{$c->__('button.return')}</span>
</div>
