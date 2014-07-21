<div id="subscribe">
    <h1 class="paddedtopbottom">{$c->__('subscribe.title')}</h1>

    <p class="paddedtopbottom">{$c->__('subscribe.info')}</p>

    {loop="$servers"}
    <div
        class="block"
        onclick="movim_redirect('{$c->route('accountnext', array($value->fn->text, false))}')">
        <div class="server {if="$value->checked"}star{/if}">
            <h1>{$value->fn->text}</h1>

            <img
                class="flag"
                title="{$value->adr->country}" 
                alt="{$value->adr->country}" 
                src="{$c->flagPath($value->adr->country)}"/>           
            <p>{$value->note->text}</p>

            <a target="_blank" href="{$value->url->uri}">
                {$value->url->uri}
            </a>
        </div>
    </div>
    {/loop}

    <div class="block">
        <div class="server">
            <h1>{$c->__('subscribe.server_question')}</h1>

            <p>
                {$c->__('subscribe.server_contact')} • <a href="http://movim.eu/">http://movim.eu/</a>
            </p>
        </div>
    </div>
</div>

