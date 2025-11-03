{if="$contacts->isNotEmpty()"}
<ul class="list spin middle flex card shadow compact active {$disposition}">
    <li class="subheader">
        <div>
            <p>{$c->__('contactssuggestions.for_you')}</p>
        </div>
    </li>
    {loop="$contacts"}
        <li title="{$value->truename}" class="block {if="$key > 2"}on_desktop{/if}">
            <span class="chip active color oppose" title="{$c->__('communityheader.follow')}"
                onclick="ContactsSuggestions_ajaxSubscribe('{$value->id|echapJS}'); ContactsSuggestions.submit(this);">
                {$c->__('communityheader.follow')}
            </span>
            <span class="primary icon bubble" onclick="MovimUtils.reload('{$c->route('contact', $value->id)}')">
                <img src="{$value->getPicture(\Movim\ImageSize::M)}">
            </span>
            <div onclick="MovimUtils.reload('{$c->route('contact', $value->id)}')">
                <p>{$value->truename}</p>
                <p>{$value->id}</p>
            </div>
        </li>
    {/loop}
</ul>
{/if}