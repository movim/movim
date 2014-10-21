<div id="admindb" class="tabelem paddedtop" title="{$c->__('db.legend')}">
    <fieldset>
        <legend>{$c->__('db.legend')}</legend>
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
            <span>{$dbtype}</span>
        </div>
        <div class="element">
            <label for="dbUsername">{$c->__('db.username')}</label>
            <span>{$conf.username}</span>
        </div>
        <div class="element">
            <label for="dbPassword">{$c->__('db.password')}</label>
            <span>Password</span>
        </div>
        <div class="element">
            <label for="dbHost">{$c->__('db.host')}</label>
            <span>{$conf.host}</span>
        </div>
        <div class="element">
            <label for="dbPort">{$c->__('db.port')}</label>
            <span>{$conf.port}</span>
        </div>
        <div class="element">
            <label for="dbName">{$c->__('db.name')}</label>
            <span>{$conf.database}</span>
        </div>
    </fieldset>
</div>
