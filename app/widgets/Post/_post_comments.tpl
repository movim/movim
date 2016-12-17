<ul class="list divided spaced middle">
    <li class="subheader center">
        <p>
            <span class="info">{$comments|count}</span> {$c->__('post.comments')}
        </p>
    </li>
    {$liked = false}

    {loop="$comments"}
        {if="$value->isMine(true) && $value->isLike()"}
            {$liked = true}
        {/if}

        {if="$value->title || $value->contentraw"}
        <li>
            {if="$value->isMine()"}
                <span class="control icon gray active" onclick="Post_ajaxDelete('{$value->origin}', '{$value->node}', '{$value->nodeid}')">
                    <i class="zmdi zmdi-delete"></i>
                </span>
            {/if}

            {if="$value->isLike()"}
                <span class="primary icon small red">
                    <i class="zmdi zmdi-favorite"></i>
                </span>
            {else}
                {$url = $value->getContact()->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble small">
                        <a href="{$c->route('contact', $value->getContact()->jid)}">
                            <img src="{$url}">
                        </a>
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->getContact()->jid|stringToColor} small">
                        <a href="{$c->route('contact', $value->getContact()->jid)}">
                            <i class="zmdi zmdi-account"></i>
                        </a>
                    </span>
                {/if}
            {/if}
            <p class="normal">
                <span class="info" title="{$value->published|strtotime|prepareDate}">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
                <a href="{$c->route('contact', $value->getContact()->jid)}">
                    {$value->getContact()->getTrueName()}
                </a>
            </p>
            {if="!$value->isLike()"}
                <p class="all">
                    {if="$value->contentraw"}
                        {$value->contentraw|addHFR}
                    {else}
                        {$value->title}
                    {/if}
                </p>
            {/if}
        </li>
        {/if}
    {/loop}

    <li>
        <p class="center">
            {if="!$liked"}
            <a class="button red flat" onclick="Post_ajaxLike('{$server}', '{$node}', '{$id}')">
                <i class="zmdi zmdi-favorite"></i> {$c->__('button.like')}
            </a>
            {/if}
            <a class="button flat gray" onclick="Post.comment()">
                <i class="zmdi zmdi-comment"></i> {$c->__('post.comment_add')}
            </a>
        </p>
    </li>

    <li class="hide" id="comment_add">
        <span class="primary icon gray">
            <i class="zmdi zmdi-comment"></i>
        </span>
        <span class="control icon gray active" onclick="Post_ajaxPublishComment(MovimUtils.formToJson('comment'),'{$server}', '{$node}', '{$id}')">
            <i class="zmdi zmdi-mail-send"></i>
        </span>
        <form name="comment">
            <div>
                <textarea
                    oninput="MovimUtils.textareaAutoheight(this);"
                    name="comment"
                    placeholder="{$c->__('field.type_here')}"
                ></textarea>
                <label for="comment">{$c->__('post.comment_add')}</label>
            </div>
        </form>
    </li>
</ul>
