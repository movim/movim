<div>
    <span class="icon"><i class="zmdi zmdi-accounts"></i></span>
    <h2>{$c->__('page.contacts')}</h2>
</div>
<div>
    {if="$contactr != null"}
        <ul class="active">
            <li onclick="{$edit}">
                <span class="icon">
                    <i class="zmdi zmdi-edit"></i>
                </span>
            </li>
            <li onclick="{$delete}">
                <span class="icon">
                    <i class="zmdi zmdi-delete"></i>
                </span>
            </li>
        </ul>
        <div class="return active r2" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();">
            <span id="back" class="icon" ><i class="zmdi zmdi-arrow-back"></i></span>
            <h2>{$contactr->getTrueName()}</h2>
        </div>
    {else}
        {if="$contact != null"}
            <ul class="active">
                <li onclick="Roster_ajaxDisplaySearch('{$jid}')">
                    <span class="icon">
                        <i class="zmdi zmdi-account-add"></i>
                    </span>
                </li>
            </ul>
            <div class="return active r2" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();">
                <span id="back" class="icon" ><i class="zmdi zmdi-arrow-back"></i></span>
                <h2>{$contact->getTrueName()}</h2>
            </div>
        {else}
            <ul class="active">
                <li onclick="Roster_ajaxDisplaySearch('{$jid}')">
                    <span class="icon">
                        <i class="zmdi zmdi-account-add"></i>
                    </span>
                </li>
            </ul>
            <div class="return active r2" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();">
                <span id="back" class="icon" ><i class="zmdi zmdi-arrow-back"></i></span>
                <h2>{$jid}</h2>
            </div>
        {/if}
    {/if}
</div>
