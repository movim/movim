<div id="statistics" class="tabelem" title="{$c->__("statistics.title")}">
    <ul class="list divided middle flex active">
        <li class="subheader block large">
            <p>{$c->__('statistics.sessions')} <span class="second">{$sessions|count}</a></p>
        </li>
        {loop="$sessions"}
            {$user = $c->getContact($value->username, $value->host)}
            <li class="block" onclick="MovimUtils.redirect('{$c->route('contact', $value->jid)}')">
                {$url = $user->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$user->jid|stringToColor}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                <p>{$user->getTrueName()} <span class="second">{$value->username}@{$value->host}</a></p>
                <p>
                    {if="isset($value->start)"}
                        {$c->getTime($value->start)}
                    {/if}
                </p>
            </li>
        {/loop}
    </ul>

    <h3 class="padded_top_bottom">{$c->__('statistics.subscriptions')}</h3>

    <br />
    <canvas id="monthly" width="800" height="400"></canvas>
</div>
