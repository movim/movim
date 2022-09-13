<div id="adminsessions" class="tabelem padded_top_bottom" title="{$c->__("adminsessions.title")}" data-mobileicon="group">
    <ul class="list fill middle flex active">
        <li class="subheader block large">
            <div>
                <p>{$c->__('adminsessions.text')} <span class="second">{$sessions|count}</span></p>
            </div>
        </li>
        {loop="$sessions"}
            {$user = $c->getContact($value->user)}
            <li class="block" onclick="MovimUtils.redirect('{$c->route('contact', $user->id)}')">
                {if="$value->user->admin"}
                    <span class="control yellow icon">
                        <i class="material-icons">star</i>
                    </span>
                {/if}
                <span class="control gray icon">
                    <i class="material-icons">chevron_right</i>
                </span>
                {$url = $user->getPhoto()}
                {if="$url"}
                    <span class="primary icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$user->id|stringToColor}">
                        <i class="material-icons">person</i>
                    </span>
                {/if}
                <div>
                    <p class="line" title="{$user->id}">
                        {$user->truename} <span class="second">{$user->id}</span>
                    </p>
                    <p>
                        {$value->created_at|strtotime|prepareDate}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
</div>
