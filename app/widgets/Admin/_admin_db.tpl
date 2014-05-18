<fieldset>
    <legend>{$c->__('')}</legend>
    <div class="clear"></div>  
    
    {if="!$connected"}
        <div class="message error">
            {$c->__('db.connect_error')}
        </div>
        <div class="message error">
            {$errors}
        </div>    
    {else}
        <div class="message success">
            {$c->__('db.connect_success')}
        </div>
        {if="null !== $infos"} 
            <p>{$c->__('db.update')}</p>
            <div class="message warning">
                {loop="$infos"}
                    <p>{$value}</p>
                {/loop}
                
                <a class="button color green icon refresh" 
                    onclick="{$db_update}"
                    style="float: right;">{$c->__('button.update')}</a>
                <div class="clear"></div>
            </div>
        {else}
            <div class="message success">
                {$c->__('db.up_to_date')}
            </div>
        {/if}
    {/if}
    
    <div class="clear"></div>  
    <div class="element">
        <label for="logLevel">{$c->__('db.type')}</label>
        <div class="select">
            <select id="dbType" name="dbType">
                {loop="$supported_db"}
                    <option value="{$key}"
                        {if="$key == $conf.dbType"}
                            selected="selected"
                        {/if}>
                        {$value}
                    </option>
                {/loop}
            </select>
        </div>
    </div>
    <div class="element">
        <label for="dbUsername">{$c->__('db.username')}</label>
        <input type="text" name="dbUsername" id="dbUsername" value="{$conf.dbUsername}" />
    </div>
    <div class="element">
        <label for="dbPassword">{$c->__('db.password')}</label>
        <input type="password" name="dbPassword" id="dbPassword" value="{$conf.dbPassword}" />
    </div>
    <div class="element">
        <label for="dbHost">{$c->__('db.host')}</label>
        <input type="text" name="dbHost" id="dbHost" value="{$conf.dbHost}" />
    </div>
    <div class="element">
        <label for="dbPort">{$c->__('db.port')}</label>
        <input type="text" name="dbPort" id="dbPort" value="{$conf.dbPort}" />
        <div class="message info">
        PostgreSQL - 5432 | MySQL - 3306
        </div>
    </div>
    <div class="element">
        <label for="dbName">{$c->__('db.name')}</label>
        <input type="text" name="dbName" id="dbName" value="{$conf.dbName}" />
    </div>
    {$validatebutton}
</fieldset>
