<div class="tabelem padded" title="{$c->t('Avatar')}" id="avatar" >
    <div class="protect orange"></div>
    {if="$getavatar != null"}
        <script type="text/javascript">setTimeout('{$getavatar}', 1000);</script>
    {/if}
    <div id="avatar_form">
        {$form}
    </div>
</div>
