<div class="tabelem padded" title="{$c->__('title')}" id="configdata" >
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <fieldset>
            <legend><i class="fa fa-database"></i> {$c->__('title.contacts')} - {$stats.rosterlink}</legend>
            <div class="clear"></div>
            <br />
            <div class="element thin">
                <a 
                    type="button" 
                    class="button color red"
                    onclick="{$clearrosterlink}">
                    <i class="fa fa-trash-o"></i> {$c->__('button.clear')}
                </a>
            </div>
        </fieldset>

    
        <fieldset>
            <legend><i class="fa fa-pencil"></i> {$c->__('title.posts')} - {$stats.post}</legend>
            <div class="clear"></div>
            <ul class="stats">
                {loop="$pstats"}
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
                    class="button color red"
                    onclick="{$clearpost}">
                    <i class="fa fa-trash-o"></i> {$c->__('button.clear')}
                </a>
            </div>
        </fieldset>
        
        <fieldset>
            <legend><i class="fa fa-comment"></i> {$c->__('title.messages')} - {$stats.message}</legend>
            <div class="clear"></div>
            <ul class="stats">
                {loop="$mstats"}
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
                    class="button color red"
                    onclick="{$clearmessage}">
                    <i class="fa fa-trash-o"></i> {$c->__('button.clear')}
                </a>
            </div>
        </fieldset>
    </form>
</div>
