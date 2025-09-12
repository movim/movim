

<section class="scroll">
    <ul class="list thick">
        <li class="block large">
            <span class="primary icon">
                <i class="material-symbols">search</i>
            </span>
            <form name="communitiesservers_discover_server">
                <div>
                    <input placeholder="pubsub.server.com" name="server">
                    <label>{$c->__('communities.search_server')}</label>
                </div>
            </form>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button
        type="button"
        onclick="CommunitiesServers_ajaxDisco(MovimUtils.formToJson('communitiesservers_discover_server'))"
        class="button flat"
        >
        {$c->__('button.search')}
    </button>
</footer>
