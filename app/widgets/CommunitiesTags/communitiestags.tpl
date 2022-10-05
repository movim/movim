<div id="communitiestags">
    {if="$tags->isNotEmpty()"}
        <ul class="list fill padded_top_bottom">
            {if="$community"}
                <li class="subheader">
                    <div>
                        <p>{$c->__('search.tags')}</p>
                    </div>
                </li>
            {/if}
            <li>
                <div>
                    <p class="{if="!$community"}line two center{/if} normal">
                        {loop="$tags"}
                            <a class="chip outline" href="{$c->route('tag', $value->name)}">
                                <i class="material-icons icon gray">tag</i>{$value->name}
                            </a>
                        {/loop}
                    </p>
                </div>
            </li>
        </ul>
    {/if}
</div>
