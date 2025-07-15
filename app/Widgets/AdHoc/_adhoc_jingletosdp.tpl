<section class="scroll">
    <form name="jingletosdp" onsubmit="return false;">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">code</i>
                    </span>
                    <div>
                        <textarea dir="auto" name="jingle" id="jingle" placeholder="<jingle xmlns='urn:xmpp:jingle:1'
action='session-initiate'
initiator='romeo@montague.lit/orchard
..." style="min-height: 60rem;" data-autoheight="true"></textarea>
                        <label for="jingle">{$c->__('tools.jingle_title')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>

    <button onclick="AdHoc_ajaxJingleToSDPSubmit(MovimUtils.formToJson('jingletosdp'));" class="button flat">
        {$c->__('button.next')}
    </button>
</footer>
