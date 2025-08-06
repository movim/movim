<section id="chat_search">
    {autoescape="off"}
        {$c->prepareSearchPlaceholder()}
    {/autoescape}
</section>
<ul class="list">
    <li class="search">
        <form name="search" onsubmit="return false;">
            <div>
                <input name="keyword" autocomplete="off"
                    placeholder="{$c->__('button.search')}" oninput="console.log(this.value); ChatActions_ajaxSearchMessages('{$jid|echapJS}', this.value, {if="$muc"}true{else}false{/if});" type=" text">
            </div>
        </form>
    </li>
</ul>
