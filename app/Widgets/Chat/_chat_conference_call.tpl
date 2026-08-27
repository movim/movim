<div class="placeholder show">
    <i class="material-symbols">adaptive_audio_mic</i>

    <h1>{$conference->title}</h1>
    <h4>{$c->__('chatrooms.conference_call_text')}</h4>
    <br />


    {if="!$conference->sfuPresence && $canedit && $sfu"}
        <ul class="list middle card shadow">
            <li class="block color blue">
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

        <br />
        <hr />
        <br />

    {/if}

    <p>
        {if="$conference->sfuPresence"}
            <button class="button color {if="$conference->sfuPresence->SFUUsersCount > 0"}blue{else}green{/if} {if="$c->currentCall()?->isStarted()"}disabled{/if}" onclick="Visio_ajaxGetSFULobby('{$conference->sfuPresence->mucjid|echapJS}', '{$conference->conference}', true, true);">
                <i class="material-symbols">video_call</i>
                {$c->__('button.join')}
            </button>
        {elseif="$conference->mujiPresences->isEmpty()"}
            <button class="button color green {if="$c->currentCall()?->isStarted()"}disabled{/if}" onclick="Visio_ajaxGetMujiLobby('{$conference->conference}', true, false);">
                <i class="material-symbols">call</i>
                {$c->__('button.create')}
            </button>
        {else}
            <button class="button color green {if="$c->currentCall()?->isStarted()"}disabled{/if}"
                    onclick="Visio_ajaxJoinMuji('{$conference->conference}');">
                <i class="material-symbols">call</i>
                {$c->__('button.join')}
            </button>
        {/if}
    </p>
</div>
