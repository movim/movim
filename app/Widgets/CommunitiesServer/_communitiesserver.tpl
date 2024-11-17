<header>
    <ul class="list middle thick">
        <li>
            <span class="primary icon active" onclick="history.back()">
                <i class="material-symbols">arrow_back</i>
            </span>
            {if="is_array($nodes) && count($nodes) > 0"}
                <span class="control icon gray">
                    {$nodes|count}
                </span>
            {/if}
            <div>
                <p>
                    {if="isset($item->name)"}
                        {$item->name}
                    {else}
                        {$c->__('page.communities')}
                    {/if}
                </p>
                <p class="line">{$server}</p>
            </div>
        </li>
    </ul>
</header>
{if="$nodes->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-symbols">group_work</i>
            <h1>{$c->__('error.oops')}</h1>
            <h4>{$c->__('communitiesserver.empty_server')}</h4>
        </li>
    </ul>
{else}
    <ul class="list middle card shadow active flex">
    {loop="$nodes"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
    </ul>
{/if}
<button onclick="CommunitiesServer_ajaxTestAdd('{$server}')" class="button action color"
    title="{$c->__('communitiesserver.add', $server)}">
    <i class="material-symbols">add</i>
</button>
