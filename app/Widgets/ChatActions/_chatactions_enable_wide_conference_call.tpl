<section>
    <h3>{$c->__('wide_conference_call.enable_title')}</h3>
    <ul class="list">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">video_call</i>
            </span>
            <div>
                <p></p>
                <p class="all">{$c->__('wide_conference_call.enable_info')}</p>
                <br />
                <p class="all">{$c->__('wide_conference_call.enable_info2')}</p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="RoomsUtils_ajaxInviteSFU('{$conference->conference}', '{$sfu->server}'); Dialog_ajaxClear()" class="button flat">
        {$c->__('button.enable')}
    </button>
</footer>
