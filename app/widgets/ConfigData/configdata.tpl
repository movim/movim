<div class="tabelem" title="{$c->__('title')}" id="configdata" >
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <ul class="thick">
            <li>
                <div class="control">
                    <a 
                        class="button flat red"
                        onclick="{$clearrosterlink}">
                        {$c->__('button.clear')}
                    </a>
                </div>
                <span class="icon bubble color orange"><i class="md md-contacts"></i></span>
                <span>{$c->__('title.contacts')} - {$stats.rosterlink}</span>
            </li>
        </ul>

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
    </form>
</div>
