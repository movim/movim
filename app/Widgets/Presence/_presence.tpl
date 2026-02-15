<li>
    <span
        onclick="Presence_ajaxHttpMenu()"
        class="primary icon bubble status
        {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
    ">
        <img src="{$me->getPicture(\Movim\ImageSize::M)}">
    </span>
    <div>
        <p class="line bold">
            {$me->truename}
        </p>
    </div>
</li>
