Movim Changelog
================

v0.9.1 (trunk)
---------------------------
 * CSS fixes
 * Add Last Message Edition support
 * Improve Post discovery in the News page
 * Add stickers support
 * Improve loading time for Chat page
 * Improve Chat bubbles display
 * New compact date display
 * Clean properly the tags in the database
 * Allow tags with special characters
 * Various UI and navigation fixed
 * Use UUID as identifiers for the messages and posts
 * Delete properly the comments when deleting a post
 * Update the dependencies
 * Create an internal API to save some memory and improve session handling
 * Improve image handling in posts
 * Improve overall performances

v0.9
---------------------------
 * New User Interface for the whole project
 * Removed BOSH connections and introduce pure XMPP TLS connections
 * Full real-time + daemon
 * New Blog engine and custom CSS support
 * New post publication system + attachements supported (upload and embed links)
 * Fully responsive design UI based on Material Design
 * Huge code cleanup and refactoring
 * Updated i18n system and new languages
 * New eventing system
 * New administration panel
 * New dedicated chat page and emojis support
 * New project icon and favicon
 * New implementation for the Groups feature
 * New Roster based on Angular
 * Refactor the Contact management system and add a gallery on the profiles
 * New universal-share bookmarklet
 * CSS animations and mobile integration (FirefoxOS and Android)
 * Internet Explorer 11 support
 * PHP7 Support

v0.8.1
---------------------------
 * Add charts in the Statistics
 * Add a Caps support table
 * Fix some Jingle issues
 * New Mud actions to create/update the database and change the administration configuration
 * New InitAccount widget to create persistent PEP node on the first login
 * Clean the Feed widget
 * Fix various CSS bugs + fix mobile UI
 * Add title attribute to some truncated texts
 * Add a new fancy login system
 * Show the status in the Roster
 * Optimize the Presence handling
 * Improve the MUC presence handling
 * Improve the posts CSS
 * Add a fancy XEP visualisator

