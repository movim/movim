<div>
    <span class="on_desktop icon"><i class="zmdi zmdi-edit"></i></span>
    <h2>{$c->__('publish.title')}</h2>
</div>
<div>
    <div class="return active r3 condensed"
        onclick="Publish.headerBack('{$server}', '{$node}', false)">
        <span id="back" class="icon" ><i class="zmdi zmdi-arrow-back"></i></span>
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
                <i class="zmdi zmdi-help"></i>
            </span>
        </li>
        <li onclick="Publish_ajaxPreview(movim_form_to_json('post'))">
            <span class="icon">
                <i class="zmdi zmdi-eye"></i>
            </span>
        </li>
        <li id="button_send"
            onclick="Publish.disableSend(); Publish_ajaxPublish(movim_form_to_json('post'));">
            <span class="icon">
                <i class="zmdi zmdi-mail-send"></i>
            </span>
        </li>
    </ul>
</div>
