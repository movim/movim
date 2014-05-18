<div id="contactsummary">
    {$c->prepareContactSummary($contact)}
    {if="$refresh"}
        <script type="text/javascript">
            setTimeout("{$refresh}", 1000);
        </script>
    {/if}
</div>
