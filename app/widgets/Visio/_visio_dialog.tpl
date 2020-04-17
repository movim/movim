<section id="visio_dialog">
    {$url = $contact->getPhoto('l')}
    <ul class="list thick">
        <li>
            <div>
                {if="$url"}
                    <p class="center">
                        <img src="{$url}">
                    </p>
                {/if}
                <p class="normal center">{$contact->truename}</p>
                <p class="center">{$c->__('visio.calling')}</p>
            </div>
        </li>
    </ul>
</section>
<div class="no_bar">
    <button onclick="VisioLink_ajaxReject('{$from}', '{$id}'); Dialog_ajaxClear()" class="button flat">
        {$c->__('button.refuse')}
    </button>
    <button onclick="VisioLink.openVisio('{$from}', '{$id}'); Dialog_ajaxClear();" class="button flat green">
        {$c->__('button.reply')}
    </button>
</div>
