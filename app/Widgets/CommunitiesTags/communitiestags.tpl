<div id="communitiestags">
    {if="$tags->isNotEmpty()"}
        <ul class="list">
            {if="$community"}
                <li class="subheader">
                    <div>
                        <p>{$c->__('search.tags')}</p>
                    </div>
                </li>
            {/if}
            <li>
                <div>
                    <p class="{if="!$community"}line two center{/if} all">
                        {loop="$tags"}
                            <a class="chip outline active" href="#" onclick="MovimUtils.reload('{$c->route('tag', $value->name)}')">
                                <i class="material-symbols icon gray">tag</i>{$value->name}
                            </a>
                        {/loop}
                    </p>
                </div>
            </li>
        </ul>
    {/if}
</div>
