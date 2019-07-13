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

To keep the session open between the Moxl requests, all the session variables are stored into memory at each request. Its integration is therefore based on the Session class.

### Namespaces
Unlike a whole lot of XMPP libraries, Moxl prefers treating messages through XMPP namespaces than via extensions (XEP).

### Details on the features

#### Authentification

The Moxl authentication sequence is based on the SASL2 library. You can find it here [GitHub The PHP SASL2 Authentification Library](https://github.com/fabiang/sasl).

### XMPP Resources

The authentification syste was also adapted to let you connect easily on servers that impose a resource (like Gmail or Facebook). An XMPP resource is a string placed at the end of the JID (Jabber ID or more commonly the address of the user on the XMPP network) that lets you specify the client you use to send your messages. A user can be connected on multiple clients at a time. For example if you send a message at :

  * `user@server.com/android` the user will receive it on his Android
  * `user@server.com/home` the user will receive it on his home computer

The resource by default for Moxl is `moxl` followed by a random hash (which makes the addresses look like `user@server.tld/moxl23ER4S`) but some servers impose a particular resource. Gmail XMPP servers for example use an hash which makes the addresses look like :

  * `user@server.com/ACE45E`

Moxl can adapt to the directives of the XMPP server and lets you connect seamlessly to a wide range of servers.

### XMPP Support

See `doap.xml` at the root of the repository.

#### Pubsub support

Movim is implementing [XEP-0060 - Publish-Subscribe](https://xmpp.org/extensions/xep-0060.html) only partially, here are the parts actually handled by Moxl.

All the items mentionned in this list and their subitems are normally fully implemented in Moxl (except some specific error handling that are trigerring a general error). All the *.* items that are not mentionned are not implemented.

* 5.2 Discover Nodes (collections are not supported)
* 5.4 Discover Node Metadata (pubsub#title, pubsub#num_subscribers and pubsub#description especially)
* 5.6 Retrieve Subscriptions (the subid is not handled)
* 5.7 Retrieve Affiliations
* 6.1 Subscribe to a Node (6.1.5 Configuration Required and 6.1.6 Multiple Subscriptions not supported)
* 6.2 Unsubscribe from a Node
* 6.5 Retrive Items from a Node (items are requested with XEP-0059 - Result Set Management)
* 6.5.8 Requesting a Particular Item (to resolve or refresh some particual items)
* 7.1 Publish an Item to a Node (all the items published are Atom entries for the Communities and Microblog)
* 7.1.2.1 Notification With Payload
* 7.1.2.2 Notification Without Payload (Movim is then resolving the Payload if it's not cached yet using 6.5.8)
* 7.2 Delete an Item from a Node
* 8.1 Create a Node
* 8.1.2 Create a Node With Default Configuration (with pubsub#access_model, pubsub#persist_items, pubsub#max_items)
* 8.2 Configure a Node
* 8.4 Delete a Node
* 8.9 Manage Affiliations (except 8.9.2.4 Multiple Simultaneous Modifications)
* 9.2 Filtered Notifications (+notify for Microblog and some PEP nodes)

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

$c = new AddItem;
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

if (is_array($ns))
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
    public function handle($stanza, $parent = false)
    {
        $sd = new \Modl\SessionxDAO;
        $sd->delete(SESSION_ID);

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
