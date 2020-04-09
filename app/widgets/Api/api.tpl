<div id="api" class="tabelem paddedtop" title="{$c->__("api.title")}">
    <ul class="list">
        <li class="subheader">
            <content>
                <p>{autoescape="off"}{$infos}{/autoescape}</p>
            </content>
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
                <content>
                    <p class="normal">{$c->__('api.registered')}</p>
                </content>
            </li>

            {if="$json->pod->activated"}
                <li>
                    <span class="primary icon bubble color green">
                        <i class="material-icons">check</i>
                    </span>
                    <content>
                        <p class="normal">{$c->__('api.validated')}</p>
                    </content>
                </li>
            {else}
                <li>
                    <span class="primary icon bubble color gray">
                        <i class="material-icons">cloud_off</i>
                    </span>
                    <content>
                        <p class="normal">{$c->__('api.wait')}</p>
                    </content>
                </li>
            {/if}

            {if="$unregister_status"}
                <li>
                    <span class="control">
                        <a class="button oppose" onclick="{$unregister}">
                            {$c->__('button.reset')}
                        </a>
                    </span>
                    <content>
                        <p class="normal">{$c->__('api.unregister')}</p>
                    </content>
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
                <content>
                    <p class="normal">{$c->__('api.register')}</p>
                </content>
            </li>
        {/if}
    {else}
        <li>
            <span class="primary icon bubble color gray">
                <i class="material-icons">cloud_off</i>
            </span>
            <content>
                <p class="normal">{$c->__('api.error')}</p>
            </content>
        </li>
    {/if}
    </ul>
</div>
