<span id="back" class="icon" onclick="MovimTpl.hidePanel(); Contact_ajaxClear();"><i class="md md-arrow-back"></i></span>
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
    <h2>{$contactr->getTrueName()}</h2>
{else}
    {if="$contact != null"}
        <ul>
            <li onclick="Roster_ajaxDisplaySearch('{$jid}')">
                <span class="icon">
                    <i class="md md-person-add"></i>
                </span>
            </li>
        </ul>
        <h2>{$contact->getTrueName()}</h2>
    {else}
        <ul>
            <li onclick="Roster_ajaxDisplaySearch('{$jid}')">
                <span class="icon">
                    <i class="md md-person-add"></i>
                </span>
            </li>
        </ul>
        <h2>{$jid}</h2>
    {/if}
{/if}
