<div id="serverresult" class="padded">
    <a class="button color purple oppose icon search" href="{$myserver}">{$c->__('discover_my_server')}</a>
    <h2><i class="fa fa-comments-o"></i> {$c->__('discussion_servers')}</h2>
    <ul class="list">
        {$servers}
    </ul>
</div>

<div class="clear"></div>

<div class="padded">
    <h2><i class="fa fa-clock-o"></i> {$c->__('last_registered')}</h2>

    <ul class="list">
    {loop="$users"}
        <li class="block">
            <img class="avatar" src="{$value->getPhoto('xs')}"/>
            <a href="{$c->route('friend', $value->jid)}">
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

    <div class="clear"></div>
</div>
