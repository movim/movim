{if="empty($roster)"}
    <script type="text/javascript">setTimeout('{$refresh}', 1500);</script>
    <span class="nocontacts">
        {$c->t('No contacts ? You can add one using the + button bellow or going to the Explore page')}
        <br />
        <br />
        <a class="button color green icon users" href="{$c->route('explore')}">{$c->t('Explore')}</a>
    </span>
{else}
    {loop="roster"}
    <div id="group{$value->name}" class="{$value->shown}">
        <h1 onclick="{$value->toggle}">{$key}</h1>
        {$value->html}
    </div>
    {/loop}
{/if}
