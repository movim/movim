<div id="subscribe">
    <ul class="simple thick">
        <li class="condensed">
            <span>{$c->__('subscribe.title')}</span>
            <p>{$c->__('subscribe.info')}</p>
        </li>
    </ul>

    <ul class="thick active">
        {loop="$servers"}
        <li
            class="block condensed"
            onclick="movim_redirect('{$c->route('accountnext', array($value->fn->text, false))}')">
            <span class="icon bubble color {$value->fn->text|stringToColor}">
                {if="$value->checked"}
                    <i class="fa md-star-outline"></i>
                {else}
                    {$value->fn->text|firstLetterCapitalize}
                {/if}
            </span>
            <div class="server {if="$value->checked"}star{/if}">
                <span class="info">
                <img
                    class="flag"
                    title="{$value->adr->country}" 
                    alt="{$value->adr->country}" 
                    src="{$c->flagPath($value->adr->country)}"/>  
                </span>
                <span>{$value->fn->text}</span>
         
                <p>
                    {$value->note->text}<br />
                    <a target="_blank" href="{$value->url->uri}">
                        {$value->url->uri}
                    </a>
                </p>
            </div>
        </li>
        {/loop}

        <li class="block condensed">
            <span class="icon bubble color orange">
                <i class="md md-add-circle-outline"></i>
            </span>
            <span>{$c->__('subscribe.server_question')}</span>
            <p>
                {$c->__('subscribe.server_contact')} • <a href="http://movim.eu/">http://movim.eu/</a>
            </p>
        </li>
    </ul>
</div>
