<div id="discover">
    <h1 class="paddedtopbottom">{$c->__('last')}</h1>

    <ul class="list paddedtop">
    {loop="$users"}
        <li class="block">
            <img class="avatar" src="{$value->getPhoto('xs')}"/>
            <a href="{$c->route('blog', array($value->jid, 'urn:xmpp:microblog:0'))}">
                {if="$value->getAge()"}
                    <span class="tag blue">{$value->getAge()}</span>
                {/if}
                {if="$value->getGender()"}
                    <span class="tag green">{$value->getGender()}</span>
                {/if}
                {if="$value->getMarital()"}
                    <span class="tag yellow">{$value->getMarital()}</span>
                {/if}
                <span class="content">{$value->getTrueName()}</span>
                <span class="desc">{$value->description|strip_tags}</span>
            </a>
        </li>
    {/loop}
    </ul>

</div>
