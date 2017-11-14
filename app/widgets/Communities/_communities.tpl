<ul class="list column third active card">
    {loop="$communities"}
        {$last = $c->getLastPublic($value->server, $value->node)}
        <li
            class="block
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->occupants > 0 || $value->num > 0"}condensed{/if}
                "
            onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
            title="{$value->server} - {$value->node}"
        >
            <ul class="list middle">
                <li>
                    {if="$value->subscription == 'subscribed'"}
                        <span class="control icon gray">
                            <i class="zmdi zmdi-bookmark"></i>
                        </span>
                    {/if}

                    {if="$value->logo"}
                        <span class="primary icon bubble">
                            <img src="{$value->getLogo(50)}">
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->node|stringToColor}">
                            {$value->node|firstLetterCapitalize}
                        </span>
                    {/if}
                    <p class="line normal">
                        {if="$value->name"}
                            {$value->name}
                        {else}
                            {$value->node}
                        {/if}
                        <span class="second">
                            {if="$value->description"}
                                {$value->description|strip_tags}
                            {/if}
                        </span>
                    </p>
                    <p>
                        {$value->server}
                        {if="$value->occupants > 0"}
                            <span title="{$c->__('communitydata.sub', $value->occupants)}">
                                - {$value->occupants} <i class="zmdi zmdi-accounts"></i>
                            </span>
                        {/if}
                    </p>
                </li>
                {if="$last"}
                    <li>
                        <p class="line" title="{$last->title}">{$last->title}</p>
                        <p dir="auto" class="all">
                            {if="$last->picture"}
                                <img class="preview" src="{$last->picture}" alt=""/>
                            {/if}
                            {$last->getSummary()}
                        </p>
                        <p>
                            {$count = $last->countLikes()}
                            {if="$count > 0"}
                                {$count} <i class="zmdi zmdi-favorite-outline"></i>
                            {/if}

                            {$count = $last->countComments()}
                            {if="$count > 0"}
                                {$count} <i class="zmdi zmdi-comment-outline"></i>
                            {/if}

                            <span class="info">
                                {$value->published|strtotime|prepareDate:true,true}
                            </span>
                        </p>
                    </li>
                {/if}
            </ul>
        </li>
    {/loop}
</ul>

