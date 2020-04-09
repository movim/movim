<div id="api" class="tabelem paddedtop" title="{$c->__("api.title")}">
    <ul class="list">
        <li class="subheader">
            <div>
                <p>{autoescape="off"}{$infos}{/autoescape}</p>
            </div>
        </li>

    {if="isset($json)"}
        {if="$json->status == 200"}
            <li>
                <span class="primary icon bubble color green">
                    <i class="material-icons">cloud</i>
                </span>
                {if="!$unregister_status"}
                    <span class="control">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.unregister')}
                        </a>
                    </span>
                {/if}
                <div>
                    <p class="normal">{$c->__('api.registered')}</p>
                </div>
            </li>

            {if="$json->pod->activated"}
                <li>
                    <span class="primary icon bubble color green">
                        <i class="material-icons">check</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('api.validated')}</p>
                    </div>
                </li>
            {else}
                <li>
                    <span class="primary icon bubble color gray">
                        <i class="material-icons">cloud_off</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('api.wait')}</p>
                    </div>
                </li>
            {/if}

            {if="$unregister_status"}
                <li>
                    <span class="control">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.reset')}
                        </a>
                    </span>
                    <div>
                        <p class="normal">{$c->__('api.unregister')}</p>
                    </div>
                </li>
            {/if}
            <script type="text/javascript">AdminTest.enableAPI();</script>
        {else}
            <li>
                <span class="primary icon bubble color blue">
                    <i class="material-icons">cloud_off</i>
                </span>
                <span class="control">
                    <a class="button oppose" onclick="{$register}">
                        {$c->__('button.register')}
                    </a>
                </span>
                <div>
                    <p class="normal">{$c->__('api.register')}</p>
                </div>
            </li>
        {/if}
    {else}
        <li>
            <span class="primary icon bubble color gray">
                <i class="material-icons">cloud_off</i>
            </span>
            <div>
                <p class="normal">{$c->__('api.error')}</p>
            </div>
        </li>
    {/if}
    </ul>
</div>
