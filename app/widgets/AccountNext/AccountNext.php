<?php

/**
 * @package Widgets
 *
 * @file Subscribe.php
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
 
class AccountNext extends WidgetBase {
    
    function load()
    {
        $this->addcss('accountnext.css');
        $this->addjs('accountnext.js');

        $xml = requestURL('http://movim.eu/server-vcards.xml', 1);
        
        if($xml) {
            $xml = simplexml_load_string($xml);
            $xml = (array)$xml->children();

            $this->view->assign('servers', $xml['vcard']);
        } else {
            $this->view->assign('servers', false);
        }
        
        $this->view->assign(
                    'getsubscriptionform',
                    $this->genCallAjax('ajaxDiscoverServer', "'".$_GET['s']."'")
                    );
                    
        $this->view->assign('ndd', $_GET['s']);
    }

    function ajaxDiscoverServer($ndd) {
        try {
            $dns = dns_get_record('_xmpp-client._tcp.'.$ndd);

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

            global $language;
            
            $stream = simplexml_load_string('<?xml version="1.0"?><stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0"><iq type="get" id="reg1" xml:lang="'.$language.'"><query xmlns="jabber:iq:register"/></iq></stream:stream>');
            $stream->addAttribute('to', $ndd);
            if (false === fwrite($f, $stream->asXML())) {
                 \system\Logs\Logger::log('fail write to stream');
                throw new \Exception('fail write to stream');
            }
            
            unset($stream);

            $response = stream_get_contents($f);
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
            $stream->addAttribute('to', $ndd);
            fwrite($f, $stream->asXML());
            
            fclose($f); unset($f);
            
            if(!empty($elements)) {
                $formview = $this->tpl();
                
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
                    $formh = $form->getHTML($response->iq->query->x->asXML());
                } else{// no <x> element in the XML 
                    $formh = $form->getHTML($response->iq->query->asXML());
                }

                if($formh != '')
                    $instr = '';

                $formview->assign('instr', $instr);
                $formview->assign('formh', $formh);
                $formview->assign('id', $id);
                $formview->assign('ndd', $ndd);
                $formview->assign('domain', $domain);

                $formview->assign(
                    'submitdata',
                    $this->genCallAjax('ajaxSubmitData', "movim_form_to_json('data')"));
                
                $html = $formview->draw('_accountnext_form', true);

                /*
                if(isset($elements['data'])) {
                    $html .= '<img src="data:image/jpg;base64,'.$elements['data'].'"/>';
                }
                */
            } else {
                $html = '
                    <div class="message warning">
                        '.$this->__('create.notfound').'
                    </div>';
            }

            RPC::call('movim_fill', 'subscription_form', $html);
            RPC::commit();
            
        } catch(Exception $e) {
            header(sprintf('HTTP/1.1 %d %s', $e->getCode(), $e->getMessage()));
            header('Content-Type: text/plain; charset=utf-8');
            \system\Logs\Logger::log($e->getMessage());
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
                RPC::call('movim_reload', Route::urlize('accountnext', 'xmppconnect'));
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
                    RPC::call('movim_reload', Route::urlize('accountnext', array(XMPP_HOST, 'xmppcomm')));
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
                    RPC::call('movim_reload', Route::urlize('accountnext', array(XMPP_HOST,'userconflict')));
                    RPC::commit();
                     exit;
                } else if($cond->getName() == 'not-acceptable') {
                    RPC::call('movim_reload', Route::urlize('accountnext', array(XMPP_HOST,'notacceptable')));
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
                    RPC::call('movim_reload', Route::urlize('accountnext', array(XMPP_HOST,'unknown')));
                    RPC::commit();
                     exit;
            }
        } catch(Exception $e) {
            header(sprintf('HTTP/1.1 %d %s', $e->getCode(), $e->getMessage()));
            header('Content-Type: text/plain; charset=utf-8');
            \system\Logs\Logger::log($e->getCode().' '.$e->getMessage().' file:'.$e->getFile().' - l.'.$e->getLine());
        }
    }

    function flagPath($country) {
        return BASE_URI.'themes/movim/img/flags/'.strtolower($country).'.png';
    }
}
