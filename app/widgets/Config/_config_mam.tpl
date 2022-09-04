<br />
<ul class="list fill">
    <li class="subheader"><div><p>{$c->__('config.mam')}</p></div></li>
</ul>

<form>
    <div>
        <div class="select">
            <select name="mam" id="mam" value="{$default}" onchange="Config_ajaxMAMSetConfig(this.value)">
                <option
                    {if="$default == 'never'"}selected="selected"{/if}
                    value="never">
                    {$c->__('config.mam_never')}
                </option>
                <option
                    {if="$default == 'roster'"}selected="selected"{/if}
                    value="roster">
                    {$c->__('page.contacts')}
                </option>
                <option
                    {if="$default == 'always'"}selected="selected"{/if}
                    value="always">
                    {$c->__('config.mam_always')}
                </option>
            </select>
        </div>
        <label for="mam">{$c->__('config.mam_text')}</label>
    </div>
</form>
