<?php

namespace Moxl\Xec;

use Movim\Session;

class Handler
{
    public static function handle($child)
    {
        $id = (in_array($child->getName(), ['iq', 'presence', 'message']))
            ? (string)$child->attributes()->id
            : '';

        $sess = Session::start();

        /**
         * See Action/Presence/Muc
         */
        if ($child->getName() == 'presence' && isset($child->x)) {
            foreach ($child->x as $x) {
                if ($x->attributes()->xmlns == 'http://jabber.org/protocol/muc') {
                    if ($id === '') {
                        $id = $sess->get((string)$child->attributes()->from);
                    }

                    $sess->remove((string)$child->attributes()->from);
                }
            }
        }

        if ($id !== ''
        && $sess->get($id) !== false) {
            \Utils::info("Handler : Memory instance found for {$id}");

            $action = $sess->get($id);
            $sess->remove($id);

            $error = false;

            // Handle specific query error
            if ($child->query->error) {
                $error = $child->query->error;
            } elseif ($child->error) {
                $error = $child->error;
            }

            // XMPP returned an error
            if ($error) {
                $errors = $error->children();
                $errorid = Handler::formatError($errors->getName());

                $message = false;

                if ($error->text) {
                    $message = (string)$error->text;
                }

                \Utils::info('Handler : '.get_class($action).' '.$id.' - '.$errorid);

                // If the action has defined a special handler for this error
                if (method_exists($action, $errorid)) {
                    $action->method($errorid);
                    $action->$errorid($errorid, $message);
                }

                // We also call a global error handler
                if (method_exists($action, 'error')) {
                    \Utils::info('Handler : Global error - '.$id.' - '.$errorid);
                    $action->method('error');
                    $action->error($errorid, $message);
                }
            } elseif (method_exists($action, 'handle')) {
                // We launch the object handle
                $action->method('handle');
                $action->handle($child);
            }
        } else {
            \Utils::info("Handler : No memory instance found for {$id}");

            $handled = Handler::handleNode($child);

            foreach ($child->children() as $s1) {
                $handled = Handler::handleNode($s1, $child);
                foreach ($s1->children() as $s2) {
                    $handled = Handler::handleNode($s2, $child);
                }
            }
        }
    }

    public static function handleNode($s, $sparent = false)
    {
        $name = $s->getName();
        $ns = '';

        foreach ($s->attributes() as $key => $value) {
            if (($key == 'xmlns' && $ns == '')
            || 'xmlns:' === substr($key, 0, 6)) {
                $ns = $value;
            }
        }

        if ($s->items && $s->items->attributes()->node) {
            $node = (string)$s->items->attributes()->node;
            $hash = md5($name.$ns.$node);
            \Utils::info('Handler : Searching a payload for "'.$name . ':' . $ns . ' [' . $node . ']", "'.$hash.'"');
            Handler::searchPayload($hash, $s, $sparent);
        }

        $hash = md5($name.$ns);
        \Utils::info('Handler : Searching a payload for "'.$name . ':' . $ns . '", "'.$hash.'"');
        Handler::searchPayload($hash, $s, $sparent);
    }

