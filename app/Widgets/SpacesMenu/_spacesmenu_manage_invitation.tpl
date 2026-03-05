<section>
    <ul class="list thick">
        <li class="invite">
            <span class="primary icon bubble space symbol">
                {if="$info"}
                    <img src="{$info->getPicture(placeholder: $info->name)}">
                {else}
                    <i class="material-symbols">communities</i>
                {/if}
            </span>
            <span class="primary icon bubble">
                <img src="{$contact->getPicture(\Movim\ImageSize::M)}">
            </span>
            <div>
                <p>{$c->__('spaceinfo.pending_request', $contact->truename)}</p>
                <p>{$contact->id}</p>
            </div>
        </li>
    </ul>
    <ul class="list divided middle active">
        <li onclick="SpacesMenu_ajaxAcceptInvitation('{$server}', '{$node}', '{$contact->id}'); Dialog_ajaxClear();">
            <span class="primary icon gray">
                <i class="material-symbols">check</i>
            </span>
            <div>
                <p>{$c->__('spacesmenu.pending_subscription_add_title', $contact->truename)}</p>
            </div>
        </li>
        <li onclick="SpacesMenu_ajaxDenyInvitation('{$server}', '{$node}', '{$contact->id}'); Dialog_ajaxClear();">
            <span class="primary icon gray">
                <i class="material-symbols">do_not_disturb_on</i>
            </span>
            <div>
                <p>{$c->__('spacesmenu.pending_subscription_refuse_title', $contact->truename)}</p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
</footer>
