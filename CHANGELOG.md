Moxl Changelog
================

v1.2
---------------------------
 * Large refactoring to move from de BOSH connection system to Websockets
 * SASL authentication is now part of the Action/Payload system
 * Clean a lot of BOSH related sourcecode

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
