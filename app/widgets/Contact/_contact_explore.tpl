<br />
<h2>{$c->__('explore')}</h2>

<ul class="flex card active thick">
{loop="$users"}
    <li class="{if="$value->description != ''"}condensed{/if} block" style="background-image: url();" onclick="Contact_ajaxGetContact('{$value->jid}');">
        <span class="icon bubble">
            <img src="{$value->getPhoto('m')}"/>
        </span>
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
</ul>
