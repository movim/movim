# Moxl

Moxl is the official XMPP library of the Movim project. It replaces Jaxl from version 0.6 (included).

## History
Moxl (for Movim XMPP Library) was developed in summer 2012 by Timothée Jaussoin after the development team decided to replace the Jaxl library which started to be too limited for the needs of the Movim project.

Moxl was partially rewritten during the autumn 2014 to work with the new Movim WebSocket daemon. The library was previously working exclusively with BOSH. All the source-code relative to BOSH was removed since. 

## Features
Its way of working is fundamentally different from Jaxl's. Moxl was created to communicate with the XMPP server only in HTTP(S). That is why there is no synchronous mode or system that can make it work like a daemon.

Moxl manage the XMPP packet received by the Movim daemon, parse them and send the resulting events to the Movim core, thought this same daemon.

Fundamentally, Moxl always works as an asynchronous library.

### Sessions

To keep the session open between the Moxl requests, all the session variables are stored into database at each request. Its integration is therefore based on the Session class which is itself based on Modl (https://github.com/edhelas/modl).

It can be interesting to know that all the session parameters (session hash, id, user, target server...) are stored in memory throughout the daemon execution, for performance reasons.

### Namespaces 
Unlike a whole lot of XMPP libraries, Moxl prefers treating messages through XMPP namespaces than via extensions (XEP).

### Details on the features

#### Authentification
For the authentification, Moxl can currently connect in several modes :
  * PLAIN : plain authentification, without encryption of the password 
  * DIGEST-MD5 : secured authentification based on secret exchange between the client and the server
  * CRAM-MD5
  * SCRAM-SHA1
  * ANONYMOUS

The Moxl authentication sequence is based on the SASL2 library. You can find it here [GitHub The PHP SASL2 Authentification Library](https://github.com/edhelas/sasl2). 

### XMPP Resources

The authentification syste was also adapted to let you connect easily on servers that impose a resource (like Gmail or Facebook). An XMPP resource is a string placed at the end of the JID (Jabber ID or more commonly the address of the user on the XMPP network) that lets you specify the client you use to send your messages. A user can be connected on multiple clients at a time. For example if you send a message at :
 
  * `user@server.com/android` the user will receive it on his Android
  * `user@server.com/home` the user will receive it on his home computer

The resource by default for Moxl is `moxl` followed by a random hash (which makes the addresses look like `user@server.tld/moxl23ER4S`) but some servers impose a particular resource. Gmail XMPP servers for example use an hash which makes the addresses look like :

  * `user@server.com/ACE45E`

Moxl can adapt to the directives of the XMPP server and lets you connect seamlessly to a wide range of servers.

### XMPP Support

| Number | Name | Implemented | Comments |
|--------|------|-------------|----------|
| [XEP-0004](http://xmpp.org/extensions/xep-0004.html) | Data Forms | Yes | For account creation form + all Pubsub configuration |
| [XEP-0012](http://xmpp.org/extensions/xep-0012.html) | Last Activity | Yes |  |
| [XEP-0030](http://xmpp.org/extensions/xep-0030.html) | Service Discovery | Yes |   | 
| [XEP-0045](http://xmpp.org/extensions/xep-0045.html) | Multi-User Chat | Yes |   |
| [XEP-0048](http://xmpp.org/extensions/xep-0048.html) | Bookmarks | Yes | MUC + URL + PubsubSuscription support |
| [XEP-0049](http://xmpp.org/extensions/xep-0049.html) | Private XML Storage | Yes | To store Movim account configuration |
| [XEP-0050](http://xmpp.org/extensions/xep-0050.html) | Ad-Hoc Commands | Yes | |
| [XEP-0054](http://xmpp.org/extensions/xep-0054.html) | vcard-temp | Yes | Add Gender + Marital elements (non-standard) |
| [XEP-0060](http://xmpp.org/extensions/xep-0060.html) | Publish-Subscribe | Yes | Implemented for the Groups + Microblog |
| [XEP-0071](http://xmpp.org/extensions/xep-0071.html) | XHTML-IM | Yes | Used for Pubsub publication | 
| [XEP-0077](http://xmpp.org/extensions/xep-0077.html) | In-Band Registration | Yes | jabber: x:oob support |
| [XEP-0080](http://xmpp.org/extensions/xep-0080.html) | User Location | Not yet | For message reception in XEP-0277 + Contact Location |
| [XEP-0084](http://xmpp.org/extensions/xep-0084.html) | User Avatar | Yes | Read and Write |
| [XEP-0085](http://xmpp.org/extensions/xep-0085.html) | Chat State Notifications | Yes | composing/paused only |
| [XEP-0092](http://xmpp.org/extensions/xep-0092.html) | Software Version | Partially | Send only |
| [XEP-0100](http://xmpp.org/extensions/xep-0100.html) | Gateway Interaction | Yes | |
| [XEP-0107](http://xmpp.org/extensions/xep-0107.html) | User Mood | Yes | Read only |
| [XEP-0108](http://xmpp.org/extensions/xep-0108.html) | User Activity | Yes | Read only |
| [XEP-0115](http://xmpp.org/extensions/xep-0115.html) | Entity Capabilities | Yes |  |
| [XEP-0118](http://xmpp.org/extensions/xep-0118.html) | User Tune | Yes | Read only |
| [XEP-0124](http://xmpp.org/extensions/xep-0124.html) | Bidirectional-streams Over Synchronous HTTP (BOSH) | Not anymore | |
| [XEP-0163](http://xmpp.org/extensions/xep-0163.html) | Personal Eventing Protocol | Yes | See XEP-0277 |
| [XEP-0172](http://xmpp.org/extensions/xep-0172.html) | User Nickname | Yes | Contact Nickname |
| [XEP-0184](http://xmpp.org/extensions/xep-0184.html) | Message Delivery Receipts | Yes | Handled but not displayed + Send |
| [XEP-0199](http://xmpp.org/extensions/xep-0199.html) | XMPP Ping | Yes |  |
| [XEP-0206](http://xmpp.org/extensions/xep-0206.html) | XMPP Over BOSH | Not anymore | |
| [XEP-0224](http://xmpp.org/extensions/xep-0224.html) | Attention | Yes |  |
| [XEP-0231](http://xmpp.org/extensions/xep-0231.html) | Bits if Binary | Yes | For the Stickers feature |
| [XEP-0245](http://xmpp.org/extensions/xep-0245.html) | The /me Command | Yes |  | 
| [XEP-0256](http://xmpp.org/extensions/xep-0256.html) | Last Activity in Presence | Yes |  |
| [XEP-0277](http://xmpp.org/extensions/xep-0277.html) | Microblogging over XMPP | Yes | |
| [XEP-0280](http://xmpp.org/extensions/xep-0280.html) | Message Carbons | Yes | |
| [XEP-0292](http://xmpp.org/extensions/xep-0292.html) | vCard4 Over XMPP | Yes | |
| [XEP-0330](http://xmpp.org/extensions/xep-0330.html) | Pubsub Subscription | Yes | Using PEP, proposed by the Movim team|
| [XEP-0363](http://xmpp.org/extensions/xep-0363.html) | HTTP File Upload | Yes | |

## Internal Operation 
### Structure of the library 

Here is the structure of the directories which compose Moxl :

```
Moxl/
|-- Stanza/
|
`-- Xec
    |-- Action/
    `-- Payload/
```

#### Stanza
This directory contains a set of functions used to generate valid Stanza (XMPP requests) by building up XML packets. These functions are grouped by theme in several files (`Presence.php`, `Message.php`…).

These functions therefore return a string containing an XML which will be sent to the requestor.

## XEC

XEC (for XMPP Event Controller) is a sub module of Moxl. It smartly manages requests passed through Moxl. XEC is divided into two parts; Actions and Payloads.

In both cases, XECPayload and XECHandler are to be developed by the integrater to link the events from Moxl to the ones of the target application.

### Action 
A XEC action is a request made to the XMPP server. In this situation XEC provides a system to let Moxl "remember" past requests and send back the result to the appropriate requestor.

```php
namespace Moxl\Xec\Action\Roster;

use Moxl\Xec\Action;
use Moxl\Stanza\Roster

class AddItem extends Action
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        Roster::add($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza) 
    {
        var_dump('Handle item');
    }
    
    public function errorServiceUnavailable() 
    {
        var_dump('Handle the Error !');
    }
}
```

This example shows the "add a contact to the contacts list" action, and this action inheritates from `XECAction`. To add a contact you can now use the following :

```php
use Moxl\Xec\Action\Roster\AddItem;

$c = new AddItem();
$c->setTo('contact@serveur.com')
  ->request();
```

Note that the line
```php
$this->store();
```
is mandatory to make XEC initialize the request and later on manage its result.

#### Requests System
The `store()` action saves the instance at its current state and stores it in an array containing all the ongoing demands. This instance will be identitfied by the XMPP request ID.

To match the result to the request, XECHandler will check that the ID of the stanza exisits in the array and reinstanciate the class. Therefore the developer won't even know that the request and the result were on two different executions (via two distinct requests on the XMPP server). The request is launched by the `request()` method and its result is received by the `handle()` method of the same instance.

#### Response
If everything goes well the result to the request will be received by the `handle($stanza)` method, where $stanza is the result converted in SimpleXML format.

The values of the attributes of the class are saved as well and given back when the result arrives. You are free to play with that feature if you want to keep some values between the request and its result.

##### Errors handling
The request can be correctly treated as well as it can raise an error on the XMPP side. XEC can handle this error result and tries to call an appropriate method in the requestor class. More precisely it looks for a method named like the error string given in a CamelCase format.

You are free to handle some of these errors or not. They will be logged anyway via syslog in `/var/log/user.log`. In the example below you can see the method that should handle the errorServiceUnavailable error.
This error handling system is interesting because it warns the user of a misuse directly from the browser (just like Movim can do it).

### Payload
The payload are kind of an opposit to the actions. They are stanza sent by the server but not requested by the client (Moxl in our situation). Typically, it is messages sent by contacts in a conversation. In the same way Moxl tries to understand what kind of stanza it is thanks to XEC.

The operation is a bit different here. Here is an extract of XECHandler which deals with hashing the payload stanza.

```php
require('XECHandler.array.php');
        
$name = $s->getName();
$ns = $s->getNamespaces();
$node = (string)$s->attributes()->node;
        
if(is_array($ns))
    $ns = current($ns);

$hash = md5($name.$ns.$node);

MoxlLogger::log('XECHandler : Searching a payload for "'.$name . ':' . $ns . ' [' . $node . ']", "'.$hash.'"');
```

Basically, XECHandler generates a unique hash for a "type" of payload. To generate it it uses three elements :
  * name : the name of the stanza
  * ns : the namespace of the stanza
  * node : the name of the "node" attribut if it exists (most of the time it's empty but it is mandatory for payloads that come from particular pubsub nodes).

#### Example
When a post is published by one of your contacts on his microblogging feed, here is how the XMPP server notifies Movim :

```xml
<event xmlns='http://jabber.org/protocol/pubsub#event'>
  <items node='urn:xmpp:microblog:0'>
    <item id='1cb57d9c-1c46-11dd-838c-001143d5d5db' publisher='romeo@montague.lit'>
     <entry xmlns='http://www.w3.org/2005/Atom'>
       <title type='text'>hanging out at the Caf&amp;#233; Napolitano</title>
       <link rel='alternate'
             type='text/html'
             href='http://montague.lit/romeo/posts/1cb57d9c-1c46-11dd-838c-001143d5d5db'/>
       <link rel='alternate'
             href='xmpp:romeo@montague.lit?;node=urn%3Axmpp%3Amicroblog%3A0;item=1cb57d9c-1c46-11dd-838c-001143d5d5db'/>
       <id>tag:montague.lit,2008-05-08:posts-1cb57d9c-1c46-11dd-838c-001143d5d5db</id>
       <published>2008-05-08T18:30:02Z</published>
       <updated>2008-05-08T18:30:02Z</updated>
     </entry>
   </item>
</event>
</items>
```

From the snippet you can see that :
  * name : `item`
  * ns : `http://jabber.org/protocol/pubsub#event`
  * node : `urn:xmpp:microblog:0`

XECHandler will then make an MD5 hash of `$name.$ns.$node` and search for the resulting string in the array contained in `XECHandler.array.php`.

Here the resulting string is `96c06e02022480352b6c581286b7eefb`.

```php
$hashToClass = array(
    '9b98cd868d07fb7f6d6cb39dad31f10e' => 'Message',
    'e83b2aea042b74b1bec00b7d1bba2405' => 'Presence',
    
    '96c06e02022480352b6c581286b7eefb' => 'Post'
    );
```

If the resulting string is one of the keys of the array, the value that corresponds to a class will be instanciate and the `handle()` method will be called.

```php
namespace Moxl\Xec\Payload;

class Post extends Payload
{
    public function handle($stanza) {   
        var_dump('Post received');
    }
}
```

In the example above, all the Posts of type Microblog will be handled by this class. You can then do what you want with the $stanza received.

#### In-depth research
The search for the appropriate Handlers is not only done in one level as you could see it on the above example. XECHandler will also search for them inside the stanza to try and find subinformation that could be interesting for the developer and dispatch events upon them.

The in-depth research is limited to three levels maximum (corresponding to three XML levels), mainly for performance purposes.

### Packet, commuication with Movim 

To standardise and unify the events sent to Movim the Payloads and Actions can emit Packets identified by an unique key generated by Moxl.

Theses Packets can be emitted from the `handle()` method or the error methods.

#### Example

The following example will help us to understand precisely how works the Packets system.

```php
namespace Moxl\Xec\Payload;

class SASLFailure extends Payload
{
    public function handle($stanza, $parent = false) {
        $session = \Sessionx::start();
        $session->destroy();
        
        $this->pack($stanza->children()->getName());
        $this->deliver();
    }
}
```

This Payload manage the case of a failing [SASL](http://fr.wikipedia.org/wiki/Simple_Authentication_and_Security_Layer) authentication. After destroying the session, two Packets relative methods are called.

  - `$this->pack()` create the package containing the data that we plan to send to the Movim core (you can put any type of data).
  - `$this->deliver()` send the Packet to the Movim events manager.

#### Keys generation

The keys will be automatically generated by Packet. Each Packet, identified by a key will fire a unique event to the Movim core. Each interested Movim widget can ask to receive this specific event using the method described on this page [[en:dev:widgets#registerevent_event_key_method|Execution environment]] FIXME.

Each key is generated using two specific patterns.

##### For the Actions

`[last namespace]_[classname]_[method]` in lowercase.

In the case of a successfull `Moxl\Xec\Action\Vcard\Get` action we will get : `vcard_get_handle`.
If the action is followed by a XMPP error, Moxl will apply the same pattern : `vcard_get_erroritemnotfound`.

##### For the Payloads

`[classname]` in lowercase.

For our example, the key will be : `saslfailure`.
