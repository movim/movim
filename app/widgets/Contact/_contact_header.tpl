<span id="back" class="on_mobile icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>
<span class="on_desktop icon" onclick="MovimTpl.hidePanel()"><i class="md md-person"></i></span>
{if="$contactr != null"}
    <ul>
        <li>
            <span class="icon">
                <i class="md md-star"></i>
            </span>
        </li>
    </ul>
    <h2>{$contactr->getTrueName()}</h2>
{else}
    {if="$contact != null"}
        <ul>
            <li>
                <span class="icon">
                    <i class="md md-person-add"></i>
                </span>
            </li>
        </ul>
        <h2>{$contact->getTrueName()}</h2>
    {else}
        <ul>
            <li>
                <span class="icon">
                    <i class="md md-person-add"></i>
                </span>
            </li>
        </ul>
        <h2>{$jid}</h2>
    {/if}
{/if}
