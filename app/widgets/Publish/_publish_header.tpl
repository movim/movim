<div>
    <span class="on_desktop icon"><i class="md md-create"></i></span>
    <h2>{$c->__('publish.title')}</h2>
</div>
<div>
    <div class="return active r3 condensed"
        onclick="
            if(typeof Post_ajaxClear === 'function') {
                Post_ajaxClear();
                Header_ajaxReset('news');
                MovimTpl.hidePanel();
            } else {
                Group_ajaxGetItems('{$server}', '{$node}');
            }">
        <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
        <h2>{$c->__('publish.new')}</h2>
        <h4>
            {if="$item != null && $item->node != 'urn:xmpp:microblog:0'"}
                {if="$item->name"}
                    {$item->name}
                {else}
                    {$item->node}
                {/if}
            {else}
                {$c->__('page.blog')}
            {/if}
        </h4>
    </div>
    <ul class="active">
        <li onclick="Publish_ajaxHelp()">
            <span class="icon">
                <i class="md md-help"></i>
            </span>
        </li>
        <li onclick="Publish_ajaxPreview(movim_form_to_json('post'))">
            <span class="icon">
                <i class="md md-remove-red-eye"></i>
            </span>
        </li>
        <li onclick="Publish_ajaxPublish(movim_form_to_json('post'))">
            <span class="icon">
                <i class="md md-send"></i>
            </span>
        </li>
    </ul>
</div>
