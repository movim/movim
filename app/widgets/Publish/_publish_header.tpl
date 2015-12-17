<div>
    <ul class="list middle">
        <li>
            <span class="primary on_desktop icon"><i class="zmdi zmdi-edit"></i></span>
            <p>{$c->__('publish.title')}</p>
        </li>
    </ul>
</div>
<div>
    <ul class="list middle active">
        <li>
            <span id="back" class="primary icon" onclick="Publish.headerBack('{$server}', '{$node}', false)">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            <span class="control icon" onclick="Publish_ajaxHelp()">
                <i class="zmdi zmdi-help"></i>
            </span>
            <span class="control icon" onclick="Publish_ajaxPreview(movim_form_to_json('post'))">
                <i class="zmdi zmdi-eye"></i>
            </span>
            <span class="control icon" id="button_send"
            onclick="Publish.disableSend(); Publish_ajaxPublish(movim_form_to_json('post'));">
                <i class="zmdi zmdi-mail-send"></i>
            </span>

            {if="$post != false"}
                <p>{$c->__('publish.edit')}</p>
            {else}
                <p>{$c->__('publish.new')}</p>
            {/if}
            <p>
                {if="$item != null && $item->node != 'urn:xmpp:microblog:0'"}
                    {if="$item->name"}
                        {$item->name}
                    {else}
                        {$item->node}
                    {/if}
                {else}
                    {$c->__('page.blog')}
                {/if}
            </p>
        </li>
    </ul>
</div>
