<ul class="divided spaced middle">
    <li class="subheader">
        {$c->__('post.comments')}
        <span class="info">{$comments|count}</span>
    </li>
    {loop="$comments"}
        <li class="condensed {if="$value->isMine()"}action{/if}">
            {if="$value->isMine()"}
                <div class="action" onclick="Post_ajaxDelete('{$value->origin}', '{$value->node}', '{$value->nodeid}')">
                    <i class="zmdi zmdi-delete"></i>
                </div>
            {/if}
            <a href="{$c->route('contact', $value->getContact()->jid)}">
                {$url = $value->getContact()->getPhoto('s')}
                {if="$url"}
                    <span class="icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="icon bubble color {$value->getContact()->jid|stringToColor}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
            </a>
            <span class="info">{$value->published|strtotime|prepareDate}</span>
            <span>
                <a href="{$c->route('contact', $value->getContact()->jid)}">
                    {$value->getContact()->getTrueName()}
                </a>
            </span>
            <p class="all">
                {$value->contentraw}
            </p>
        </li>
    {/loop}
    <li class="action">
        <div class="action" onclick="Post_ajaxPublishComment(movim_form_to_json('comment'),'{$server}', '{$node}', '{$id}')">
            <i class="zmdi zmdi-mail-send"></i>
        </div>
        <span class="icon gray">
            <i class="zmdi zmdi-comment"></i>
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
