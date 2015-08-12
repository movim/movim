<div id="api" class="tabelem paddedtop" title="{$c->__("api.title")}">
    <ul>
        <li class="subheader">{$infos}</li>

    {if="isset($json)"}
        {if="$json->status == 200"}
            <li>
                <span class="icon bubble color green">
                    <i class="zmdi zmdi-cloud"></i>
                </span>
                {if="!$unregister_status"}
                    <div class="action">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.unregister')}
                        </a>
                    </div>
                {/if}
                <span>{$c->__('api.registered')}</span>
            </li>

            {if="$json->pod->activated"}
                <li>
                    <span class="icon bubble color green">
                        <i class="zmdi zmdi-cloud"></i>
                    </span>
                    <span>{$c->__('api.validated')}</span>
                </li>
            {else}
                <li>
                    <span class="icon bubble color gray">
                        <i class="zmdi zmdi-cloud-off"></i>
                    </span>
                    <span>{$c->__('api.wait')}</span>
                </li>
            {/if}

            {if="$unregister_status"}
                <li>
                    <div class="action">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.reset')}
                        </a>
                    </div>
                    <span>{$c->__('api.unregister')}</span>
                </li>
            {/if}
            <script type="text/javascript">AdminTest.enableAPI();</script>
        {else}
            <li>
                <span class="icon bubble color blue">
                    <i class="zmdi zmdi-cloud-off"></i>
                </span>
                <div class="action">
                    <a class="button oppose" onclick="{$register}">
                        {$c->__('button.register')}
                    </a>
                </div>
                <span>{$c->__('api.register')}</span>
            </li>
        {/if}
    {else}
        <li>
            <span class="icon bubble color gray">
                <i class="zmdi zmdi-cloud-off"></i>
            </span>
            {$c->__('api.error')}
        </li>
    {/if}
    </ul>
</div>
