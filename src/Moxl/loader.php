<?php

$base = __DIR__.'/';

define('XMPP_LIB_NAME', 'moxl');

require_once($base.'Auth.php');
require_once($base.'API.php');
require_once($base.'Request.php');
require_once($base.'Utils.php');

require_once($base.'Stanza/Message.php');
require_once($base.'Stanza/Muc.php');
require_once($base.'Stanza/Presence.php');
require_once($base.'Stanza/Roster.php');
require_once($base.'Stanza/Vcard.php');
require_once($base.'Stanza/Ack.php');

require_once($base.'Stanza/Carbons.php');

require_once($base.'Stanza/Avatar.php');

require_once($base.'Stanza/Bookmark.php');

require_once($base.'Stanza/Microblog.php');

require_once($base.'Stanza/Group.php');

require_once($base.'Stanza/Pubsub.php');
require_once($base.'Stanza/PubsubAtom.php');

require_once($base.'Stanza/PubsubSubscription.php');

require_once($base.'Stanza/Notification.php');

require_once($base.'Stanza/Storage.php');

require_once($base.'Stanza/Disco.php');

require_once($base.'Stanza/Location.php');

require_once($base.'Stanza/Version.php');

require_once($base.'Stanza/Vcard4.php');

require_once($base.'Stanza/Jingle.php');

// XEC loader

require_once($base.'Xec/Action.php');
require_once($base.'Xec/Payload.php');

// To handle error generated par Moxl requests
require_once($base.'Xec/Payload/RequestError.php');

require_once($base.'Xec/Action/Pubsub/Errors.php');

require_once($base.'Xec/Action/Disco/Request.php');

require_once($base.'Xec/Action/Bookmark/Get.php');
require_once($base.'Xec/Action/Bookmark/Set.php');

require_once($base.'Xec/Action/Roster/GetList.php');
require_once($base.'Xec/Action/Roster/AddItem.php');
require_once($base.'Xec/Action/Roster/UpdateItem.php');
require_once($base.'Xec/Action/Roster/RemoveItem.php');

require_once($base.'Xec/Action/Presence/Away.php');
require_once($base.'Xec/Action/Presence/Chat.php');
require_once($base.'Xec/Action/Presence/DND.php');
require_once($base.'Xec/Action/Presence/Subscribe.php');
require_once($base.'Xec/Action/Presence/Subscribed.php');
require_once($base.'Xec/Action/Presence/Unavaiable.php');
require_once($base.'Xec/Action/Presence/Unsubscribe.php');
require_once($base.'Xec/Action/Presence/Unsubscribed.php');
require_once($base.'Xec/Action/Presence/XA.php');
require_once($base.'Xec/Action/Presence/Muc.php');

require_once($base.'Xec/Action/Vcard/Get.php');
require_once($base.'Xec/Action/Vcard/Set.php');

require_once($base.'Xec/Action/Avatar/Set.php');
require_once($base.'Xec/Action/Avatar/Get.php');

require_once($base.'Xec/Action/Vcard4/Set.php');
require_once($base.'Xec/Action/Vcard4/Get.php');

require_once($base.'Xec/Action/Message/Publish.php');
require_once($base.'Xec/Action/Message/Composing.php');
require_once($base.'Xec/Action/Message/Paused.php');

require_once($base.'Xec/Action/Microblog/CreateNode.php');
require_once($base.'Xec/Action/Microblog/CommentsGet.php');
require_once($base.'Xec/Action/Microblog/CommentPublish.php');
require_once($base.'Xec/Action/Microblog/CommentCreateNode.php');

require_once($base.'Xec/Action/Notification/Get.php');
require_once($base.'Xec/Action/Notification/ItemDelete.php');

require_once($base.'Xec/Action/Storage/Get.php');
require_once($base.'Xec/Action/Storage/Set.php');

require_once($base.'Xec/Action/Location/Publish.php');

require_once($base.'Xec/Action/Group/Create.php');
require_once($base.'Xec/Action/Group/Delete.php');

require_once($base.'Xec/Action/PubsubSubscription/ListAdd.php');
require_once($base.'Xec/Action/PubsubSubscription/ListGet.php');
require_once($base.'Xec/Action/PubsubSubscription/ListGetFriends.php');
require_once($base.'Xec/Action/PubsubSubscription/ListRemove.php');

require_once($base.'Xec/Action/Version/Send.php');

require_once($base.'Xec/Action/Ack/Send.php');

// Pubsub Actions

require_once($base.'Xec/Action/Pubsub/PostPublish.php');
require_once($base.'Xec/Action/Pubsub/PostDelete.php');
require_once($base.'Xec/Action/Pubsub/GetItems.php');
require_once($base.'Xec/Action/Pubsub/DiscoItems.php');
require_once($base.'Xec/Action/Pubsub/GetConfig.php');
require_once($base.'Xec/Action/Pubsub/SetConfig.php');
require_once($base.'Xec/Action/Pubsub/GetAffiliations.php');
require_once($base.'Xec/Action/Pubsub/SetAffiliations.php');
require_once($base.'Xec/Action/Pubsub/GetSubscriptions.php');
require_once($base.'Xec/Action/Pubsub/SetSubscriptions.php');
require_once($base.'Xec/Action/Pubsub/Subscribe.php');
require_once($base.'Xec/Action/Pubsub/Unsubscribe.php');
require_once($base.'Xec/Action/Pubsub/GetMetadata.php');

// Jingle Actions
require_once($base.'Xec/Action/Jingle/SessionInitiate.php');
require_once($base.'Xec/Action/Jingle/SessionTerminate.php');

require_once($base.'Xec/Handler.array.php');
require_once($base.'Xec/Handler.php');
