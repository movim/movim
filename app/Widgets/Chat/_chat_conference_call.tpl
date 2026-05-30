<div class="placeholder show">
    <i class="material-symbols">adaptive_audio_mic</i>

    <h1>{$conference->title}</h1>
    <h4>{$c->__('chatrooms.conference_call_text')}</h4>
    <br />
    <p>
        {if="$conference->mujiPresences->isEmpty()"}
            <button class="button color green {if="$incall"}disabled{/if}" onclick="Visio_ajaxGetMujiLobby('{$conference->conference}', true, false);">
                <i class="material-symbols">call</i>
                {$c->__('button.create')}
            </button>
        {else}
            <button class="button color green {if="$incall"}disabled{/if}"
                    onclick="Visio_ajaxJoinMuji('{$conference->conference}');">
                <i class="material-symbols">call</i>
                {$c->__('button.join')}
            </button>
        {/if}
    </p>
</div>
