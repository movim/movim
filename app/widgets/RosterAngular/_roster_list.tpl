{if="empty($roster)"}
    <script type="text/javascript">setTimeout('{$refresh}', 1500);</script>
    <span class="nocontacts">
        {$c->__('roster.no_contacts')}
        <br />
        <br />
        <a class="button color green icon users" href="{$c->route('explore')}">{$c->__('page.explore')}</a>
    </span>
{else}
    {loop="$roster"}
    <div id="group{$value->name}" class="{$value->shown}">
        <h1 onclick="{$value->toggle}">{$key}</h1>
        {$value->html}
    </div>
    {/loop}
{/if}
