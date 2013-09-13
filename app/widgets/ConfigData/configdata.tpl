<div class="tabelem padded" title="{$c->t('Data')}" id="configdata" >
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <fieldset>
            <legend>{$c->t('Cache')}</legend>
            <div class="clear"></div>
            <div class="element thin">
                <label for="name">{$c->t('Contact')} - {$stats.rosterlink}</label><br />
                <a 
                    type="button" 
                    class="button icon color red back"
                    onclick="'.$clearrosterlink.'">{$c->t('Clear')}</a>
            </div>
        </fieldset>

    
        <fieldset>
            <legend>{$c->t('Posts')} - {$stats.post}</legend>
            <div class="clear"></div>
            <ul class="stats">
                {loop="pstats"}
                    <li style="height: {$c->formatHeight($value.count)}%;">
                        <span class="date">
                            {$c->formatDate($value.month, $value.year)}
                        </span>
                        <span class="num">
                            {$value.count}
                        </span>
                    </li>
                {/loop}
            </ul>
            <div class="element thin">
                <a 
                    type="button" 
                    name="email" 
                    class="button icon color red back"
                    onclick="'.$clearpost.'">{$c->t('Clear')}</a>
            </div>
        </fieldset>
        
        <fieldset>
            <legend>{$c->t('Messages')} - {$stats.message}</legend>
            <div class="clear"></div>
            <ul class="stats">
                {loop="mstats"}
                    <li style="height: {$c->formatHeight($value.count)}%">
                        <span class="date">
                            {$c->formatDate($value.month, $value.year)}
                        </span>
                        <span class="num">
                            {$value.count}
                        </span>
                    </li>
                {/loop}
            </ul>
            <div class="element thin">
                <a 
                    type="button" 
                    name="email" 
                    class="button icon color red back"
                    onclick="'.$clearmessage.'">{$c->t('Clear')}</a>
            </div>
        </fieldset>
    </form>
</div>
