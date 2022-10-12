Movim Changelog
================

v0.21 (trunk)
---------------------------
 * Implement XEP-0461: Message Replies
 * Add PWA Push Notification support through the service worker
 * CSS fixes
 * Upgrade Embed to v4.x and add Twitter integration
 * Add a JID blocking feature
 * Add a pod wise JID blocking feature for admins
 * Add the VisioConfig widget in the configuration to configure the default microphone and camera
 * Add sound level detection during call and configuration and display warning if the mic is muted
 * Display messages for incoming and outgoing call and fix related issues
 * Fix the handling of MUC resources with / (#1044)
 * Fix Roster pushes initiated by the server should update movim's roster (#1084)
 * Fix gateway presence handling (#1083)
 * Small redesign of the configuration page to make things more coherent
 * Implement XEP-0080: User Location
 * Add new Location widget
 * Show distance with a contact if both locations are enabled
 * Reorganize and simplify the main menu, add a small submenu that pop on hover
 * Add support for the Unicode 14.0 emojis release through Twemoji 14.0.2

v0.20
---------------------------
* Add a checkbox to disable the Registration feature in the admin panel (#901)
* New CSS for articles table
* Fix Notifications crash on MySQL
* Fix MUC private message self Carbon handling
* Fix a XML entities issues in Post content (#976)
* Add resolveInfos() to resolve Info elements for Posts easily
* New design for Post cards
* Fix chat attachments alignements
* Fix message delivery receipt and chat markers issues with Dino and Conversations
* Improve animated GIF preview
* Add picture resolution and size to Preview
* Fix attached pictures for long posts on public pages
* Enforces pubsub#multi-items support to ensure a good Movim-XMPP server compatibiliy
* Redesign the avatar widget and integrate it in the Profile tab
* Move from OpenSans to Roboto for the default font
* Move to a two columns design for blogs and communities public pages
* Refactor and unify some views between the public and private widgets
* Handle proper > quotes in the Chat bubbles
* Replace michelf/php-markdown with league/commonmark
* Add OMEMO basic support for one-to-one chat
* Show contact self presences in the Account tab in the configuration page
* New design for the general navigation bar on desktop
* Redesign the Contact and Communities information block and drawer to be more compact
* Allow chatrooms to be pinned in Bookmarks2 using a Movim pin extension
* Add Links tab in Contact and Conferences drawer
* Fix picture drawing size before uploading a picture
* Improve the microphone level indicator in Visio
* Remember the last selected microphone and camera in Visio
* Add the chat counter on the back button of opened conversations on mobile
* Add a banner URL configuration to set a banner for the pod from the admin panel
* Add support for the Unicode 13.0 emojis release through Twemoji 13.1.0
* Smarter MAM sync, only retrieve the last month on first sync, set as seen all the "older than one week messages" and do a MAM request when no history when opening a contact chat
* Add support for AESGCM encrypted files (receive only)
* New chatrooms header design
* New design for the chatbox
* Allow discussions links to be copied to the clipboard
* Display recent public contact posts in the drawer
* Add support of HTTP headers in the PUT request in HTTP Upload
* Allow MUC messages to be edited (only in MUC Groups)
* Refactor the message edition architecture to support several editions
* Change counter color in favicon when post notification
* Add OMEMO support for MUC Groups
* Remove the old API code
* Add support of XEP-0393: Message Styling
* Add a BundleCapabilityResolver to link OMEMO bundles with the capability/info table
* Add pagination for Pictures and Links in the Contact and Room drawers
* Implement affiliations modification and ban/unban in Rooms
* Introducing Material Chips in the design, use them for the tags
* Prepare PHP 8.1 support
* Movim can now be installed as a Progressive Web App
* Rewrite Picture to Image and add transparent images support, default pictures are compressed to webp
* Implement the MUC creation flow https://xmpp.org/extensions/xep-0045.html#createroom, fix #1039

v0.19
---------------------------
 * Enable SNI for SSL auth to fix DirectTLS connection with some XMPP servers
 * Fix #884, Don't reload page on status posting
 * Fix #921, Delete encrypted passwords after 7 days
 * Small redesign of the Chatrooms create/join form
 * Add support of Multi-user Chats Modern XMPP standard
 * Chats/Rooms redesign, common button to start/create a conversation
 * Improve the PresenceBuffer SQL requests
 * Automatically close SQL connections after a few seconds
 * Conferences and Subscriptions multiple inserts SQL optimisations
 * Handle Caps in PresenceBuffer to save lots of SQL request
 * Preload the MUC Presences when preparing groups of Messages in Chat
 * Handle vcard-temp avatars refresh in PresenceBuffer to also save some SQL requests
 * Add ~1500 new emojis to the Javascript selector
 * Implement XEP-0201: Best Practices for Message Threads and add Reply feature
 * Add a cache for translations, refreshed when the daemon is launched or by a explicit command
 * Make NewsNav asynchrone
 * Make Menu pure Ajax HTTP based
 * Add "More" button in Communities
 * Order Communities in Servers by last published
 * Rename Communities page in Explore
 * Add WebM and H264 video embedding support in the Chat
 * Add Tenor support for GIF/videos search and integration in Chat
 * Set first the chatrooms with unread messages
 * Resolve also the Message URLs to display a proper embed element
 * Move the picture proxy under picture/ to allow server caching (with fastcgi_cache for example)
 * Remove picture proxy redirection and enforce the proxy for all the pictures
 * Add a cache layer in Widget template system
 * Use the cache layer in Chats items and remove the Chats item placeholder system
 * Display encrypted messages in the conversations
 * Add Chat Markers support for GroupChat and small Channel (< 10)
 * Simplify the URL resolving system to always use cache
 * TLS encryption enabling is non blocking during connection
 * Send the Presence a bit earlier on login
 * Add a "copy to clipboard" button in Preview
 * Firebase Push notification integration for the official client
 * Allow image pasting in Movim to trigger the Upload
 * Allow global drag & drop in Movim to upload a file

v0.18
---------------------------
 * Keep the scroll to the "last read" message when opening a discussion
 * Unify the Chat top bar design between one-to-one and chatrooms
 * Refactor the scrolling system in Chat
 * Fix Chat drag & drop panel header and performances
 * Add audio notifications for incoming chat messages and calls
 * Add a way to lock the Dialog box, can only be closed using the action buttons
 * Add scroll-to-bottom button in Chat
 * Fix several Jingle session + ICE issues
 * Add Chat messages URL pictures resolver
 * Full rewrite of the Movim list system in the UI
 * Remove the Notifications panel from Onboarding, the browser can take care of that
 * Redesign the header of contacts and chatrooms drawers
 * Add a PresenceBuffer to do mass insert of Presences in the SQL DB
 * Complete and fix Jingle/WebRTC implementation in Movim, work on Compatibility with Conversations
 * Add Audio-only call support
 * Added support for XEP-0215: External Service Discovery
 * Fix browser tab Chat counter
 * Global chat counter count the notified chats and not the unread chat messages anymore
 * Add support for Unicode 12.0 emojis
 * Add a refresh system for the Chat header based on the presences (and filtered by notifications)
 * Use UNION ALL instead of OR for messages request (to prevent optimisations issues in SQL)
 * Better handling of Pictures in Posts and Messages
 * Show picture number when selecting one in a gallery when publishing a Post
 * Draw on pictures before uploading them + fix some Upload behaviors
 * Use ImageCapture Web API if it exists to capture images
 * Rewrite and split Rooms in Rooms and Rooms Utils for better performances
 * Add support for XEP-0319: Last User Interaction in Presence and refactor the "last seen" feature
 * Protect Ajax calls when session is dropped, return 403 and redirect properly
 * Add an indexed parent column on Info to ensure the component origins
 * Request all the latest messages in one query instead of a loop in Chats
 * Improve the Draw widget lines quality (christine-ho-dev)
 * New emoji picker (christine-ho-dev)
 * Add on-the-fly picture compression for the Picture proxy for larger pictures
 * Update XEP-0402 to urn:xmpp:bookmarks:1 and add xmpp:movim.eu/notifications:0 extension support
 * Update meme and rage comic stickers pack
 * Add a little indicator when saving draft in PublishBrief
 * Create a new Toast widget
 * Remove favico.js and rewrite it to a custom pure Javascript
 * Fix some notifications issues
 * Added support for XEP-0368: SRV records for XMPP over TLS
 * Use Happy EyeBalls for the IP resolution

v0.17.1
---------------------------
 * Improve Upload widget, add drag & drop feature
 * Remove the custom CSS blog feature
 * Add a touch-slide event for Chat
 * Minor UI fixes
 * Check 7.4 compatibility
 * Add new core contributors to About
 * Disable "set cookies only over HTTPS"

v0.17 – Catalina
---------------------------
 * Remove some paddings in the UI
 * Set public url as a default body for shared Posts
 * Request all the open Chats messages in one request (instead of looping)
 * Add a third way to query widgets (websocket, ajax to daemon and pure ajax)
 * Convert some Widgets RPC calls to pure Ajax
 * Clear message counter when Movim receive a message of the current user through Carbon or MAM (#857)
 * Use the from as a temporary key resolver if the presence id is not set for MUC presences (#893)
 * Fix an error in Picture (filesize when on a missing file) (#810)
 * Implement search.jabber.network and refactor the global search feature
 * Fix handling of JIDs with escaped anti-slash
 * Complete and fix XMPPtoForm
 * Add a checkbox to disable the social features in the admin panel
 * Fix MAM handling for MUC
 * Fix panel and notification management for Chat
 * Add support for XEP-0359: Unique and Stable Stanza IDs
 * Add support for XEP-0380: Explicit Message Encryption
 * Add support for XEP-0422: Message Fastening (for Message Retractation)
 * Add support for XEP-0424: Message Retraction
 * Add "read time" info in posts headers
 * New nights-theme colors
 * More agressive linker killer (24h for sending, 30min for receiving)

v0.16.1
---------------------------
 * Allow the edition of all the sent messages
 * Add an index on Contacts avatarhash
 * Save avatarhash in any cases when retrieving vcard-temps from Presences (even failed or empty ones)
 * Enforce query node if not set when doing a disco#info, some libraries or clients doesn't put it back in the iq answer
 * Put back the public link for posts cards on mobile
 * Improve Communities headers (avatar + padding)
 * Add publish model infos in Communitiy data
 * Handle DNS and timeout (5sec) errors and display an error message in Login (#368)
 * Use generic presence make for MUC presences (#711)
 * Rename SQL_DATE to MOVIM_SQL_DATE (#820)
 * Fix camera switching in Snap
 * Fix Visio call (JS error)
 * Implement XEP-0353: Jingle Message Initiation partially
 * Refactor the Visio and VisioLink widgets to simplify them
 * Allow post sharing in chatrooms (#881)

v0.16 – Cesco
---------------------------
 * Cleanup the unanswered IQ requests after 60 seconds
 * Simplify the Moxl handler
 * Remove object serialization in Session
 * Prevent the communities to fire a "Post deleted" event if the avatar is not set
 * Don't notify or display my own messages in Chats
 * Fix title generation in Template Builder
 * Always display a default title in Blog pages
 * Fix Search tags display (icons, section title and placeholder)
 * Send http://jabber.org/protocol/muc#user in MUC PM
 * Handle separately the ChatStates for MUC PM messages
 * Boost the Search widget loading by defering the Roster injection
 * Slight upgrade of the general CSS (round corners, padding…)
 * Display more info in Chats (Me, chatstates)
 * Redesign the attachment system for the Chat
 * Display all the contacts clients in the ContactActions drawer
 * Add support of pubsub#publish_model in CommunityHeader
 * Fix Bookmarks edition (Chat panel toggle) and show a toast on save
 * Add Communities results to Search
 * Add a Draw widget to the chat page
 * Add an index on user_id on the users table
 * Allow camera switch in Visio
 * Allow Visio notification to also open pop-up
 * Clear all candidates when terminating or starting a new Visio
 * Fix a compatibility issue between Phinx and symphony/console v4.3.4
 * Add support for XEP-0402: Bookmarks 2 and a migration button
 * Add a quick emoji search feature using the :key: system
 * Fix XEP-0085: Chat State Notifications flow
 * Fix Roster item and Bookmark 2 XMPP injection
 * Merge Infos and Capabilities and add Identities support to handle multi-identity par XMPP resource
 * Update HTTP Upload support to urn:xmpp:http:upload:0
 * Fix Groupchat and Headline subject handling
 * Allow calling a specific device from the Contact drawer + specific resource status
 * Only detect Jingle if audio is supported (and not Jingle globally)

v0.15 – Donati
---------------------------
 * Redesign the Communities page
 * Center verticaly the content of cards when they are displayed as flexbox
 * Give feedback when a new Communities Server is explored (#804)
 * Fix Undefined variable: url in _communitysubscriptions.tpl (#802)
 * Fix short columns length in SQL database (#801)
 * Fix XMPP whitelist also filter XMPP servers on registration page (#773)
 * Allow users to set a local nickname and handle blog urls with this nickname
 * Fix "Forever composing" (#130) + ids for composing/paused states
 * Add support of XEP-0367: Message Attaching, add Reactions feature in Movim
 * Move the index.php file to the public/ subfolder and all the assets bellow it
 * Add Snap widget to quickly take pictures and publish them in Chats or Posts
 * Refactor the notification mechanism for the Chat (move the status to the messages table)
 * Comment and remove SQLite support in the project
 * Using XEP-0372: References allow Movim users to share articles in the chats and chatrooms
 * Display errors when the Pubsub nodes config are not saved
 * Add an option to make the Chat page the main one

v0.14.1
---------------------------
 * Replace ZeroMQ sockets with WebSockets, remove reactphp/zmq and php-zmq dependency
 * Make Movim compatible with PHP 7.3
 * Display icon + infobox when the chatroom is public
 * Display the Git HEAD commit hash in ?infos if available
 * Don't reload the page when opening chat from Search
 * Don't reload the discussion when the WS is reconnecting but only append the new messages
 * Add slide-to-close feature in Chats to quickly close one-to-one discussions on touch devices
 * Fix Onboarding issues on some devices
 * Fix scrolling on iOS
 * Add a close button to the Drawer widget
 * Errors and Exceptions handling improvements
 * Fix connection using utf8mb4 on MySQL
 * Update the log system for a simpler one
 * Display Gateway connection status
 * CSS fixes in Forms
 * Disable the dropdown in Form if there is only one choice
 * Add support for embeded images in Forms (for CAPTCHA)
 * Chat bubbles a bit more compact
 * Display single emojis as small stickers
 * Display chat states in MUC, handle the chat states with a new ChatStates class
 * Allow setting Avatars on Communities by combining XEP-0084 (metadata + url) and XEP-0060
 * UI fixes for mobile (tabs)

v0.14 – Scotty
---------------------------
 * Add a picture picker when sharing a URL in a post
 * Merge Publish in PublishBrief
 * Implement XEP-0157 to allow users to contact their administrators
 * Change the Reply button to Share
 * Add a spoiler on NSFW articles in the news feed
 * Show a spoiler on NSFW posts when the filter is enabled in News
 * Enhancements on Visio and CSS improvements
 * Fix date display in Chat on instable connectivity
 * Add a Preview widget to allow previsualisation of pictures in Movim
 * Code cleanup
 * Add support of XEP-0070: Verifying HTTP Requests via XMPP
 * Use longer varchar for some columns in the database (Roster and Posts)
 * Replace movim/sasl with fabiang/sasl
 * Fix several Warnings and Errors
 * Move Pubsub subscriptions to a specific PEP nodes to prevent overwritting by another client
 * Replace Modl with Eloquent
 * Fix IPC cleanup when launching the daemon
 * Add support for MAM configuration
 * Add support for XEP-0153: vCard-Based Avatar
 * Allow avatars to be retrieved and set for chatrooms
 * Move Websocket URI below the base URI (e.g. /ws → /movim/ws)
 * Remove preliminary Debian packaging
 * Bundle moxl
 * Remove several dependencies (heyupdate/emoji, clue/buzz-react, ramsey/uuid) and fix the versions of some of them (react/zmq, rain/raintpl, react/http)
 * Improve handling of Emojis (by mirabilos)
 * Improve performances by using eager loading (for Chats, Posts and Contacts related widgets)
 * Redirect the unauthenticated to the correct page when trying to access ?post
 * Link Chatrooms and Pubsub nodes using muc#roominfo_pubsub in the UI
 * Update the favicon
 * Fix the public pages metadata
 * Update the MaterialIcons font to the Google one
 * Cleanup the CSS
 * Index some columns in the database (Message, Attachements) to improve performances
 * Allow to pick no pictures when sharing a link
 * Add some animations to the CSS
 * Replace the placeholders with the default icon font
 * Improve the search feature
 * Remove the main Contacts page and related widgets (Roster, ContactDisco…)
 * Move the invitations and like/comments notifications to the sidebar
 * Add support for SQLite (JKingweb)
 * Use higher resolution images to have proper avatars in hi-def screens
 * Handle the MUC self-presences using a session state during the join
 * Display the moderator messages differently in MUCs
 * Move the Communities, Blogs and News articles feeds to paginated ones
 * Autoescape templates by default in RainTPL
 * Remove the action buttons in Chat
 * Use XEP-0359: Unique and Stable Stanza IDs as unique identifiers for the Messages in the DB
 * Improve video call and terminate flows
 * Allow the usage of Markdown for the Login page information

v0.13 – Coggia
---------------------------
 * Update ReactPHP
 * Use PHP ZeroMQ to manage the communications between the processes
 * Cleanup some existing buffers
 * Add a pure HTTP ajax endpoint for some futur requests that needs it
 * Add some slight animations in the UI
 * Add a nightmode
 * Cleanup and refactorize some CSS (colors, forms)
 * Improve the connectivity UX status of chatrooms
 * Publish the chat messages using Ajax
 * Improve the configuration of Communities
 * Update the OpenSans font

v0.12.1
---------------------------
 * Add xmpp: uri to public pages headers
 * Code cleanup (by RyDroid)
 * Remove gender, marital and the Skype/Twitter/Yahoo account info
 * Fix Content-Security-Policy
 * UI improvement for the bottom navigation on mobile
 * Cleanup the Privacy Model
 * Set a max-width for the picture preview in Upload
 * Add application/javascript header to prevent MIME type checking issue
 * Redesign the Communities page
 * Remove the CommunitiesDiscover widget

v0.12 – Lovejoy
---------------------------
 * Add autojoin support for chatrooms
 * New Contact page
 * Improve Posts tags detection and navigation
 * New system to recover the session quickly
 * New PublishBrief widget
 * Add support for MUC invitations
 * Don't notify if the user is not in the Roster
 * UI optimisations
 * Better integration for Youtube videos
 * Add support of XEP-0333: Chat Markers
 * Update the translations
 * New design for the post Material Design cards
 * New UI for adding a contact through a Gateway (by singpolyma)
 * Allow users to clear their information on the instance and leave it properly
 * Add NSFW filter configuration
 * Save Draft of publications in Publish and PublishBrief
 * Add touch support to open the menu on mobile devices
 * Improve Stickers picker
 * Display more information in the Rooms list
 * Suggest public and open chatrooms on the Chat page
 * New navigation menu for mobile devices
 * Rotate correctly the JPEG files when uploading them
 * Add support of private MUC messages
 * Redesign of the Community main page
 * Refactor and cleanup the management of the comments internaly
 * Autocomplete nicknames in MUC using tabulation (by pztrn)
 * Add picture preview when posting links in MUC
 * Redesign the MUC bubbles to unify the style with the simple chats
 * Enable history for MUC
 * Pictures can be previewed before upload
 * Do not send the message when carriage return is pressed on mobile
 * More colors!
 * Protect pictures URLs with a HTTP HEAD check
 * Add Miho sticker pack
 * Add support of MAM (up to mam:2) for the MUCs

v0.11 – Tuttle
---------------------------
 * Navigation improvement
 * Add previous/next post shortcut in the footer of each posts
 * Highlight mentionned messages in chatrooms
 * Non alpha-numeric Pubsub items and nodes support
 * Non alpha-numeric JID support
 * Fix Markdown links with underscores
 * Fix two-way contact subscription button in Contact
 * New simplified and optimized Roster
 * Improved search (global and roster)
 * CSS fixes
 * Refactoring of the groups page UI and UX
 * Add (small) picture embeding in chats
 * Various speed optimisation
 * Add reply feature of existing posts
 * New and improved Share widget, now supports xmpp: links
 * New Stickers!
 * Big refactoring of the Groups, now called Communities with improved navigation and discovery features
 * Also refactor the Post widget
 * Add an Onboarding widget with some advices
 * Add Like feature
 * New Notifications widget to keep track of the comments and likes
 * Improvements in Carbons feature
 * Improve the Stickers picker
 * Refactor and cleanup the session management

v0.10 – Holmes
---------------------------
 * Resize and compress large pictures in Upload
 * Refactor MovimWebsocket and fix disconnection issues
 * Remove and cleanup old code
 * Handle errors when uploading large files
 * New bubble merging algorythm in the Chat
 * Improve UI and mobile UX on low resolution devices
 * New widget Drawer used for the stickers and the search form
 * Fix behaviour for Android and Electron packages
 * Fix Pubsub metadata handling for some XMPP servers
 * Add global search
 * Add silent notifications for chatrooms
 * Add alternate nickname support (adding "_") when joining a chatroom
 * Allow room configuration edition
 * Put your own XMPP server as default in the configuration (movim.eu in fallback)
 * Close the Dialog box when pressing ESC
 * Moving values from Sessionx to Session
 * Using chart.js for the statistics
 * Refactor the "public" system for the Posts
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

v0.9 – Tchouri
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

v0.8.1 – Polar Aurora
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

v0.8.0 – Polar Aurora
---------------------------

 * Refactor the whole Movim sourcecode + clean old code
 * Quite all the Movim widgets are now using a full MVC system
 * Rewrite the core session manager (Sessionx)
 * Add a new localisation system + new translations
 * Move the Movim libraries and dependencies to Composer and convert Modl and Moxl to PSR-0 to simplify the loading and packaging of the libraries
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

v0.7.2 – Sandstorm
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

v0.7.1 – Sandstorm
---------------------------

 * Huge speed optimisation
 * Fux UI fix
 * Implement picute insertion in posts
 * Chat fix
 * Smiley updated

v0.7.0 – Sandstorm
---------------------------

 * Media hosting and implementation (picture) @edhelas
 * Group implementation @edhelas @nodpounod
 * Datajar to Modl (https://github.com/edhelas/modl) portage @edhelas
 * Video + picture integration (gallery preview) @edhelas
 * Admin panel with hosting space administration @edhelas
 * URL rewriting @edhelas
 * Multi User Chat @edhelas

v0.6.1 – Cumulus
---------------------------

 * Fix SSL certificate problem

v0.6.0 – Cumulus
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

v0.5.0 – Snowball
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
