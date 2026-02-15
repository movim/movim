<section>
    <h3>{$c->__('spaceinfo.destroy_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('spaceinfo.destroy_text')}</h4>

    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">communities</i>
            </span>
            <div>
                <p>
                    {if="$subscription->info"}
                        {$subscription->info->name ?? $subscription->info->node}
                    {else}
                        {$subscription->node}
                    {/if}
                </p>
                <p>
                    <i class="material-symbols">people</i> {$subscription->spaceAffiliations()->count()} {$c->__('chatrooms.members')}<br />
                    <i class="material-symbols">tag</i> {$subscription->spaceRooms()->count()} {$c->__('chatrooms.rooms')}
                </p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="SpaceInfo_ajaxDestroy('{$server}', '{$node}'); Dialog_ajaxClear()">
        {$c->__('button.destroy')}
    </button>
</footer>
