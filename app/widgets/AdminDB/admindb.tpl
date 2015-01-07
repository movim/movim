<div id="admindb" class="tabelem paddedtop" title="{$c->__('db.legend')}">
    <form>
        <ul>
            <li class="subheader">{$c->__('db.legend')}</li>

            {if="!$connected"}
                <li class="block large condensed">
                    <span class="icon bubble color red">
                        <i class="fa fa-plug"></i>
                    </span>
                    <span>{$c->__('db.connect_error')}</span>
                    <p>{$errors}</p>
                </li> 
            {else}
                <li class="block large">
                    <span class="icon bubble color green">
                        <i class="fa fa-plug"></i>
                    </span>
                    <span>{$c->__('db.connect_success')}</span>
                </li>
                {if="null !== $infos"}
                    <li class="block large condensed">
                        <span class="icon bubble color orange">
                            <i class="fa fa-refresh"></i>
                        </span>
                        <span>{$c->__('db.update')}</span>
                        <div class="control">
                            <a class="button oppose" 
                                onclick="{$db_update}"> {$c->__('button.update')}</a>
                        </div>
                        {loop="$infos"}
                            <p>{$value}</p>
                        {/loop}
                    </li>
                {else}
                    <li class="block large">
                        <span class="icon bubble color green">
                            <i class="fa fa-refresh"></i> 
                        </span>
                        <span>{$c->__('db.up_to_date')}</span>
                    </li>
                {/if}
            {/if}
        </ul> 
        
        <div class="clear"></div>  
        <div class="block">
            <input value="{$dbtype}" disabled/>
            <label for="logLevel">{$c->__('db.type')}</label>
        </div>
        <div class="block">
            <input value="{$conf.username}" disabled/>
            <label for="dbUsername">{$c->__('db.username')}</label>
        </div>
        <div class="block">
            <input value="{$c->hidePassword($conf.password)}" disabled/>
            <label for="dbPassword">{$c->__('db.password')}</label>
        </div>
        <div class="block">
            <input value="{$conf.host}" disabled/>
            <label for="dbHost">{$c->__('db.host')}</label>
        </div>
        <div class="block">
            <input value="{$conf.port}" disabled/>
            <label for="dbPort">{$c->__('db.port')}</label>
        </div>
        <div class="block">
            <input value="{$conf.database}" disabled/>
            <label for="dbName">{$c->__('db.name')}</label>
        </div>
    </form>
</div>
