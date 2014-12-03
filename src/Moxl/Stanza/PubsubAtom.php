<?php 

namespace Moxl\Stanza;

class PubsubAtom {
    public $id;
    public $name;
    public $jid;
    public $content;
    public $title;
    public $contenthtml = false;
    
    public $geo = false;
    public $comments = false;
    
    public function __construct() {
        $this->id = md5(openssl_random_pseudo_bytes(5));
    }

    public function enableComments() {
        $this->comments = true;
    }
    
    public function __toString() {
        $xml = '
            <entry xmlns="http://www.w3.org/2005/Atom">
                <title>'.$this->title.'</title>
                <author>
                    <name>'.$this->name.'</name>
                    <uri>xmpp:'.$this->jid.'</uri>
                </author>';
        
        if($this->comments)
            $xml .= '
                    <link 
                        rel="replies" 
                        title="comments" 
                        href="xmpp:'.$this->jid.'?;node=urn:xmpp:microblog:0:comments/'.$this->id.'"/>';
                        
        if($this->geo) {
            $xml .= '
                    <geoloc xmlns="http://jabber.org/protocol/geoloc">
                        <lat>'.$this->geo['latitude'].'</lat>
                        <lon>'.$this->geo['longitude'].'</lon>
                        <altitude>'.$this->geo['altitude'].'</altitude>
                        <country>'.$this->geo['country'].'</country>
                        <countrycode>'.$this->geo['countrycode'].'</countrycode>
                        <region>'.$this->geo['region'].'</region>
                        <postalcode>'.$this->geo['postalcode'].'</postalcode>
                        <locality>'.$this->geo['locality'].'</locality>
                        <street>'.$this->geo['street'].'</street>
                        <building>'.$this->geo['building'].'</building>
                        <text>'.$this->geo['text'].'</text>
                        <uri>'.$this->geo['uri'].'</uri>
                        <timestamp>'.date('c').'</timestamp>
                    </geoloc>';
        }

        if($this->contenthtml)
            $xml .= '
                <content type="html">
                    <html xmlns="http://jabber.org/protocol/xhtml-im">
                        <body xmlns="http://www.w3.org/1999/xhtml">
                            '.$this->contenthtml.'
                        </body>
                    </html>
                </content>';
        else
            $xml .= '
                <content type="text">'.$this->content.'</content>';

        $xml .= '
                <published>'.date(DATE_ISO8601).'</published>  
                <updated>'.date(DATE_ISO8601).'</updated>
            </entry>';

        return $xml;
    }
}