v0.8.0
---------------------------

 * Refactor the whole Movim sourcecode + clean old code
 * Quite all the Movim widgets are now using a full MVC system
 * Rewrite the core session manager (Sessionx)
 * Add a new localisation system + new translations
 * Move the Movim librairies and dependencies to Composer and convert Modl and Moxl to PSR-0 to simplify the loading and packaging of the libraries
 * Monolog is now the new log library for Movim
 * Lots of warnings fixed
 * Add WebRTC threw Jingle audio-video conferencing
 * Make the UI fully responsible (from smartphone to FullHD screens)
 * The Roster widget has been totally rewriten
 * New picture library manager (with new thumbnail generation system)
 * Better MUC integration in the Chat widget
 * Rich text messages are now supported in the Chat widget
 * Add Vcard4 (http://xmpp.org/extensions/xep-0292.html) support in the profile
 * Implement the new official Movim API (https://api.movim.eu/)
 * Huge sourcecode optimisation
 * Rewrite the Administration panel and split it in many little widgets
 * Move the full configuration system to the database (except the database credentials)
 * List all the Movim network pods on a new page
 * Move the all UI to OpenSans
 * Add Title support during post publication
 * New statistics page for the administrators
 * Rewrite the infos page and move it to a widget, move the data structure from XML to JSON
 * Use SASL2 library (https://github.com/edhelas/sasl2) for the XMPP authentication and add SCRAM-SHA1 mechanism support
 * Split the Profile form in 3 littles forms (general, avatar and localisation)
 * Rewrite the Explore page
 * Move from XML to JSON for the browser-server requests
 * Update the locales

v0.7.2
---------------------------

 * Rewrite Modl to Modl2 with dynamic database update, PDO support (MySQL and PostgreSQL)
 * Add support of XEP-0084: User Avatar
 * Bug fixes in chatroom
 * Complete rewrite f the bookmark/subscription system
 * Huge code optimisation (x10 of some parts)
 * CSS fixes
 * Fix lot of issues on the groups (add youtube video support) + microblog
 * Add a new log system
 * Various minor bug fixed

v0.7.1
---------------------------

 * Huge speed optimisation
 * Fux UI fix
 * Implement picute insertion in posts
 * Chat fix
 * Smiley updated

v0.7.0
---------------------------

 * Media hosting and implementation (picture) @edhelas
 * Group implementation @edhelas @nodpounod
 * Datajar to Modl (https://github.com/edhelas/modl) portage @edhelas
 * Video + picture integration (gallery preview) @edhelas
 * Admin panel with hosting space administration @edhelas
 * URL rewriting @edhelas
 * Multi User Chat @edhelas

v0.6.1
---------------------------

 * Fix SSL certificate problem

v0.6.0
---------------------------

 * Create a new installer @kilian @edhelas
 * Create admin user interface to change conf.xml @edhelas
 * Improved user experience @edhelas

### Core @edhelas ###

 * 100% Moxl integration
 * Add Moxl support to build.sh

### Widgets @edhelas ###

#### Chat ####

 * Support “user is typing”

#### Roster ####

 * bidirectional friendrequests. Users can always see each other
 * little search box to filter the list (nodpounod)

#### Post ####

 * http://xmpp.org/extensions/xep-0071.html some basic WYSIWYG
 * Provide public/private posts

### Datajar ###

 * Support updating of db-schemas.

### Translations ###

 * Pull new translations automaticly into trunk
 * Add new translations

### Moxl ###
 * Support of the XEP-0115 Entity Capabilities, which enables the client to communicate its features and the extent of its XMPP support to the server
 * Implementation of DIGEST-MD5 and CRAM-MD5 as more secure log-in mechanisms

v0.5.0
---------------------------

 * Parse all the Movim messages to make them more “user-friendly” (smileys, links, bb-code like) @Etenil
 * DONE Make a public XML page reporting on the pod status (how many user hosted, version, current status…), to be pinged from pod.movim.eu @edhelas
 * Move DataJar based Classes into a single folder @edhelas
 * Cleaner CSS @edhelas
 * Update dates (like “2 min ago”) automatically @edhelas
 * Clean and move UserConf in a single class @edhelas
 * New UI @edhelas

### Core ###

 * Integrate Datajar @etenil
 * Test Movim on all Datajar back-ends @etenil
 * Write a makefile to manage packaging/pulling dependencies @etenil
 * Provide a more consistent API for the XMPP library (to ease the replacement of JAXL later) @etenil
 * Store the Caps (XEP-115) in the database to cache them @edhelas

### Widgets @edhelas ###

 * Move Profile to a single page
 * Merge “News” and “Feed” in one single widget and create filters (by source, date…)
 * Create a system to cache the Widgets

#### Roster ####

 * Add groups support
 * Fixed Bug : chat link when a contact become online

#### Profile ####

 * New system to switch the presences
 * Change the status

#### Feed/Wall ####

 * Store comments in the database
 * Add comments
 * Show/hide old comments if there is a lot of them (like 2 or more)

#### vCard ####

 * Add Avatar support
 * Date picker for the birth date (kilian)
 * Display client informations

#### Chat ####

 * More consistent UI
 * Store all the Messages in the database to handle them more cleanly

v0.4.0
---------------------------

 * Multisession support
 * Dynamically modify page title
 * image.php to built pictures from the database + ask the browser to cache them
 * Inscription on the Server (XMPP+Movim)
 * HTML5 + HTML Title page notification on a new message
 * Support of HTTP Proxy (installation and configuration)
 * Support of HTTPS Servers
 * Implementation on ORDERBY in the Storage database library
 * Fix language selector
 * Fix Roster display and organisation
 * Fix Chat display
 * Rename some widgets
 * Fix Vcard widget

v0.3.0
---------------------------

 * Widgets debugging
 * Enlarge widgets
 * Notifications
 * Blinking tab title
 * Coloured nicknames
 * Cached conversation
 * Tabbed conversations
 * Blocks-based layout
 * More bug fixes
 * URL Rewriting
 * Logger

v0.2.0
---------------------------

 * Inter-widgets communication
 * Proper disconnection handling
 * Added Installer
 * Changed to static loading
 * Speed optimisations
 * Improved Javascript libraries
 * Added unit-testing structure
 * Restructured the program
 * Reimplemented PHP's session
 * Added Cache
 * Use of SQLite3 as Cache/Session back-end (only for 0.2)
 * Improved theme

v0.1.0
---------------------------

 * Base core
 * Events system
 * Configuration
 * XMPP connection
 * Widget system

