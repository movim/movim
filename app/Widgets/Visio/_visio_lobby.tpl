<section id="visio_lobby">
    <ul class="list thick">
        <li>
            <span class="primary icon bubble">
                <img src="{$contact->getPicture(\Movim\ImageSize::O)}">
            </span>
            <div>
                <p class="normal">
                    {if="$calling"}
                        <i class="material-symbols icon blue">call</i>
                        {$c->__('visiolobby.calling', $contact->truename)}
                    {else}
                        <i class="material-symbols icon blue">phone_callback</i>
                        {$c->__('visiolobby.called', $contact->truename)}
                    {/if}
                </p>
                <p>{$c->__('visiolobby.setup')}</p>
            </div>
        </li>
    </ul>
    <div class="placeholder">
        <i class="material-symbols">camera_video</i>
        <h4>{$c->__('visiolobby.devices_disco')}</h4>
    </div>
    <form>
        <div>
            <ul class="list">
                <li id="default_microphone" class="muted">
                    <span class="primary icon gray">
                        <i class="material-symbols enabled">settings_voice</i>
                        <i class="material-symbols disabled">mic_off</i>
                    </span>
                    <div>
                        <div class="select">
                            <select name="default_microphone"></select>
                        </div>
                        <label for="default_microphone">{$c->__('visiolobby.microphone_label')}</label>
                        <span class="supporting">
                            {$c->__('visiolobby.no_mic_sound')}<br />
                            {$c->__('visiolobby.no_mic_sound2')}
                        </span>
                    </div>
                </li>
                <li>
                    <div id="mic_preview">
                        <div class="level">
                            <span class="disabled color green"></span>
                            <span class="disabled color green"></span>
                            <span class="disabled color green"></span>
                            <span class="disabled color green"></span>
                            <span class="disabled color green"></span>
                            <span class="disabled color yellow"></span>
                            <span class="disabled color yellow"></span>
                            <span class="disabled color yellow"></span>
                            <span class="disabled color yellow"></span>
                            <span class="disabled color red"></span>
                        </div>
                    </div>
                </li>
                {if="$withvideo"}
                    <li>
                        <span class="primary icon gray">
                            <i class="material-symbols">video_camera_back</i>
                        </span>
                        <div>
                            <div class="select">
                                <select name="default_camera"></select>
                            </div>
                            <label for="default_camera">{$c->__('visiolobby.camera_label')}</label>
                        </div>
                    </li>
                    <li>
                        <span class="primary"></span>
                        <div><video id="camera_preview" muted></video></div>
                    </li>
                {/if}
            </ul>
        </div>
    </form>
</section>
<div>
    {if="$calling"}
        <button onclick="Visio.goodbye(); Dialog_ajaxClear()" class="button flat">
            {$c->__('button.cancel')}
        </button>
        <button id="lobby_start" onclick="Visio.prepare('{$jid}', null, {if="$withvideo"}true{else}false{/if}); Dialog_ajaxClear();" class="button color green disabled">
            {if="$withvideo"}
                <i class="material-symbols">videocam</i>
            {else}
                <i class="material-symbols">call</i>
            {/if}
            {$c->__('button.call')}
        </button>
    {else}
        <button onclick="Visio_ajaxReject('{$jid|echapJS}', '{$id}'); Visio.goodbye(); Dialog_ajaxClear()" class="button color red">
            <i class="material-symbols">call_end</i>
            {$c->__('button.refuse')}
        </button>
        <button id="lobby_start" onclick="Visio.prepare('{$jid}', '{$id}', {if="$withvideo"}true{else}false{/if}); Dialog_ajaxClear();" class="button color green disabled">
            {if="$withvideo"}
                <i class="material-symbols">videocam</i>
            {else}
                <i class="material-symbols">call</i>
            {/if}
            {$c->__('button.reply')}
        </button>
    {/if}
</div>
