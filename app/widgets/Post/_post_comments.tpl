<ul class="divided middle">
    <li class="subheader">{$c->__('post.comments')}</li>
    {loop="$comments"}
        <li class="condensed">
            <span class="icon bubble">
                <img src="{$value->getContact()->getPhoto('xs', $value->aid)}"/>
            </span>
            <span class="info">{$value->published|strtotime|prepareDate}</span>
            <span>{$value->getContact()->getTrueName()}</span>
            <p>
                {$value->content}
            </p>
        </li>
    {/loop}
</ul>
