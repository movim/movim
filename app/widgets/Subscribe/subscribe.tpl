<div id="subscribe">
    <h1>{$c->t('Create a new account')}</h1>

    <p class="paddedtop">{$c->t('Movim is a decentralized social network, before creating a new account you need to choose a server to register.')}</p>

    <div class="paddedtop">
        {loop="$servers"}
        <div
            class="block {if="$value->checked"}star{/if}"
            onclick="movim_redirect('{$c->route('accountnext', array($value->fn->text, false))}')">
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
        {/loop}

        <div class="block">
            <h1>{$c->t('Your server here ?')}</h1>

            <p>
                {$c->t('Contact us to add yours to the officially supported servers list')} • <a href="http://movim.eu/">http://movim.eu/</a>
            </p>
        </div>
    </div>
</div>

