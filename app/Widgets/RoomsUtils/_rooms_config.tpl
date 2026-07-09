<section class="scroll">
    {if="$conference->isGroupChat() && !$conference->sfuPresence && $sfu"}
        <ul class="list flex middle card shadow">
            <li class="block color blue large">
                <i class="material-symbols main">video_call</i>
                <div>
                    <button class="button oppose color blue active" onclick="ChatActions_ajaxEnableWideConferenceCall('{$conference->conference}')">
                        <i class="material-symbols">mode_off_on</i>
                    </button>
                    <p>{$c->__('wide_conference_call.enable_title')}</p>
                    <p>{$c->__('wide_conference_call.enable_text')}</p>
                </div>
            </li>
        </ul>
    {/if}
    <form name="config">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </form>
</section>
<footer>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="RoomsUtils_ajaxSetConfig(MovimUtils.formToJson('config'), '{$room}'); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.save')}
    </a>
</footer>
