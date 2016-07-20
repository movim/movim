<section>
    <h3>{$c->__('error.title')}</h3>
    <br />
    <h4 class="gray">{$error}</h4>
</section>
<div>
    <span
        class="button flat oppose"
        onclick="MovimUtils.redirect('{$c->route('disconnect')}');">
        {$c->__('button.return')}
    </span>
</div>
