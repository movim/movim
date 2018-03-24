<ul class="list middle">
    <li>
        {if="$in_roster"}
            <span class="control icon active gray" onclick="ContactHeader_ajaxEditContact('{$contact->id|echapJS}')">
                <i class="zmdi zmdi-edit"></i>
            </span>
            <span class="control icon active gray" onclick="ContactHeader_ajaxDeleteContact('{$contact->id|echapJS}')">
                <i class="zmdi zmdi-delete"></i>
            </span>
        {else}
            <span class="control icon active gray" onclick="ContactActions_ajaxAddAsk('{$contact->id}')">
                <i class="zmdi zmdi-account-add"></i>
            </span>
        {/if}
        <span class="control active icon gray on_mobile" onclick="ContactActions_ajaxGetDrawer('{$contact->id}')">
            <i class="zmdi zmdi-more"></i>
        </span>
        <span class="primary icon active gray" onclick="history.back()">
            <i class="zmdi zmdi-arrow-back"></i>
        </span>
        <p class="line">
            {$contact->truename}
        </p>
        <p class="line">{$contact->id}</p>
    </li>
</ul>

