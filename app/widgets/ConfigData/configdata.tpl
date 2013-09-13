<div class="tabelem padded" title="{$c->t('Data')}" id="configdata" >
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <fieldset>
            <legend>{$c->t('Cache')}</legend>
            <div class="clear"></div>
            <div class="element thin">
                <label for="name">{$c->t('Post')} - {$stats.post}</label><br />
                <a 
                    type="button" 
                    name="email" 
                    class="button icon color red back"
                    onclick="'.$clearpost.'">{$c->t('Clear')}</a>
            </div>
            <div class="element thin">
                <label for="name">{$c->t('Message')} - {$stats.message}</label><br />
                <a 
                    type="button" 
                    name="email" 
                    class="button icon color red back"
                    onclick="'.$clearmessage.'">{$c->t('Clear')}</a>
            </div>
            <div class="element thin">
                <label for="name">{$c->t('Contact')} - {$stats.rosterlink}</label><br />
                <a 
                    type="button" 
                    class="button icon color red back"
                    onclick="'.$clearrosterlink.'">{$c->t('Clear')}</a>
            </div>
        </fieldset>
    </form>
    
    <h2>{$c->t('Posts')}</h2>
    <div class="stats">
        <ul>
            {loop="pstats"}
                <li style="height: {$c->formatHeight($value.count)}%;">
                    <span>
                        {$c->formatDate($value.month, $value.year)} - {$value.count}
                    </span>
                </li>
            {/loop}
        </ul>
    </div>
    
    <h2>{$c->t('Messages')}</h2>
    <div class="stats">
        <ul>
            {loop="mstats"}
                <li style="height: {$c->formatHeight($value.count)}%">
                    <span>
                        {$c->formatDate($value.month, $value.year)} - {$value.count}
                    </span>
                </li>
            {/loop}
        </ul>
    </div>
</div>
