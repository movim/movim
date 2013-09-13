<fieldset>
    <legend>{$c->t('Database Settings')}</legend>
    <div class="clear"></div>  
    
    {if="!$connected"}
        <div class="message error">
            {$c->t("Modl wasn't able to connect to the database")}
        </div>
        <div class="message error">
            {$errors}
        </div>    
    {else}
        <div class="message success">
            {$c->t('Movim is connected to the database')}
        </div>
        {if="$infos != null"} 
            <p>{$c->t('The database need to be updated')}</p>
            <div class="message warning">
                {loop="infos"}
                    <p>{$value}</p>
                {/loop}
                
                <a class="button color green icon refresh" 
                    onclick="{$db_update}"
                    style="float: right;">{$c->t('Update')}</a>
                <div class="clear"></div>
            </div>
        {else}
            <div class="message success">
                {$c->t('Movim database is up to date')}
            </div>
        {/if}
    {/if}
    
    <div class="clear"></div>  
    <div class="element">
        <label for="logLevel">{$c->t('Database Type')}</label>
        <div class="select">
            <select id="dbType" name="dbType">
                {loop="supported_db"}
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
        <label for="dbUsername">{$c->t('Username')}</label>
        <input type="text" name="dbUsername" id="dbUsername" value="{$conf.dbUsername}" />
    </div>
    <div class="element">
        <label for="dbPassword">{$c->t('Password')}</label>
        <input type="password" name="dbPassword" id="dbPassword" value="{$conf.dbPassword}" />
    </div>
    <div class="element">
        <label for="dbHost">{$c->t('Host')}</label>
        <input type="text" name="dbHost" id="dbHost" value="{$conf.dbHost}" />
    </div>
    <div class="element">
        <label for="dbPort">{$c->t('Port')}</label>
        <input type="text" name="dbPort" id="dbPort" value="{$conf.dbPort}" />
        <div class="message info">
        PostgreSQL - 5432 | MySQL - 3306
        </div>
    </div>
    <div class="element">
        <label for="dbName">{$c->t('Database Name')}</label>
        <input type="text" name="dbName" id="dbName" value="{$conf.dbName}" />
    </div>
</fieldset>
{$validatebutton}
