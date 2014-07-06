<li class="block">
    <img class="avatar" src="{$user->getPhoto('xs')}"/>
    <a href="{$c->route('friend', $user->jid)}">
        {if="$user->getAge()"}
            <span class="tag blue">{$user->getAge()}</span>
        {/if}
        {if="$gender"}
            <span class="tag green">{$gender}</span>
        {/if}
        {if="$marital"}
            <span class="tag yellow">{$marital}</span>
        {/if}
        <span class="content">{$user->getTrueName()}</span>
        <span class="desc">{$user->description|strip_tags}</span>
    </a>
</li>
