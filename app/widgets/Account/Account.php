<?php

/**
 * @package Widgets
 *
 * @file Account.php
 * This file is part of MOVIM.
 *
 * @brief The account creation widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 25 November 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */
 
class Account extends WidgetBase {
    
    function WidgetLoad()
    {
        $this->addcss('account.css');
        $this->addjs('account.js');
    }
    
    function ajaxDiscoverServer($ndd) {
        if($ndd['ndd'] == '') {
            RPC::call('movim_reload', Route::urlize('account', 'datamissing'));
            RPC::commit();
            exit;
        }
        
        
        try {
            $dns = dns_get_record('_xmpp-client._tcp.'.$ndd['ndd']);

            if(isset($dns[0]['target']) && $dns[0]['target'] != null) {
                $domain = $dns[0]['target'];
            } else {
                $domain = $ndd['ndd'];
            }

            $f = fsockopen($domain, 5222, $errno, $errstr, 10);
  
            if(!$f ) {
                RPC::call('movim_reload', Route::urlize('account', 'xmppconnect'));
                RPC::commit();
                exit;
            }
            
            $stream = simplexml_load_string('<?xml version="1.0"?><stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0"><iq type="get" id="reg1"><query xmlns="jabber:iq:register"/></iq></stream:stream>');
            $stream->addAttribute('to', $ndd['ndd']);
            if (false === fwrite($f, $stream->asXML())) {
                 \system\Logs\Logger::log('fail write to stream');
                throw new \Exception('fail write to stream');
            }
            
            unset($stream);

            $response = stream_get_contents($f);
            \system\Logs\Logger::log('response');
            \system\Logs\Logger::log($response);
            if(!$response) {
                    RPC::call('movim_reload', Route::urlize('account', 'xmppcomm'));
                    RPC::commit();
                     exit;
            }

            $response = simplexml_load_string($response);

            $id = (string)$response->attributes()->id;

            $elements = (array)$response->iq->query;
            
            // We close properly our first register request
            $stream = simplexml_load_string('<?xml version="1.0"?><stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0"><iq type="set" id="unreg1"><query xmlns="jabber:iq:register"><remove/></query></iq></stream:stream>');
            $stream->addAttribute('to', $ndd['ndd']);
            fwrite($f, $stream->asXML());
            
            fclose($f); unset($f);
            
            if(!empty($elements)) {
                $html .= '
                    <form name="data">
                        <fieldset>
                                <legend>'.t('Step 2 - Fill in your informations').'</legend><br /><br /><br />';

                if($response->iq->query->instructions && $response->iq->query->x) {
                    $instr = '
                        <div class="element simple large">
                            <label>'.(string)$response->iq->query->instructions.'</label>';
                    if($response->iq->query->x->url)
                        $instr .= '
                            <a href="'.(string)$response->iq->query->x->url.'" target="_blank">'.
                                (string)$response->iq->query->x->url.'
                            </a>';
                            
                    $instr .= '
                        </div>';
                        
                }

                $form = new XMPPtoForm();
                if(!empty($response->iq->query->x)){
                    $formh .= $form->getHTML($response->iq->query->x->asXML());
                } else{/*no <x> element in the XML*/    
                    $formh .= $form->getHTML($response->iq->query->asXML());
                }

                if($formh == '')
                    $html .= $instr;
                else
                    $html .= $formh;
                
                $html .= '
                        <input
                            type="hidden"
                            value="'.$domain.'"
                            name="ndd"
                            id="ndd"
                        />
                    ';
                    
                $html .= '
                        <input
                            type="hidden"
                            value="'.$ndd['ndd'].'"
                            name="to"
                            id="to"
                        />
                    ';
                    
                $html .= '
                        <input
                            type="hidden"
                            value="'.$id.'"
                            name="id"
                            id="id"
                        />
                    ';
                                    
                if(isset($elements['data'])) {
                    $html .= '<img src="data:image/jpg;base64,'.$elements['data'].'"/>';
                }
                
                $submit = $this->genCallAjax('ajaxSubmitData', "movim_form_to_json('data')");
                
                $html .= '
                        <a
                            class="button color green icon yes" 
                            style="float: right;"
                            onclick="'.$submit.'"
                        >
                            '.t('Validate').'
                        </a>';
                
                $html .= '
                        </fieldset>
                    </form>';
            
                RPC::call('movim_fill', 'fillform', $html);
                RPC::commit();
                
            } else {
                $html = '
                    <div class="message warning">
                        '.t('No account creation form founded on the server').'
                    </div>';
                
                RPC::call('movim_fill', 'fillform', $html);
                RPC::commit();
            }
            
        } catch(Exception $e) {
            header(sprintf('HTTP/1.1 %d %s', $e->getCode(), $e->getMessage()));
            header('Content-Type: text/plain; charset=utf-8');
            \system\Logs\Logger::log($e->getMessage());
            //echo $e->getMessage(),"\n";
        }
    }
    
