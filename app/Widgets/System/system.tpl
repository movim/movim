var BASE_HOST = '{$base_host}';
var BASE_URI = '{$base_uri}';
var ERROR_URI = '{$error_uri}';
var SW_URI = '{$sw_uri}';
var SMALL_PICTURE_LIMIT = {$small_picture_limit};
var NOTIFICATION_CHAT = {if="$user && $user->notificationchat === true"}true{else}false{/if};
var NOTIFICATION_CALL = {if="$user && $user->notificationcall === true"}true{else}false{/if};
var OMEMO_ENABLED = {if="$user && $user->hasOMEMO()"}true{else}false{/if};
var USER_JID = {if="$user && isset($user->id)"}'{$user->id}'{else}false{/if};
var VAPID_PUBLIC_KEY = '{$vapid_public_key}';

var favoriteEmojis = {autoescape="off"}{$favorite_emojis|json_encode}{/autoescape};