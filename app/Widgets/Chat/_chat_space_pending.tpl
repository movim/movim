{$info = $message->resolveSpacePendingInvitation()}
<i class="material-symbols icon gray">communities</i>
<span class="icon bubble tiny">
    <img src="{$contact->getPicture(\Movim\ImageSize::M)}">
</span>

{$c->__('spaceinfo.pending_request', $contact->truename)}
{if="$info"}
    <span class="icon bubble tiny">
        <img src="{$info->getPicture(placeholder: $info->name)}">
    </span>
    {$info->name}
{/if}
<br />
<a href="#" onclick="SpacesMenu_ajaxManageInvitation('{$message->jidfrom}', '{$message->body}', '{$message->subject}')">
    {$c->__('spaceinfo.pending_action')}
</a>
