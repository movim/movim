<div id="initaccount_widget" class="paddedtopbottom">
    <script type="text/javascript">
    
    {if="isset($create_bookmark)"}
        movim_add_onload(function() {
            {$create_bookmark}
        });
    {/if}
    {if="isset($create_vcard4)"}
        movim_add_onload(function() {
            {$create_vcard4}
        });
    {/if}
    {if="isset($create_location)"}
        movim_add_onload(function() {
            {$create_location}
        });
    {/if}
    {if="isset($create_avatar)"}
        movim_add_onload(function() {
            {$create_avatar}
        });
    {/if}
    {if="isset($create_pubsubsubscription)"}
        movim_add_onload(function() {
            {$create_pubsubsubscription}
        });
    {/if}
    </script>

    {if="$creating"}
        <div class="spacetop"></div>
        <div class="message info">{$c->__('pubsub.creating')} - {$c->__('step.step', $creating)}/6</div>
    {/if}

    {if="isset($create_microblog)"}
        <script type="text/javascript">
            movim_add_onload(function() {
                {$create_microblog}
            });
        </script>
        <div class="message info">{$c->__('pubsub.creating_feed')} </div>
    {/if}
    
    {if="$no_pubsub"}
        <div class="spacetop"></div>
        <div class="message warning">{$c->__('pubsub.no_support')}</div>
    {/if}
</div>
