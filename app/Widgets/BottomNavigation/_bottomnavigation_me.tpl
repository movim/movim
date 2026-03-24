<span
    onclick="Presence_ajaxHttpMenu()"
    class="primary icon bubble status
    {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
">
    <a href="#" onclick="listIconClick(this)">
        <img src="{$me->getPicture(\Movim\ImageSize::M)}">
    </a>
</span>