Moxl Changelog
================

v1.10
---------------------------
* XEP-0153: vCard-Based Avatar

v1.9
---------------------------
* Replace movim/sasl2 with fabiang/sasl
* Remove the support of DIGEST-MD5 and CRAM-MD5

v1.8
---------------------------
* Add XEP-0059: Result Set Management
* Add XEP-0070: Verifying HTTP Requests via XMPP

v1.7
---------------------------
* Add XEP-0385: Stateless Inline Media Sharing (SIMS)
* Add XEP-0313: Message Archive Management

v1.6
---------------------------
 * Big refresh and cleanup of the library
 * Fix and update Jingle
 * Fix and Update XEP-0330: Pubsub Subscription

v1.5
---------------------------
 * Add XEP-0231: Bits of Binary support

v1.4
---------------------------
 * Add a XML Stream parser

v1.3
---------------------------
 * Clean the XEP-0060: Pubsub actions and use the Package system
 * Fix the error handler to fire properly global errors

v1.2.1
---------------------------
 * Add support of the XEP-0050: Ad-Hoc Commands

v1.2
---------------------------
 * Large refactoring to move from de BOSH connection system to Websockets
 * SASL authentication is now part of the Action/Payload system
 * Clean a lot of BOSH related sourcecode
 * Move a lot of Payloads and Actions to the Package system
 * Add the account registration support

v1.1.1
---------------------------
 * The Actions now inherit from the Payloads to gain Package system support
 * The Roster is now refreshed on each connection
 * We get the server Caps during the connection
 * Add anonymous login support
 * Fix an issue during Pubsub subscription

v1.1.0
---------------------------

 * Use the SASL2 (https://github.com/edhelas/sasl2) authentication library
 * Add the SCRAM-SHA1 support
 * Clean a lot of old files

v1.0.0
---------------------------

 * Initial version of Moxl
