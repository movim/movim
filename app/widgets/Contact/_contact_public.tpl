<ul class="list flex card active">
{loop="$users"}
    <li class="block" title="{$value->jid}" style="background-image: url();" onclick="Contact_ajaxGetContact('{$value->jid}', {if="$page"}{$page}{else}0{/if});">
        {$url = $value->getPhoto('l')}
        {if="$url"}
            <span class="primary icon bubble
            {if="$value->value"}
                status {$presencestxt[$value->value]}
            {/if}
            " style="background-image: url({$url});">
            </span>
        {else}
            <span class="primary icon bubble color {$value->jid|stringToColor}
            {if="$value->value"}
                status {$presencestxt[$value->value]}
            {/if}
            ">
                <i class="zmdi zmdi-account"></i>
            </span>
        {/if}

        <p class="normal">
            {$value->getTrueName()}
        </p>

        <p>
            {$value->description|strip_tags|truncate:80}
            {if="$value->description != ''"}<br />{/if}
            {if="$value->getAge()"}
                <span class="tag color gray">{$c->__('age.years', $value->getAge())}</span>
            {/if}
            {if="$value->getGender()"}
                <span class="tag color
                {if="$value->gender == 'M'"}blue{/if}
                {if="$value->gender == 'F'"}red{/if}
                ">{$value->getGender()}</span>
            {/if}
        </p>
    </li>
{/loop}
</ul>
<ul class="list">
{if="$pages"}
    <li>
        <p class="center">
            {loop="$pages"}
                <a onclick="Contact_ajaxPublic({$key});" class="button flat {if="$key == $page"}on{/if}">{$key+1}</a>
            {/loop}
        </p>
    </li>
{/if}
</ul>