    function ajaxSubmitData($datas) {
        define(XMPP_HOST, $datas->to->value);
        define(XMPP_CONN, $datas->ndd->value);
        
        unset($datas->to);
        unset($datas->ndd);
        
        define(XMPP_PORT, 5222);

        try {         
            // We try to connect to the XMPP Server
            $f = fsockopen(XMPP_CONN, XMPP_PORT, $errno, $errstr, 10);

            if(!$f) {
                RPC::call('movim_reload', Route::urlize('account', 'xmppconnect'));
                RPC::commit();
                 exit;
            }

            // We create the XML Stanza
            $stream = simplexml_load_string('<?xml version="1.0"?><stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0"><iq id="'.$datas->id->value.'" type="set"><query xmlns="jabber:iq:register"><x xmlns="jabber:x:data" type="form"></x></query></iq></stream:stream>');
            
            unset($datas->id);

            $stream->addAttribute('to', XMPP_HOST);

            $xmpp = new FormtoXMPP();
            $stream = $xmpp->getXMPP($stream->asXML(), $datas);

            fwrite($f, $stream->asXML());

            unset($stream);

            $response = stream_get_contents($f);

            if(!$response) {
                    RPC::call('movim_reload', Route::urlize('account', 'xmppcomm'));
                    RPC::commit();
                     exit;
            }

            fclose($f); unset($f);

            $response = simplexml_load_string($response);

            if(!$response) throw new Exception('The XMPP server sent an invalid response', 500);

            if($stream_error = $response->xpath('/stream:stream/stream:error')) {
                list($stream_error) = $stream_error;
                list($cond) = $stream_error->children();

                throw new Exception($stream_error->text ? $stream_error->text : $cond->getName(), 500);
            }

            $iq = $response->iq;

            if($iq->error) {
                list($cond) = $iq->error->children();
                if($cond->getName() == 'conflict') {
                    RPC::call('movim_reload', Route::urlize('account', 'userconflict'));
                    RPC::commit();
                     exit;
                } else if($cond->getName() == 'not-acceptable') {
                    RPC::call('movim_reload', Route::urlize('account', 'notacceptable'));
                    RPC::commit();
                     exit;
                }
                throw new Exception($iq->error->text ? $iq->error->text : $cond->getName(), 400);
            }

            if($iq = $response->iq and $iq->attributes()->type == 'result') {
                RPC::call('movim_reload', Route::urlize('login', 'acccreated'));
                RPC::commit();
                exit;
            } else {
                    RPC::call('movim_reload', Route::urlize('account', 'unknown'));
                    RPC::commit();
                     exit;
            }
        } catch(Exception $e) {
            header(sprintf('HTTP/1.1 %d %s', $e->getCode(), $e->getMessage()));
            header('Content-Type: text/plain; charset=utf-8');
            \system\Logs\Logger::log($e->getCode().' '.$e->getMessage().' file:'.$e->getFile().' - l.'.$e->getLine());
		echo $e->getMessage(),"\n";
        }
    }
    
    function printServerList() {        
        $file = dirname(__FILE__).DIRECTORY_SEPARATOR.'server-vcards.xml';
        
        $html = '';
        
        if(file_exists($file)) {
            $vcards = simplexml_load_file($file);
            
            $html .= '
                <div class="clear"></div>
                <table id="list">
                    <thead> 
                        <tr>
                            <td>
                                '.t('Name').'
                            </td>
                            <td>
                                '.t('Description').'
                            </td>
                            <td>
                                '.t('URL').'
                            </td>
                            <td>
                                '.t('Software').'
                            </td>
                            <td>
                            </td>
                        </tr>
                    </thead>
                    <tbody>';
            
            $markers = array();            
            foreach($vcards as $vcard) {
                $name = (string)$vcard->fn->text;
                $html .='                                
                    <tr onclick="selectServer(\''.$name.'\');">
                        <td>
                            <a href="#nddlink">'.$name.'</a>
                        </td>
                        <td>
                            '.(string)$vcard->note->text.'
                        </td>
                        <td>
                            <a target="_blank" href="'.(string)$vcard->url->uri.'">
                                '.(string)$vcard->url->uri.'
                            </a>
                        </td>
                        <td>
                            '.(string)$vcard->name.'
                        </td>
                        <td>
                            <img 
                                title="'.(string)$vcard->adr->country.'" 
                                alt="'.(string)$vcard->adr->country.'" 
                                src="'.BASE_URI.'themes/movim/img/flags/'.strtolower((string)$vcard->adr->country).'.png"/>
                        </td>
                    </tr>
                    ';    
                $coord = explode("geo:", $vcard->geo->uri);
                if(isset($coord[1])){
                    $split = explode(",", $coord[1]);
                    $newkey = round(floatval($split[0]),1).",".round(floatval($split[1]),1);
                    if(!isset($markers[$newkey])){
                        $markers[$newkey] = array($name);
                    }
                    else{
                        array_push($markers[$newkey], $name);
                    }
                }
            }
                    
            $html .= '
                    </tbody>
                </table>';
                        
            $javascript = '<script type="text/javascript">
                            var map = L.map("map").setView([40,0], 2);
                            
                            L.tileLayer("http://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                                attribution: "Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Mapnik ©",
                                maxZoom: 18
                            }).addTo(map);';
            
            $id = 0;
            
            foreach($markers as $coord => $arrayOfNames){
                $javascript .= "
                                var marker".$id." = L.marker([".$coord."]).addTo(map);
                                marker".$id.".bindPopup('";
                foreach($arrayOfNames as $name){
                    $action = 'selectServer("'.$name.'");';
                    $javascript .= "<span onclick=\'".$action."\' ><a href=\'#nddlink\'>".$name."</a></span><br />";
                }
                $javascript .= "');
                                ";
                $id++;
            }
            
            $javascript .= '</script>';
        }
        
        return $javascript.$html;
    }
    
