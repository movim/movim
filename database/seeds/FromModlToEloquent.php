<?php

use Phinx\Seed\AbstractSeed;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class FromModlToEloquent extends AbstractSeed
{
    public function run()
    {
        // Migrating the configuration
        $configuration = \App\Configuration::firstOrNew(['id' => 1]);
        $config = DB::table('config')->first();

        if ($config) {
            $configuration->description     = !empty($config->description)
                                                ? $config->description : null;
            $configuration->info            = !empty($config->info)
                                                ? $config->info : null;
            $configuration->unregister      = $config->unregister;
            $configuration->locale          = $config->locale;
            $configuration->loglevel        = $config->loglevel;
            $configuration->username        = !empty($config->username)
                                                ? $config->username : null;
            $configuration->password        = !empty($config->password)
                                                ? $config->password : null;
            $configuration->xmppdomain      = !empty($config->xmppdomain)
                                                ? $config->xmppdomain : null;
            $configuration->xmppdescription = !empty($config->xmppdescription)
                                                ? $config->xmppdescription : null;
            $configuration->xmppwhitelist   = !empty($config->xmppwhitelist)
                                                ? $config->xmppwhitelist : null;
            $configuration->restrictsuggestions = !empty($config->restrictsuggestions)
                                                ? $config->restrictsuggestions : false;

            $configuration->save();
        }

        // Migrating the users
        foreach (array_diff(scandir(DOCUMENT_ROOT . '/users'), ['..', '.']) as $jid) {
            $user = \App\User::firstOrNew(['id' => $jid]);

            $settings = DB::table('setting')->where('session', $jid)->first();
            $privacy = DB::table('privacy')->where('pkey', $jid)->first();

            if ($settings) {
                $user->language = $settings->language;
                $user->nightmode = (bool)$settings->nightmode;
                $user->nsfw = (bool)$settings->nsfw;
            } else {
                $user->nightmode = false;
                $user->nsfw = false;
            }

            if ($privacy) {
                $user->public = $privacy->value;
            }

            $user->save();
        }

        // Migrating the caches
        foreach (DB::table('cache')->get() as $c) {
            $cache = \App\Cache::firstOrNew(['user_id' => $c->session, 'name' => $c->name]);
            $cache->data = $c->data;

            try {
                $cache->save();
            } catch (\Exception $e) {
                // Best effort
            }
        }

        // Migrating the invitations
        foreach (DB::table('invite')->get() as $i) {
            $invite = \App\Invite::firstOrNew(['code' => $i->code]);
            $invite->user_id = $i->jid;
            $invite->resource = $i->resource;

            try {
                $invite->save();
            } catch (\Exception $e) {
                // Best effort
            }
        }

        // Drop all the Modl tables
        DB::schema()->dropIfExists('cache');
        DB::schema()->dropIfExists('caps');
        DB::schema()->dropIfExists('conference');
        DB::schema()->dropIfExists('config');
        DB::schema()->dropIfExists('contact');
        DB::schema()->dropIfExists('encryptedpass');
        DB::schema()->dropIfExists('info');
        DB::schema()->dropIfExists('invite');
        DB::schema()->dropIfExists('message');
        DB::schema()->dropIfExists('postn');
        DB::schema()->dropIfExists('presence');
        DB::schema()->dropIfExists('privacy');
        DB::schema()->dropIfExists('rosterlink');
        DB::schema()->dropIfExists('setting');
        DB::schema()->dropIfExists('sessionx');
        DB::schema()->dropIfExists('subscription');
        DB::schema()->dropIfExists('sharedsubscription');
        DB::schema()->dropIfExists('tag');
        DB::schema()->dropIfExists('url');
    }
}
