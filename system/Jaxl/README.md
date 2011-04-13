# JAXL (Jabber XMPP Client and Component Library in PHP)

Jaxl 2.x is an object oriented XMPP framework in PHP for developing real time applications
for browsers, desktops and hand held devices. Jaxl 2.x is a robust, flexible and easy to use
version of Jaxl 1.x series which was hosted at google code.

* More robust, flexible, scalable and easy to use with event mechanism for registering callbacks for xmpp events
* Integrated support for Real Time Web (XMPP over Bosh) application development
* Support for DIGEST-MD5, PLAIN, ANONYMOUS, X-FACEBOOK-PLATFORM authentication mechanisms
* 32 implemented XMPP extensions [(XEP's)](http://xmpp.org/extensions/) including MUC, PubSub and PEP
* Setup dynamic number of parallel XMPP instance on the fly
* Monitoring, usage stat collection, rate limiting and production ready goodies

## Download

* For better experience download [latest stable tarball](http://code.google.com/p/jaxl/downloads/list) from *google code*
* The development version of Jaxl is hosted here at *Github*, have fun cloning the source code with Git

Warning: The development source code at Github is only intended for people that want to develop Jaxl or absolutely need the latest features still not available on the stable releases.

## Writing XMPP apps using JAXL library

* Download and extract inside `/path/to/jaxl`
* Jaxl library provide an event based mechanism exposing hooks like `jaxl_post_auth`
* Register callback(s) inside your app code for required events (see example below)
* Write your app logic inside callback'd methods

Here is how a simple send chat message app looks like using Jaxl library:

    // Include and initialize Jaxl core
    require_once '/path/to/jaxl/core/jaxl.class.php';
    $jaxl = new JAXL(array(
        'user'=>'username',
        'pass'=>'password',
        'host'=>'talk.google.com',
        'domain'=>'gmail.com',
        'authType'=>'PLAIN',
        'logLevel'=>5
    ));

    // Send message after successful authentication
    function postAuth($payload, $jaxl) {
        global $argv;
        $jaxl->sendMessage($argv[1], $argv[2]);
        $jaxl->shutdown();
    }

    // Register callback on required hook (callback'd method will always receive 2 params)
    $jaxl->addPlugin('jaxl_post_auth', 'postAuth');

    // Start Jaxl core
    $jaxl->startCore('stream');

Run from command line:

    php sendMessage.php "anotherUser@gmail.com" "This is a test message"

## Useful Links

* [PHP Documentation](http://jaxl.net/)
* [Developer Mailing List](http://groups.google.com/group/jaxl/)
* [Issue Tracker](http://code.google.com/p/jaxl/issues/list?can=1&q=&colspec=ID+Type+Status+Priority+Milestone+Owner+Summary&cells=tiles)

Generate Jaxl documentation on your system for quick reference:
    
    phpdoc -o HTML:Smarty:PHP -ti "JAXL Documentation" -t /var/www/ -d xmpp/,xep/,env/,core/

