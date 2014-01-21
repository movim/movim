{loop="roster"}
<div id="group{$value->name}" class="{$value->shown}">
    <h1 onclick="{$value->toggle}">{$key}</h1>
    {$value->html}
</div>
{/loop}
