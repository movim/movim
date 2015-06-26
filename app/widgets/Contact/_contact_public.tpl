{loop="$users"}
    <li class="{if="$value->description != ''"}condensed{/if} block" style="background-image: url();" onclick="Contact_ajaxGetContact('{$value->jid}');">
        {$url = $value->getPhoto('s')}
        {if="$url"}
            <span class="icon bubble">
                <img src="{$url}">
            </span>
        {else}
            <span class="icon bubble color {$value->jid|stringToColor}">
                <i class="zmdi zmdi-account"></i>
            </span>
        {/if}
        
        <span>{$value->getTrueName()}</span>
        
            {if="$value->getAge()"}
                <span class="info">{$c->__('age.years', $value->getAge())}</span>
            {/if}
            {if="$value->getGender()"}
                <span class="info">{$value->getGender()}</span>
            {/if}
            <!--
            {if="$value->getMarital()"}
                <span class="info">{$value->getMarital()}</span>
            {/if}
            -->
        
            {if="$value->description != ''"}
            <p>
                {$value->description|strip_tags}
            </p>
            {/if}
    </li>
{/loop}
{if="$pages"}
    <li class="block condensed">
        <span class="icon gray">
            <i class="zmdi zmdi-my-library-books"></i>
        </span>
        {loop="$pages"}
            <a onclick="Contact_ajaxPublic('{$key}');" class="button flat {if="$key == $page"}on{/if}">{$key+1}</a>
        {/loop}
    </li>
{/if}
