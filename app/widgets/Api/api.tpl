<div id="api" class="tabelem paddedtop" title="{$c->__("api.title")}">
    <p>{$infos}</p>

    {if="isset($json)"}
        {if="$json->status == 200"}
            <div class="message success">
                {$c->__('api.registered')}
                {if="!$unregister_status"}
                    <a class="button color red oppose" onclick="{$unregister}">
                        <i class="fa fa-sign-out"></i> {$c->__('button.unregister')}
                    </a>
                {/if}
                <div class="clear"></div>
            </div>

            {if="$json->pod->activated"}
                <div class="message success">
                    {$c->__('api.validated')}
                </div>
            {else}
                <div class="message warning">{$c->__('api.wait')}</div>
            {/if}

            {if="$unregister_status"}
                <div class="message info">{$c->__('api.unregister')}
                    <a class="button color orange oppose" onclick="{$unregister}">
                        <i class="fa fa-sign-out"></i> {$c->__('button.reset')}
                    </a>
                    <div class="clear"></div>
                </div>
            {/if}
        {else}
            <div class="message info">
                {$c->__('api.register')}
                <a class="button color green oppose" onclick="{$register}">
                    <i class="fa fa-sign-in"></i> {$c->__('button.register')}
                </a>
                <div class="clear"></div>
            </div>
        {/if}
    {else}
        <div class="message error">{$c->__('api.error')}</div>
    {/if}
</div>
