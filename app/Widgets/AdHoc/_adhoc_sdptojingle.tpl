<section class="scroll">
    <form name="sdptojingle" onsubmit="return false;">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">short_text</i>
                    </span>
                    <div>
                        <textarea dir="auto" name="sdp" id="sdp" placeholder="v=0
o=jdoe 2890844526 2890842807 IN IP4 10.47.16.5
s=SDP Seminar
..." style="min-height: 60rem;" data-autoheight="true"></textarea>
                        <label for="sdp">{$c->__('tools.sdp_title')}</label>
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

    <button onclick="AdHoc_ajaxSDPToJingleSubmit(MovimUtils.formToJson('sdptojingle'));" class="button flat">
        {$c->__('button.next')}
    </button>
</footer>
