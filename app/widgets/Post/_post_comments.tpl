<ul class="list divided spaced middle">
    <li class="subheader">
        <p><span class="info">{$comments|count}</span> {$c->__('post.comments')}</p>
    </li>
    {loop="$comments"}
        <li>
            {if="$value->isMine()"}
                <span class="control icon gray active" onclick="Post_ajaxDelete('{$value->origin}', '{$value->node}', '{$value->nodeid}')">
                    <i class="zmdi zmdi-delete"></i>
                </span>
            {/if}

            {$url = $value->getContact()->getPhoto('s')}
            {if="$url"}
                <span class="primary icon bubble">
                    <a href="{$c->route('contact', $value->getContact()->jid)}">
                        <img src="{$url}">
                    </a>
                </span>
            {else}
                <span class="primary icon bubble color {$value->getContact()->jid|stringToColor}">
                    <a href="{$c->route('contact', $value->getContact()->jid)}">
                        <i class="zmdi zmdi-account"></i>
                    </a>
                </span>
            {/if}
            <p>
                <span class="info">{$value->published|strtotime|prepareDate}</span>
                <a href="{$c->route('contact', $value->getContact()->jid)}">
                    {$value->getContact()->getTrueName()}
                </a>
            </p>
            <p class="all">
                {if="$value->contentraw"}
                    {$value->contentraw|prepareString}
                {else}
                    {$value->title}
                {/if}
            </p>
        </li>
    {/loop}
    <li>
        <span class="primary icon gray">
            <i class="zmdi zmdi-comment"></i>
        </span>
        <span class="control icon gray active" onclick="Post_ajaxPublishComment(movim_form_to_json('comment'),'{$server}', '{$node}', '{$id}')">
            <i class="zmdi zmdi-mail-send"></i>
        </span>
        <form name="comment">
            <div>
                <textarea
                    oninput="movim_textarea_autoheight(this);"
                    name="comment"
                    placeholder="{$c->__('field.type_here')}"
                ></textarea>
                <label for="comment">{$c->__('post.comment_add')}</label>
            </div>
        </form>
    </li>
</ul>
