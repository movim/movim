<form enctype="multipart/form-data" method="post" action="index.php" name="general">
    <br/>
    <h3>{$c->__('config.general')}</h3>
    <div class="block">
        <div class="select">
            <select name="language" id="language" value="{$conf.language}">
                <option value="en">English (default)</option>
                    {loop="$languages"}
                        {if="$key == $conf.language"}
                            <option
                                value="{$key}"
                                dir="auto"
                                selected="selected">
                                {$value}
                            </option>
                        {else}
                            <option
                                dir="auto"
                                value="{$key}">
                                {$value}
                            </option>
                        {/if}
                    {/loop}
            </select>
        </div>
        <label for="language">{$c->__('config.language')}</label>
    </div>

    <br />
    <h3>{$c->__('config.advanced')}</h3>

    <div class="block">
        <input name="cssurl" class="content" placeholder="http://myserver.com/style.css" value="{$conf.cssurl}" type="url">
        <label for="cssurl">{$c->__('cssurl.label')}</label>
    </div>

    <div class="clear padded"></div>
    <a
        onclick="{$submit}"
        class="button color oppose" >
        {$c->__('button.save')}
    </a>
    <div class="clear"></div>
</form>
