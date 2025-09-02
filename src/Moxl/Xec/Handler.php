<?php

namespace Moxl\Xec;

use Movim\Session;

class Handler
{
    public static function handle(\SimpleXMLElement $child)
    {
        $id = (in_array($child->getName(), ['iq', 'presence', 'message']))
            ? (string)$child->attributes()->id
            : '';

        $session = Session::instance();

        if (
            $id !== ''
            && $session->get($id) !== null
        ) {
            logInfo("Handler : Memory instance found for {$id}");

            $action = $session->get($id);
            $session->delete($id);

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

                $message = null;

                if ($error->text) {
                    $message = (string)$error->text;
                }

                logInfo('Handler : ' . get_class($action) . ' ' . $id . ' - ' . $errorid);

                $propagate = true;

                // If the action has defined a special handler for this error
                if (method_exists($action, $errorid)) {
                    $action->method($errorid);
                    $propagate = $action->$errorid($errorid, $message);
                }

                // We also call a global error handler
                if (method_exists($action, 'error') && $propagate == true) {
                    logInfo('Handler : Global error - ' . $id . ' - ' . $errorid);
                    $action->method('error');
                    $action->error($errorid, $message);
                }
            } elseif (method_exists($action, 'handle')) {
                // We launch the object handle
                $action->method('handle');
                $action->handle($child);
            }
        } else {
            logInfo("Handler : No memory instance found for {$id}");

            $handledFirst = $handledSecond = $handledThird = false;

            $handledFirst = Handler::handleNode($child);

            foreach ($child->children() as $s1) {
                $handledSecond = Handler::handleNode($s1, $child);
                foreach ($s1->children() as $s2) {
                    $handledThird = Handler::handleNode($s2, $child);
                }
            }

            if ($child->getName() == 'iq' && !$handledFirst && !$handledSecond && !$handledThird) {
                Handler::searchPayload('iq_error', $child);
            }
        }
    }

    public static function handleNode($s, ?\SimpleXMLElement $sparent = null): bool
    {
        $name = $s->getName();
        $ns = '';

        foreach ($s->attributes() as $key => $value) {
            if (($key == 'xmlns' && $ns == '')
                || 'xmlns:' === substr($key, 0, 6)
            ) {
                $ns = $value;
            }
        }

        $matchPayloadWithNode = $matchPayload = false;

        if ($s->items && $s->items->attributes()->node) {
            $node = (string)$s->items->attributes()->node;
            $hash = md5($name . $ns . $node);
            logInfo('Handler : Searching a payload for "' . $name . ':' . $ns . ' [' . $node . ']", "' . $hash . '"');
            $matchPayloadWithNode = Handler::searchPayload($hash, $s, $sparent);
        }

        $hash = md5($name . $ns);
        logInfo('Handler : Searching a payload for "' . $name . ':' . $ns . '", "' . $hash . '"');
        $matchPayload = Handler::searchPayload($hash, $s, $sparent);

        if (!$matchPayloadWithNode && !$matchPayload) {
            return false;
        }

        return true;
    }

    public static function searchPayload($hash, $s, ?\SimpleXMLElement $sparent = null): bool
    {
        $hashToClass = [
            '9a534a8b4d6324e23f4187123e406729' => 'Message',
            '78e731027d8fd50ed642340b7c9a63b3' => 'Message', // TLS

            '89d8bb4741fd3a62e8b20e0d52a85a36' => 'MucUser',

            'f9e18585fd0e0873c52e880c800f267a' => 'ReceiptAck',
            '004a75eb0a92fca2b868732b56863e66' => 'ReceiptRequest',
            '3ca6c24643a9389b91323ddd1aaa84d0' => 'Displayed',
            '8bbeda5372a8170a51b2719fd0f1a533' => 'Retracted', // :1

            '0977b7387b95f69007332a3e9b386f93' => 'MAMResult', // mam:0
            'fd60f5fdd5d2a06d1c4dd723032fb41a' => 'MAMResult', // mam:1
            '0e49eb65ba266051d2a2287660e22ab1' => 'MAMResult', // mam:2

            '396a134b8d9f83b17bf1986966a609b5' => 'Blocked',
            '6b5eb74c2d7133303b2350dc2c91f28f' => 'Unblocked',

            '887777451221e69bc638f4659ecfeffb' => 'Bookmark2', // bookmarks:0
            'c539a0a12da7913eef6b8b5292e31e68' => 'Bookmark2', // bookmarks:1

            '1040105fc01bfac8a5ab81324875e382' => 'Presence',
            '362b908ec9432a506f86bed0bae7bbb6' => 'Presence', // TLS
            'a0e8e987b067b6b0470606f4f90d5362' => 'Roster',

            'fa9d41e26f664d9056618a4afe213861' => 'Post',

            '53b95afd89dcb7199dfcca39a90592eb' => 'Confirm', // XEP-0070

            '9952d726429340d482ecac82c1496191' => 'BOB',

            '4c9681f0e9aca8a5b65f86b8b80d490f' => 'DiscoInfo',
            '2bf34d156903518b18e58b4786c25d3b' => 'DiscoItems',

            '0981a46bbfa88b3500c4bccda18ccb89' => 'Location',
            '9c8ed44d4528a66484b0fbd44b0a9070' => 'Nickname',

            '40ed26a65a25ab8bf809dd998d541d95' => 'PingPong',

            '54c22c37d17c78ee657ea3d40547a970' => 'Version',

            '1cb493832467273efa384bbffa6dc35a' => 'AvatarData',
            '0f59aa7fb0492a008df1b807e91dda3b' => 'Avatar',

            '64d80ef76ceb442578e658fa39cde8c9' => 'Banner', // Movim specific for now

            '36fe2745bdc72b1682be2c008d547e3d' => 'Vcard4',

            '0923dd6b12f46f658b4273104a129ec9' => 'JinglePropose',
            '829715591c7554bad3630dfd3353b4e7' => 'JingleAccept',
            '0c0238797befe918ac81efaa0200771b' => 'JingleProceed',
            '46ee3ca42af934e8a3b4d42062817aa8' => 'JingleRetract',
            '44d0c16e222fcdee6961c8939b647e15' => 'JingleReject',
            '1622ee132ba08e9ccf42bfa3956931f9' => 'JingleFinish',
            'd84d4b89d43e88a244197ccf499de8d8' => 'Jingle',

            // TODO: Update the handlers to XEP-0482 when Dino is updated
            'f157a6eb56b1ad5d1e4b9ae9101464d7' => 'CallInvitePropose',
            'c633e665374419257db0c4f8e2624798' => 'CallInviteAccept',
            //'b7b53756a85b4d0c27c8797b3dcbaf6f' => 'CallInviteReject',
            'ddbf7b88f16d982ddfb129c18aaf94dc' => 'CallInviteRetract',
            'a6e8ce859ac26a7bc1c728a9c829ddcc' => 'CallInviteLeft',

            '09ef1b34cf40fdd954f10d6e5075ee5c' => 'Carbons', // sent
            '201fa54dd93e3403611830213f5f9fbc' => 'Carbons', // received

            'da6b60476aeab672ac0afe3ff27dc6a4' => 'OMEMODevices',

            'd9017180bc56364e7ba2bb1e493994b8' => 'StreamFeatures',
            '9a0cd265cabedadea095d8572d26167e' => 'StreamError',

            'b95746de5ddc3fa5fbf28906c017d9d8' => 'STARTTLS',

            '5e291b72f7160dabd1aa28f90cbde769' => 'SASLChallenge',
            'abae1d63bb4295636badcce1bee02290' => 'SASLChallenge', // TLS
            '2dae39d4435419c8d33cfee1822f914e' => 'SASL2Challenge',
            'a5af6a9efd75060b5aca9b473f1ef756' => 'SASLSuccess',
            '53936dd4e1d64e1eeec6dfc95c431964' => 'SASLSuccess', // TLS
            'ef58bff52ec9ffc2c1fb360ed77a2be5' => 'SASL2Success',
            'de175adc9063997df5b79817576ff659' => 'SASLFailure',
            '270c38e008e3d10e2ed5ed4fd615e5dd' => 'SASL2Failure',
            '0bc0f510b2b6ac432e8605267ebdc812' => 'SessionBind',
            '128477f50347d98ee1213d71f27e8886' => 'SessionBind',

            'iq_error' => 'IqError',
        ];

        if (isset($hashToClass[$hash])) {
            $classname = '\\Moxl\\Xec\\Payload\\' . $hashToClass[$hash];

            $payloadClass = new $classname;
            $payloadClass->prepare($s, $sparent);
            $payloadClass->handle($s, $sparent);

            return true;
        }

        logInfo('Handler : This event is not listed');
        return false;
    }

    /**
     * A simple function to format a error-string-text to a
     * camelTypeText
     */
    public static function formatError(string $string): string
    {
        $words = explode('-', $string);
        $f = 'error';

        foreach ($words as $word) {
            $f .= ucfirst($word);
        }

        return $f;
    }
}
