<div id="api" class="tabelem paddedtop" title="{$c->__("api.title")}">
    <ul class="list">
        <li class="subheader">
            <p>{$infos}</p>
        </li>

    {if="isset($json)"}
        {if="$json->status == 200"}
            <li>
                <span class="primary icon bubble color green">
                    <i class="zmdi zmdi-cloud"></i>
                </span>
                {if="!$unregister_status"}
                    <span class="control">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.unregister')}
                        </a>
                    </span>
                {/if}
                <p class="normal">{$c->__('api.registered')}</p>
            </li>

            {if="$json->pod->activated"}
                <li>
                    <span class="primary icon bubble color green">
                        <i class="zmdi zmdi-cloud"></i>
                    </span>
                    <p class="normal">{$c->__('api.validated')}</p>
                </li>
            {else}
                <li>
                    <span class="primary icon bubble color gray">
                        <i class="zmdi zmdi-cloud-off"></i>
                    </span>
                    <p class="normal">{$c->__('api.wait')}</p>
                </li>
            {/if}

            {if="$unregister_status"}
                <li>
                    <span class="control">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.reset')}
                        </a>
                    </span>
                    <p class="normal">{$c->__('api.unregister')}</p>
                </li>
            {/if}
            <script type="text/javascript">AdminTest.enableAPI();</script>
        {else}
            <li>
                <span class="primary icon bubble color blue">
                    <i class="zmdi zmdi-cloud-off"></i>
                </span>
                <span class="control">
                    <a class="button oppose" onclick="{$register}">
                        {$c->__('button.register')}
                    </a>
                </span>
                <p class="normal">{$c->__('api.register')}</p>
            </li>
        {/if}
    {else}
        <li>
            <span class="primary icon bubble color gray">
                <i class="zmdi zmdi-cloud-off"></i>
            </span>
            <p class="normal">{$c->__('api.error')}</p>
        </li>
    {/if}
    </ul>
</div>
