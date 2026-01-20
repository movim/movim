<section>
    <ul class="list thick">
        <li>
            <span class="primary icon bubble">
                <img src="{$contact->getPicture(\Movim\ImageSize::O)}">
            </span>
            <div>
                <p class="line">
                    <i class="material-symbols icon blue">call</i>
                    {$c->__('visiolobby.calling', $contact->truename)}
                </p>
                <p>{$c->__('visio.hang_up')}</p>
            </div>
        </li>
    </ul>
    <h4 class="gray">{$c->__('visio.hang_up_text')}</h4>
</section>
<footer>

    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button onclick="Visio_ajaxGoodbye('{$jid|echapJS}', '{$sid}'); Dialog_ajaxClear()" class="button color red">
        <i class="material-symbols">call_end</i>
    </button>
</futton>