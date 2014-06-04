<?php

namespace Moxl;

require 'Auth/SASL2.php';

class Auth {
    static function encryptPassword($data, $user, $pass) {
        foreach(array('realm', 'cnonce', 'digest-uri') as $key)
            if(!isset($data[$key]))
                $data[$key] = '';
        
        $pack = md5($user.':'.$data['realm'].':'.$pass);
        
        if(isset($data['authzid'])) 
            $a1 = pack('H32',$pack).sprintf(':%s:%s:%s',$data['nonce'],$data['cnonce'],$data['authzid']);
        else 
            $a1 = pack('H32',$pack).sprintf(':%s:%s',$data['nonce'],$data['cnonce']);
        $a2 = 'AUTHENTICATE:'.$data['digest-uri'];
        
        return md5(sprintf('%s:%s:%s:%s:%s:%s', md5($a1), $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], md5($a2)));
    }

    static function createChallengeDIGESTMD5($decoded)
    {
        $session = \Sessionx::start();
        
        $decoded = Utils::explodeData($decoded);

        if(!isset($decoded['digest-uri'])) $decoded['digest-uri'] = 'xmpp/'.$session->host;

        $decoded['cnonce'] = base64_encode(Utils::generateNonce());

        if(isset($decoded['qop'])
        && $decoded['qop'] != 'auth'
        && strpos($decoded['qop'],'auth') !== false
        ) { $decoded['qop'] = 'auth'; }

        $response = array('username'=>$session->user,
            'response' => self::encryptPassword(
                            array_merge(
                                $decoded,
                                array('nc'=>'00000001')),
                                $session->user,
                                $session->password),
            'charset' => 'utf-8',
            'nc' => '00000001',
            'qop' => 'auth'
        );

        foreach(array('nonce', 'digest-uri', 'realm', 'cnonce') as $key)
            if(isset($decoded[$key]))
                $response[$key] = $decoded[$key];

        $response = base64_encode(Utils::implodeData($response));

        return $response;
    }

    static function createChallengeCRAMMD5($decoded)
    {
        $session = \Sessionx::start();
        
        $key = $session->password;
        
        if (strlen($key) > 64) {
            $key = pack('H32', md5($key));
        }

        if (strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
        }

        $k_ipad = substr($key, 0, 64) ^ str_repeat(chr(0x36), 64);
        $k_opad = substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64);

        $inner  = pack('H32', md5($k_ipad . $decoded));
        $digest = md5($k_opad . $inner);
        
        $digest = base64_encode($session->user. ' ' . $digest);

        return $digest;
    }

    static function mechanismChoice($mec) {
        $mechanism = array(
                        'SCRAM-SHA-1',
                        'DIGEST-MD5',
                        'CRAM-MD5',
                        'PLAIN');
        
        $mecchoice = false;
        $i = 0;
        
        while($mecchoice == false && $i <= count($mechanism)) {
            if(in_array($mechanism[$i], $mec))
                $mecchoice = true;
            else $i++;
        }

        return $mechanism[$i];
    }

    static function mechanismPLAIN() {
        $session = \Sessionx::start();
        
        $response = base64_encode(chr(0).$session->user.chr(0).$session->password);

        $xml = API::boshWrapper(
                '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN" client-uses-full-bind-result="true">'.
                    $response.
                '</auth>', false);

        $r = new Request($xml);
        $xml = $r->fire();

        $xmle = new \SimpleXMLElement($xml['content']);

        if(!$xmle->success)
            return 'wrongaccount';
        else
            return 'OK';
    }

    static function mechanismDIGESTMD5() {
        $xml = API::boshWrapper(
                '<auth 
                    client-uses-full-bind-result="true"
                    xmlns="urn:ietf:params:xml:ns:xmpp-sasl" 
                    mechanism="DIGEST-MD5"/>', false);

        $r = new Request($xml);
        $xml = $r->fire();

        $xmle = new \SimpleXMLElement($xml['content']);
        if($xmle->failure)
            return 'errormechanism';

        $decoded = base64_decode((string)$xmle->challenge);

        if($decoded)
            $response = self::createChallengeDIGESTMD5($decoded);
        else
            return 'errorchallenge';

        Utils::log("/// CHALLENGE");

            $xml = API::boshWrapper(
                    '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">
                        '.$response.'
                    </response>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);
            if($xmle->failure)
                return 'wrongaccount';

        if($xmle->success)
            return 'OK';

        Utils::log("/// RESPONSE");

            $xml = API::boshWrapper(
                    '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl"/>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);

        if(!$xmle->success)
            return 'failauth';
        else
            return 'OK';
    }

    static function mechanismCRAMMD5() {
            $xml = API::boshWrapper(
                    '<auth 
                        client-uses-full-bind-result="true"
                        xmlns="urn:ietf:params:xml:ns:xmpp-sasl" 
                        mechanism="CRAM-MD5"/>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);
            if($xmle->failure)
                return 'errormechanism';

            $decoded = base64_decode((string)$xmle->challenge);

            if($decoded)
                $response = createChallengeCRAMMD5($decoded);
            else
                return 'errorchallenge';

        Utils::log("/// CHALLENGE");

            $xml = API::boshWrapper(
                    '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'.$response.'</response>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);

        if(!$xmle->success)
            return 'failauth';
        else
            return 'OK';
    }

    static function saltSHA1($str, $salt, $i)
    {
        $int1 = "\0\0\0\1";
        $ui = hash_hmac('sha1', $str, $salt . $int1);
        $result = $ui;
        for ($k = 1; $k < $i; $k++)
        {
            $ui =  hash_hmac('sha1', $str, $ui, true);
            $result = $result ^ $ui;
        }
        return $result;
    }


    static function mechanismSCRAMSHA1() {
            $f = new \Auth_SASL2;
            $fa = $f->factory('SCRAM-SHA1');

            $session = \Sessionx::start();

        Utils::log("/// INITIAL MESSAGE");

            $response = base64_encode($fa->getResponse($session->user, $session->password));

            $xml = API::boshWrapper(
                    '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="SCRAM-SHA-1">
                        '.$response.'
                    </auth>');

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);
            if($xmle->failure)
                return 'errormechanism';

            $challenge = base64_decode((string)$xmle->challenge);

        Utils::log("/// SECOND MESSAGE - PROOF");

            $response = base64_encode($fa->getResponse($session->user, $session->password, $challenge));
            
            $xml = API::boshWrapper(
                    '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">
                        '.$response.'
                    </response>', false);

            $r = new Request($xml);
            $xml = $r->fire();

            $xmle = new \SimpleXMLElement($xml['content']);

        if(!$xmle->success)
            return 'failauth';
        else
            return 'OK';
    }

    static function restartRequest() {
        $session = \Sessionx::start();
        
        $xml =
            '<body
                rid="'.$session->rid.'"
                sid="'.$session->sid.'"
                to="'.$session->host.'"
                xml:lang="en"
                xmpp:restart="true"
                xmlns="http://jabber.org/protocol/httpbind"
                xmlns:xmpp="urn:xmpp:xbosh"/>';

        $r = new Request($xml);
        $xml = $r->fire();

        return $xml;
    }

    static function ressourceRequest() {
        $session = \Sessionx::start();
        
        $xml = API::boshWrapper(
            '<iq type="set" id="'.$session->id.'">
                <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">
                    <resource>'.$session->ressource.'</resource>
                </bind>
            </iq>', false, true);

        $r = new Request($xml);
        $xml = $r->fire();

        $xmle = new \SimpleXMLElement($xml['content']);

        if($xmle->head || (string)$xmle->attributes()->type == 'terminate')
            return 'failauth';
        elseif($xmle->iq->bind->jid) {
            list($jid, $ressource) = explode('/', (string)$xmle->iq->bind->jid);
            if($ressource)
                $session->ressource = $ressource;
        }

        return 'OK';
    }

}
