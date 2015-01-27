<div>
    <span class="icon"><i class="md md-people"></i></span>
    <h2>{$c->__('page.contacts')}</h2>
</div>
<div>
    {if="$contactr != null"}
        <ul class="active">
            <li onclick="{$edit}">
                <span class="icon">
                    <i class="md md-edit"></i>
                </span>
            </li>
            <li onclick="{$delete}">
                <span class="icon">
                    <i class="md md-delete"></i>
                </span>
            </li>
        </ul>
        <h2 class="active r2" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();">
            <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
            {$contactr->getTrueName()}
        </h2>
    {else}
        {if="$contact != null"}
            <ul>
                <li onclick="Roster_ajaxDisplaySearch('{$jid}')">
                    <span class="icon">
                        <i class="md md-person-add"></i>
                    </span>
                </li>
            </ul>
            <h2 class="active r2" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();">
                <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
                {$contact->getTrueName()}
            </h2>
        {else}
            <ul>
                <li onclick="Roster_ajaxDisplaySearch('{$jid}')">
                    <span class="icon">
                        <i class="md md-person-add"></i>
                    </span>
                </li>
            </ul>
            <h2 class="active r2" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();">
                <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
                {$jid}
            </h2>
        {/if}
    {/if}
</div>
