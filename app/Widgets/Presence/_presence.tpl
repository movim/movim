<li role="menuitem" onclick="Presence_ajaxHttpMenu()">
    <span
        class="primary icon bubble status
        {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
    ">
        <a href="#" onclick="listIconClick(event)">
            <img alt="{$c->__('status.my_avatar')}" src="{$me->getPicture(\Movim\ImageSize::M)}">
        </a>
    </span>
    <div>
        <p class="line bold">
            {$me->truename}
        </p>
    </div>
</li>
