<i class="material-symbols icon red">call_end</i> {$c->__('chat.muji_retract')}{if="$diff"} • {if="$diff->h > 0"}{$c->__('chat.jingle_hours', $diff->h, $diff->i)}{elseif="$diff->i > 0"}{$c->__('chat.jingle_minutes', $diff->i, $diff->s)}{else}{$c->__('chat.jingle_seconds', $diff->s)}{/if}{/if}
