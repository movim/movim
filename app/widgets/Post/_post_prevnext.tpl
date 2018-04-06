{if="$post->next || $post->previous"}
    <ul class="list card flex active">
        {if="$post->previous"}
            <li class="block"
                onclick="MovimUtils.redirect('{$c->route('post', [$post->previous->server, $post->previous->node, $post->previous->nodeid])}')">
                <span class="primary icon gray">
                    <i class="zmdi zmdi-arrow-left"></i>
                </span>
                <p class="line" {if="isset($post->previous->title)"}title="{$post->previous->title}"{/if}>
                {if="isset($post->previous->title)"}
                    {$post->previous->title}
                {else}
                    {$post->previous->node}
                {/if}
                </p>
                <p class="line">{$post->previous->getSummary()}</p>
                <p>
                    {$likes = $post->previous->countLikes()}
                    {if="$likes > 0"}
                        {$likes} <i class="zmdi zmdi-favorite-outline"></i>
                    {/if}
                    {$count = $post->previous->countComments()}
                    {if="$count > 0"}
                        {$count} <i class="zmdi zmdi-comment-outline"></i>
                    {/if}
                    <span class="info">
                        {$post->previous->published|strtotime|prepareDate}
                    </span>
                </p>
            </li>
        {/if}
        {if="$post->next"}
            <li class="block"
                onclick="MovimUtils.redirect('{$c->route('post', [$post->next->server, $post->next->node, $post->next->nodeid])}')">
                <span class="control icon gray">
                    <i class="zmdi zmdi-arrow-right"></i>
                </span>
                <p class="line" {if="isset($post->next->title)"}title="{$post->next->title}"{/if}>
                {if="isset($post->next->title)"}
                    {$post->next->title}
                {else}
                    {$post->next->node}
                {/if}
                </p>
                <p class="line">{$post->next->getSummary()}</p>
                <p>
                    {$likes = $post->next->countLikes()}
                    {if="$likes > 0"}
                        {$likes} <i class="zmdi zmdi-favorite-outline"></i>
                    {/if}
                    {$count = $post->next->countComments()}
                    {if="$count > 0"}
                        {$count} <i class="zmdi zmdi-comment-outline"></i>
                    {/if}
                    <span class="info">
                        {$post->next->published|strtotime|prepareDate}
                    </span>
                </p>
            </li>
        {/if}
    </ul>
{/if}
