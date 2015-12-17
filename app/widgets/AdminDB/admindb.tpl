<div id="admindb" class="tabelem" title="{$c->__('db.legend')}">
    <ul class="list flex large">
        <li class="subheader">
            <p>{$c->__('db.legend')}</p>
        </li>

        {if="!$connected"}
            <li class="block large">
                <span class="primary icon bubble color red">
                    <i class="zmdi zmdi-code-setting"></i>
                </span>
                <p>{$c->__('db.connect_error')}</p>
                <p>{$errors}</p>
            </li>
        {else}
            <li class="block large">
                <span class="primary icon bubble color green">
                    <i class="zmdi zmdi-code-setting"></i>
                </span>
                <p class="normal">{$c->__('db.connect_success')}</p>
            </li>
            {if="null !== $infos"}
                <li class="block large">
                    <span class="primary icon bubble color orange">
                        <i class="zmdi zmdi-refresh"></i>
                    </span>
                    <span class="control">
                        <a class="button" onclick="{$db_update}"> {$c->__('button.update')}</a>
                    </span>
                    <p>{$c->__('db.update')}</p>
                    {loop="$infos"}
                        <p>{$value}</p>
                    {/loop}
                </li>
            {else}
                <li class="block large">
                    <span class="primary icon bubble color green">
                        <i class="zmdi zmdi-refresh"></i>
                    </span>
                    <p class="normal">{$c->__('db.up_to_date')}</p>
                </li>
            {/if}
        {/if}
    </ul>

    <form class="flex padded_top_bottom">
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
