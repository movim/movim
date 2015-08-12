<div class="tabelem" title="{$c->__('title')}" id="configdata" >
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <ul class="thick">
            <li>
                <div class="control">
                    <a 
                        class="button"
                        onclick="{$clearrosterlink}">
                        {$c->__('button.clear')}
                    </a>
                </div>
                <span class="icon bubble color orange"><i class="zmdi zmdi-contacts"></i></span>
                <span>{$c->__('title.contacts')} - {$stats.rosterlink}</span>
            </li>

            <li>
                <div class="control">
                    <a 
                        type="button" 
                        name="email" 
                        class="button"
                        onclick="{$clearpost}">
                        {$c->__('button.clear')}
                    </a>
                </div>
                <span class="icon bubble color blue"><i class="zmdi zmdi-contacts"></i></span>
                <span>{$c->__('title.posts')} - {$stats.post}</span>
            </li>
            <li>
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
            </li>
            <li>
                <div class="control">
                    <a 
                        type="button" 
                        name="email" 
                        class="button"
                        onclick="{$clearmessage}">
                        {$c->__('button.clear')}
                    </a>
                </div>
                <span class="icon bubble color brown"><i class="zmdi zmdi-comments"></i></span>
                <span>{$c->__('title.messages')} - {$stats.message}</span>
            </li>

            <li>
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
            </li>
        </ul>
    </form>
</div>
