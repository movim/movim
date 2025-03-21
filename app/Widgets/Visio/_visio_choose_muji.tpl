<section id="visio_lobby">
    <ul class="list thick">
        {loop="$conference->mujiCalls"}
            <li>
                <span class="primary icon">
                    <i class="material-symbols icon">
                        {$value->icon}
                    </i>
                </span>
                <div>
                    <button class="button oppose color blue"
                            onclick="Visio_ajaxJoinMuji('{$value->id}', {if="$value->video"}true{else}false{/if});">
                        <i class="material-symbols">
                            {$value->icon}
                        </i>
                    </button>
                    <p>
                        {$c->__('visio.in_call')}
                        <span class="second">
                            {$value->participants->count()}
                            <i class="material-symbols">people</i>
                        </span>
                    </p>
                    <p>
                        {$value->created_at|prepareDate:true,true}
                        •
                        {$c->__('visio.by', $value->inviter->name)}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
</futton>