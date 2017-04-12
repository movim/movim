{if="$comments"}
    <ul class="list divided spaced middle">
        <li class="subheader">
            <p>
                <span class="info">{$comments|count}</span>
                {$c->__('post.comments')}
            </p>
        </li>
        {loop="$comments"}
            {if="$value->title || $value->contentraw"}
            <li>
                {if="$value->isLike()"}
                    <span class="primary icon small red">
                        <i class="zmdi zmdi-favorite"></i>
                    </span>
                {else}
                    {$url = $value->getContact()->getPhoto('s')}
                    {if="$url"}
                        <span class="primary icon small bubble">
                            <img src="{$url}">
                        </span>
                    {else}
                        <span class="primary icon small bubble color {$value->getContact()->jid|stringToColor}">
                            <i class="zmdi zmdi-account"></i>
                        </span>
                    {/if}
                {/if}
                <p class="normal line">
                    <span class="info" title="{$value->published|strtotime|prepareDate}">
                        {$value->published|strtotime|prepareDate:true,true}
                    </span>
                    {$value->getContact()->getTrueName()}
                </p>
                {if="!$value->isLike()"}
                    <p class="all">
                        {if="$value->title"}
                            {$value->title}
                        {else}
                            {$value->contentraw}
                        {/if}
                    </p>
                {/if}
            </li>
            {/if}
        {/loop}
    </ul><br />
{/if}
