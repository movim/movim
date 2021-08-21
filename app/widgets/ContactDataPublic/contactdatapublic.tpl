<ul id="contact_public_data" class="contact_data list card">
    <br />
    {if="$public"}
        {autoescape="off"}
            {$c->prepareCard($contact)}
        {/autoescape}

        {autoescape="off"}
            {$c->prepareSubscriptions($jid)}
        {/autoescape}
    {else}
        <div class="block">
            <ul class="list thick">
                <li>
                    <span class="primary icon gray">
                        <i class="material-icons">
                            highlight_off
                        </i>
                    </span>
                    <div>
                        <p class="normal">
                            {$c->__('contactdatapublic.no_data')}
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    {/if}
</ul>