    public static function searchPayload($hash, $s, $sparent = false): bool
    {
        $hashToClass = [
            '9a534a8b4d6324e23f4187123e406729' => 'Message',
            '78e731027d8fd50ed642340b7c9a63b3' => 'Message',// TLS

            '89d8bb4741fd3a62e8b20e0d52a85a36' => 'MucUser',

            'f9e18585fd0e0873c52e880c800f267a' => 'ReceiptAck',
            '004a75eb0a92fca2b868732b56863e66' => 'ReceiptRequest',
            '3ca6c24643a9389b91323ddd1aaa84d0' => 'Displayed',
            'e1f7d8bae32ee67aaf33a13fcbcf0c4a' => 'Retracted',

            '0977b7387b95f69007332a3e9b386f93' => 'MAMResult', // mam:0
            'fd60f5fdd5d2a06d1c4dd723032fb41a' => 'MAMResult', // mam:1
            '0e49eb65ba266051d2a2287660e22ab1' => 'MAMResult', // mam:2

            '887777451221e69bc638f4659ecfeffb' => 'Bookmark2', // bookmarks:0
            'c539a0a12da7913eef6b8b5292e31e68' => 'Bookmark2', // bookmarks:1

            '1040105fc01bfac8a5ab81324875e382' => 'Presence',
            '362b908ec9432a506f86bed0bae7bbb6' => 'Presence',// TLS
            'a0e8e987b067b6b0470606f4f90d5362' => 'Roster',

            'fa9d41e26f664d9056618a4afe213861' => 'Post',

            '53b95afd89dcb7199dfcca39a90592eb' => 'Confirm', // XEP-0070

            '9952d726429340d482ecac82c1496191' => 'BOB',

            '4c9681f0e9aca8a5b65f86b8b80d490f' => 'DiscoInfo',
            '2bf34d156903518b18e58b4786c25d3b' => 'DiscoItems',

            //'37ff18f136d5826c4426af5a23729e48' => 'Mood',
            //'6b38ed328fb77617c6e4a5ac9dda0ad2' => 'Tune',
            '0981a46bbfa88b3500c4bccda18ccb89' => 'Location',
            '9c8ed44d4528a66484b0fbd44b0a9070' => 'Nickname',

            // Should be handled by the PresenceBuffer, to be removed !
            //'d8ea912a151202700bb399c9e04d205f' => 'Caps',

            '40ed26a65a25ab8bf809dd998d541d95' => 'PingPong',

            //'cb52f989717d25441018703ea1bc9819' => 'Attention',

            '54c22c37d17c78ee657ea3d40547a970' => 'Version',

            '1cb493832467273efa384bbffa6dc35a' => 'Avatar',
            '0f59aa7fb0492a008df1b807e91dda3b' => 'AvatarMetadata',
            '36fe2745bdc72b1682be2c008d547e3d' => 'Vcard4',

            '0923dd6b12f46f658b4273104a129ec9' => 'JinglePropose',
            '829715591c7554bad3630dfd3353b4e7' => 'JingleAccept',
            '0c0238797befe918ac81efaa0200771b' => 'JingleProceed',
            '46ee3ca42af934e8a3b4d42062817aa8' => 'JingleRetract',
            '44d0c16e222fcdee6961c8939b647e15' => 'JingleReject',
            'd84d4b89d43e88a244197ccf499de8d8' => 'Jingle',

            '09ef1b34cf40fdd954f10d6e5075ee5c' => 'Carbons',
            '201fa54dd93e3403611830213f5f9fbc' => 'Carbons',//?

            'da6b60476aeab672ac0afe3ff27dc6a4' => 'OMEMODevices',

            '9a0cd265cabedadea095d8572d26167e' => 'StreamError',

            'b95746de5ddc3fa5fbf28906c017d9d8' => 'STARTTLS',

            'f728271d924a04b0355379b28c3183a1' => 'SASL',
            '5e291b72f7160dabd1aa28f90cbde769' => 'SASLChallenge',
            'abae1d63bb4295636badcce1bee02290' => 'SASLChallenge', // TLS
            'a5af6a9efd75060b5aca9b473f1ef756' => 'SASLSuccess',
            '53936dd4e1d64e1eeec6dfc95c431964' => 'SASLSuccess', // TLS
            'de175adc9063997df5b79817576ff659' => 'SASLFailure',
            '0bc0f510b2b6ac432e8605267ebdc812' => 'SessionBind',#
            '128477f50347d98ee1213d71f27e8886' => 'SessionBind',
        ];
        if (isset($hashToClass[$hash])) {
            $classname = '\\Moxl\\Xec\\Payload\\'.$hashToClass[$hash];

            $payload_class = new $classname;
            $payload_class->prepare($s, $sparent);
            $payload_class->handle($s, $sparent);

            return true;
        }

        \Utils::info('Handler : This event is not listed');
        return false;
    }

    /**
     * A simple function to format a error-string-text to a
     * camelTypeText
     */
    public static function formatError($string)
    {
        $words = explode('-', $string);
        $f = 'error';

        foreach ($words as $word) {
            $f .= ucfirst($word);
        }

        return $f;
    }
}
