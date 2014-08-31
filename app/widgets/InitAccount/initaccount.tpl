<div id="initaccount_widget" class="paddedtopbottom">
    <script type="text/javascript">
    
    {if="isset($create_bookmark)"}
        setTimeout('{$create_bookmark}', 2000);
    {/if}
    {if="isset($create_vcard4)"}
        setTimeout('{$create_vcard4}', 2000);
    {/if}
    {if="isset($create_location)"}
        setTimeout('{$create_location}', 2000);
    {/if}
    {if="isset($create_avatar)"}
        setTimeout('{$create_avatar}', 2000);
    {/if}
    {if="isset($create_pubsubsubscription)"}
        setTimeout('{$create_pubsubsubscription}', 2000);
    {/if}
    </script>

    {if="$creating"}
        <div class="spacetop"></div>
        <div class="message info">{$c->__('pubsub.creating')} - {$c->__('step.step', $creating)}/6</div>
    {/if}

    {if="isset($create_microblog)"}
        <script type="text/javascript">
            setTimeout('{$create_microblog}', 2000);
        </script>
        <div class="message info">{$c->__('pubsub.creating_feed')} </div>
    {/if}
    
    {if="$no_pubsub"}
        <div class="spacetop"></div>
        <div class="message warning">{$c->__('pubsub.no_support')}</div>
    {/if}
</div>
