<div id="statistics" class="tabelem padded" title="{$c->t("Statistics")}">
    <ul class="list">
        <li class="title">
            <a class="action">{$c->t('Since')}</a>
            {$c->t('Sessions')} - {$sessions|count}
        </li>
        {loop="$sessions"}
            <li>
                <a class="action">{$c->getTime($value->rid)}</a>
                <a>{$value->username}@{$value->host}</a>
            </li>
        {/loop}
    </ul>
</div>
