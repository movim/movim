{if="$tags->isNotEmpty()"}
    <ul class="list">
        <li>
            <div>
                <p class="line normal">
                    <a class="button flat disabled gray">
                        <i class="material-icons">whatshot</i>
                    </a>
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
