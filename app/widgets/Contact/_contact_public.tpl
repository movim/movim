{loop="$users"}
    <li class="block" style="background-image: url();" onclick="Contact_ajaxGetContact('{$value->jid}', {$page});">
        {$url = $value->getPhoto('s')}
        {if="$url"}
            <span class="primary icon bubble">
                <img src="{$url}">
            </span>
        {else}
            <span class="primary icon bubble color {$value->jid|stringToColor}">
                <i class="zmdi zmdi-account"></i>
            </span>
        {/if}

        <p class="normal">
            {if="$value->getAge()"}
                <span class="info">{$c->__('age.years', $value->getAge())}</span>
            {/if}
            {if="$value->getGender()"}
                <span class="info">{$value->getGender()}</span>
            {/if}
            {$value->getTrueName()}
        </p>

        {if="$value->description != ''"}
        <p>
            {$value->description|strip_tags}
        </p>
        {/if}
    </li>
{/loop}
{if="$pages"}
    <li class="block">
        <span class="primary icon gray">
            <i class="zmdi zmdi-book"></i>
        </span>
        {loop="$pages"}
            <p>
                <a onclick="Contact_ajaxPublic('{$key}');" class="button flat {if="$key == $page"}on{/if}">{$key+1}</a>
            </p>
        {/loop}
    </li>
{/if}
