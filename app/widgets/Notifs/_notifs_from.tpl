<ul>
    <li>
        <h2><i class="fa fa-plus-square"></i> {$c->__('notifs.title')}</h2>
    </li>
    {loop="$invitations"}
        <li>
            <img src="{$value->getPhoto('xs')}"/> {$value->getTrueName()}
        </li>
    {/loop}
</ul>

<!--<ul>
    {loop="$contacts"}
        <li>
            <img src="{$value->getPhoto('xs')}"/> {$value->getTrueName()}
        </li>
    {/loop}
</ul>
-->
