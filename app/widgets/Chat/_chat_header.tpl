<span id="back" class="on_mobile icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>
<span class="on_desktop icon" onclick="MovimTpl.hidePanel()"><i class="md md-person"></i></span>

<ul class="active">
    <li onclick="{$close}">
        <span class="icon">
            <i class="md md-close"></i>
        </span>
    </li>
</ul>
{if="$contact != null"}
    <h2>{$contact->getTrueName()}</h2>
{else}
    <h2>{$jid}</h2>
{/if}
