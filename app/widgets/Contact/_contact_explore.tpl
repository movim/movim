<h2 class="padded">{$c->__('last_registered')}</h2>

<ul class="active">
{loop="$users"}
    <li class="condensed" onclick="Contact_ajaxGetContact('{$value->jid}');">
        <span class="icon bubble"><img class="avatar" src="{$value->getPhoto('m')}"/></span>
        <span>{$value->getTrueName()}</span>
        <p>
            {if="$value->getAge()"}
                <span class="tag blue on_desktop">{$value->getAge()}</span>
            {/if}
            {if="$value->getGender()"}
                <span class="tag green on_desktop">{$value->getGender()}</span>
            {/if}
            {if="$value->getMarital()"}
                <span class="tag yellow on_desktop">{$value->getMarital()}</span>
            {/if}
            <br/>
            <span class="desc on_desktop">{$value->description|strip_tags}</span>
        </p>
    </li>
{/loop}
</ul>
