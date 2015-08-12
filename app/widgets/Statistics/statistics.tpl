<div id="statistics" class="tabelem" title="{$c->__("statistics.title")}">
    <ul class="divided thick">
        <li class="subheader">
            {$c->__('statistics.sessions')} - {$sessions|count}
        </li>
        {loop="$sessions"}
            {$user = $c->getContact($value->username, $value->host)}
            <li class="condensed">
                {$url = $user->getPhoto('s')}
                {if="$url"}
                    <span class="icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="icon bubble color {$user->jid|stringToColor}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                <span>{$user->getTrueName()} - {$value->username}@{$value->host} - {$value->domain}</span>
                <p>
                    {if="isset($value->start)"}
                        {$c->getTime($value->start)}
                    {/if}
                </p>
            </li>
        {/loop}
    </ul>

    <h3 class="padded_top_bottom">{$c->__('statistics.subscriptions')}</h3>
    <div class="card">
        <img src="{$cache_path}monthly.png">
    </div>
    <div class="card">
        <img src="{$cache_path}monthly_cumulated.png">
    </div>
    <script type="text/javascript">
        MovimWebsocket.attach(function() {
            MovimWebsocket.connection.admin("{$hash}");
        });
    </script>
</div>
