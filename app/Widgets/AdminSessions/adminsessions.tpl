<div id="adminsessions" class="tabelem" title="{$c->__("adminsessions.title")}" data-mobileicon="group">
    <ul class="list middle flex active card shadow">
        <li class="subheader">
            <div>
                <p>{$c->__('adminsessions.text')} <span class="second">{$sessions|count}</span></p>
            </div>
        </li>
        {loop="$sessions"}
            {$user = $c->getContact($value->user)}
            <li class="block" onclick="MovimUtils.redirect('{$c->route('contact', $user->id)}')">
                {if="$value->user->admin"}
                    <span class="control yellow icon">
                        <i class="material-symbols fill">star</i>
                    </span>
                {/if}
                <span class="primary icon bubble status {if="$value->presence"}{$value->presence->presencekey}{/if}">
                    <img src="{$user->getPicture()}">
                </span>
                <div>
                    <p class="line" title="{$user->id}">
                        {$user->truename} <span class="second">{$user->id}</span>
                    </p>
                    <p class="line">
                        {$value->created_at|prepareDate}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
</div>
