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
                    <p class="{if="!$community"}line{/if} normal">
                        {if="!$community"}
                            <a class="button flat disabled gray">
                                <i class="material-icons">whatshot</i>
                            </a>
                        {/if}
                        {loop="$tags"}
                            <a class="button flat narrow" href="{$c->route('tag', $value->name)}">
                                <i class="material-icons">tag</i>{$value->name}
                            </a>
                        {/loop}
                    </p>
                </div>
            </li>
        </ul>
    {/if}
</div>
