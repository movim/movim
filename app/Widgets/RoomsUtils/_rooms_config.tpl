<section class="scroll">
    <ul class="list thick">
        <li>
            {if="$conference->presence->mucaffiliation == 'owner'"}
                <span class="control icon divided active"
                    title="{$c->__('button.destroy')}"
                    onclick="RoomsUtils_ajaxAskDestroy('{$conference->conference|echapJS}');">
                    <i class="material-symbols">delete</i>
                </span>
            {/if}
            <span class="primary icon bubble">
                <img src="{$conference->getPicture(\Movim\ImageSize::M)}">
            </span>
            <div>
                <p class="line">{if="isset($conference)"}{$conference->title}{else}{$id}{/if}</p>
                <p>{$c->__('chatrooms.configure')}</p>
            </div>
        </li>
        {if="$conference->presence->mucrole == 'moderator'"}
            <li class="active divided" onclick="RoomsUtils_ajaxGetAvatar('{$conference->conference|echapJS}')">
                <span class="primary icon gray">
                    <i class="material-symbols">image</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p>{$c->__('page.avatar')}</p>
                    <p>{$c->__('chatrooms.avatar')}</p>
                </div>
            </li>
        {/if}
    </ul>
    {if="$conference->isGroupChat() && !$conference->sfuPresence && $conference->hasRelatedSFUService()"}
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
    <a onclick="RoomsUtils_ajaxSetConfig(MovimUtils.formToJson('config'), '{$conference->conference}'); Dialog_ajaxClear();" class="button flat green color">
        {$c->__('button.save')}
    </a>
</footer>
