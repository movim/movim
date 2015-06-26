<section>
    <h4 class="gray">{$c->__('publish.form_filled')}</h4>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.bool_no')}
    </a>
    <a onclick="Publish.headerBack('{$server}', '{$node}', true); Dialog.clear();" class="button flat">
        {$c->__('button.bool_yes')}
    </a>
</div>
