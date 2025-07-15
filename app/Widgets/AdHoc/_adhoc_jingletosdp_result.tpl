<section class="scroll">
    <form>
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">code</i>
                    </span>
                    <div>
                        <textarea dir="auto" name="sdp" id="sdp" placeholder="" style="min-height: 60rem;" data-autoheight="true">{$sdp}</textarea>
                        <label for="sdp">{$c->__('tools.sdp_title')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</section>
<footer>
    <button onclick="AdHoc_ajaxJingleToSDP()" class="button flat">
        {$c->__('button.previous')}
    </button>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</footer>
