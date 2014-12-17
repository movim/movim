<span id="back" class="on_mobile icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>
<span class="on_desktop icon" onclick="MovimTpl.hidePanel()"><i class="md md-textsms"></i></span>
{if="$post != null"}
    <h2>{$post->title}</h2>
{else}
    <h2>Empty</h2>
{/if}
