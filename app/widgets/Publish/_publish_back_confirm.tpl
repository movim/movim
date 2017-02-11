<section>
    <h4 class="gray">{$c->__('publish.form_filled')}</h4>
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.bool_no')}
    </button>
    <button onclick="Publish.headerBack('{$server}', '{$node}', true); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.bool_yes')}
    </button>
</div>
