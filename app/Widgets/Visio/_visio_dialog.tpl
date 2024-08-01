<section id="visio_dialog">
    <ul class="list thick">
        <li>
            <div>
                <p class="center">
                    <img src="{$contact->getPicture(\Movim\ImageSize::L)}">
                </p>
                <p class="normal center">
                    {if="$withvideo"}
                        {$c->__('visio.video_call')}
                    {else}
                        {$c->__('visio.audio_call')}
                    {/if}
                </p>
                <p class="center">{$contact->truename}</p>
                <p class="center">{$c->__('visio.calling')}</p>
            </div>
        </li>
    </ul>
</section>
<div class="no_bar">
    <button onclick="Visio_ajaxReject('{$from|echapJS}', '{$id}'); Dialog_ajaxClear()" class="button flat">
        {$c->__('button.refuse')}
    </button>
    <button onclick="Visio.prepare('{$from}', '{$id}', {if="$withvideo"}true{else}false{/if}); Dialog_ajaxClear();" class="button flat green">
        {$c->__('button.reply')}
    </button>
</div>
