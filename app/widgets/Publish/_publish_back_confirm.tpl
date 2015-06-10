<section>
    <h4 class="gray">{$c->__('publish.form_filled')}</h4>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.no')}
    </a>
    <a onclick="Publish.headerBack('{$server}', '{$node}', true); Dialog.clear();" class="button flat">
        {$c->__('button.yes')}
    </a>
</div>