    function build()
    {
        $warning = '';
        
        if(isset($_GET['err']))
        switch ($_GET['err']) {
            case 'datamissing':
                $warning = '
                        <div class="message error">
                            '.t('Some data are missing !').'
                        </div> ';
                break;
            case 'jiderror':
                $warning = '
                        <div class="message error">
                            '.t('Wrong ID').'
                        </div> ';
                break;
            case 'passworddiff':
                $warning = '
                        <div class="message error">
                            '.t('You entered different passwords').'
                        </div> ';
                break;
            case 'nameerr':
                $warning = '
                        <div class="message error">
                            '.t('Invalid name').'
                        </div> ';
                break;
            case 'notacceptable':
                $warning = '
                        <div class="message error">
                            '.t('Request not acceptable').'
                        </div> ';
                break;
            case 'userconflict':
                $warning = '
                        <div class="message error">
                            '.t('Username already taken').'
                        </div> ';
                break;
            case 'xmppconnect':
                $warning = '
                        <div class="message error">
                            '.t('Could not connect to the XMPP server').'
                        </div> ';
                break;
            case 'xmppcomm':
                $warning = '
                        <div class="message error">
                            '.t('Could not communicate with the XMPP server').'
                        </div> ';
                break;
            case 'unknown':
                $warning = '
                        <div class="message error">
                            '.t('Unknown error').'
                        </div> ';
                break;
        }
        
        $submit = $this->genCallAjax('ajaxDiscoverServer', "movim_parse_form('account')");
        ?>
        <div id="main">
            <div id="left">
                <?php echo $warning; ?>
            </div>
            <div id="center">
                <h1><?php echo t('Create a new account'); ?></h1>
                <div style="margin: 0em 1.5em;">
                    <p>
                        <?php echo t('Movim is a decentralized social network, before creating a new account you need to choose a server to register.'); ?>
                        <?php echo t('Keep in mind that this server will handle all your personnal data.'); ?>
                    </p>
                    <br />
                    <form name="account">
                        <fieldset>
                            <legend><?php echo t('Step 1 - Search the server'); ?></legend>
                                <div class="clear"></div>
                                <p>
                                    <?php echo t('You can%s enter your server domain name%s. ', '<a href="#nddlink">', '</a>'); 
                                        echo t('Or you can choose a server from this list.'); ?>   
                                </p>
                                <br />
                                
                                <div style="height: 300px;" id="map"></div>
                                
                                <br />
                                <?php echo $this->printServerList(); ?>               
                                
                                <br />
                                <div class="element">
                                    <label for="ndd"><?php echo t("Server"); ?></label>
                                    <input
                                        pattern="^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$"
                                        placeholder="<?php echo t("Enter the server domain (ex: movim.eu)"); ?>"
                                        name="ndd"
                                        id="ndd"
                                    />
                                </div>     
                                
                                <div class="clear"></div>
                                
                                <a
                                    name="nddlink"
                                    class="button icon search color green" 
                                    style="float: right;"
                                    onclick="<?php echo $submit;?>; document.getElementById('fillform').innerHTML ='<?php echo t('Searching...');?>'"
                                >
                                    <?php echo t('Search'); ?>
                                </a>    
                        </fieldset>
                    </form>
                    
                    <div class="clear"></div>
                    <div id="fillform"></div>
                </div>
            </div>
        </div>
        <?php
    }
}
