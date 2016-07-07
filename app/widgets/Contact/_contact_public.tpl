{loop="$users"}
    <li class="block" style="background-image: url();" onclick="Contact_ajaxGetContact('{$value->jid}', {if="$page"}{$page}{else}0{/if});">
        {$url = $value->getPhoto('l')}
        {if="$url"}
            <span class="primary icon thumb" style="background-image: url({$url});">
            </span>
        {else}
            <span class="primary icon thumb color {$value->jid|stringToColor}">
                <i class="zmdi zmdi-account"></i>
            </span>
        {/if}

        <p class="normal">
            {$value->getTrueName()}
            {if="$value->getAge()"}
                <span class="second">{$c->__('age.years', $value->getAge())}</span>
            {/if}
            {if="$value->getGender()"}
                <span class="second">{$value->getGender()}</span>
            {/if}
        </p>

        {if="$value->description != ''"}
        <p>
            {$value->description|strip_tags}
        </p>
        {/if}
    </li>
{/loop}
{if="$pages"}
    <li class="block large">
        <span class="primary icon gray">
            <i class="zmdi zmdi-book"></i>
        </span>
        <p>
            {loop="$pages"}
                <a onclick="Contact_ajaxPublic({$key});" class="button flat {if="$key == $page"}on{/if}">{$key+1}</a>
            {/loop}
        </p>
    </li>
{/if}
