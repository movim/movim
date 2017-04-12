{if="$next || $previous"}
    <ul class="list card flex active">
        {if="$previous"}
            <li class="block"
                onclick="MovimUtils.redirect('{$c->route('post', [$previous->origin, $previous->node, $previous->nodeid])}')">
                <span class="primary icon gray">
                    <i class="zmdi zmdi-arrow-left"></i>
                </span>
                <p class="line" {if="isset($previous->title)"}title="{$previous->title}"{/if}>
                {if="isset($previous->title)"}
                    {$previous->title}
                {else}
                    {$previous->node}
                {/if}
                </p>
                <p class="line">{$previous->getSummary()}</p>
                <p>
                    {$likes = $previous->countLikes()}
                    {if="$likes > 0"}
                        {$likes} <i class="zmdi zmdi-favorite-outline"></i>
                    {/if}
                    {$count = $previous->countComments()}
                    {if="$count > 0"}
                        {$count} <i class="zmdi zmdi-comment-outline"></i>
                    {/if}
                    <span class="info">
                        {$previous->published|strtotime|prepareDate}
                    </span>
                </p>
            </li>
        {/if}
        {if="$next"}
            <li class="block"
                onclick="MovimUtils.redirect('{$c->route('post', [$next->origin, $next->node, $next->nodeid])}')">
                <span class="control icon gray">
                    <i class="zmdi zmdi-arrow-right"></i>
                </span>
                <p class="line" {if="isset($next->title)"}title="{$next->title}"{/if}>
                {if="isset($next->title)"}
                    {$next->title}
                {else}
                    {$next->node}
                {/if}
                </p>
                <p class="line">{$next->getSummary()}</p>
                <p>
                    {$likes = $next->countLikes()}
                    {if="$likes > 0"}
                        {$likes} <i class="zmdi zmdi-favorite-outline"></i>
                    {/if}
                    {$count = $next->countComments()}
                    {if="$count > 0"}
                        {$count} <i class="zmdi zmdi-comment-outline"></i>
                    {/if}
                    <span class="info">
                        {$next->published|strtotime|prepareDate}
                    </span>
                </p>
            </li>
        {/if}
    </ul>
{/if}
