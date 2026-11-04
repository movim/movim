Movim Installation
==================

This tutorial describes the different steps you need to take to deploy Movim.

# General Architecture

It's mandatory to understand the general architecture of the project to a certain extent before trying to deploy it.

When you use Movim, it acts as an intermediary between the user's browser and XMPP servers. All the data that is sent and received by these two parties are managed by the Movim core, some of them are also saved in a database (for cache purposes).

From the browser perspective, all communication with Movim is done using WebSockets and Ajax requests (except for the "default" page loading). These sockets are proxied through your web-server to the Movim daemon. On the XMPP side Movim connects using pure TCP connections (like any XMPP client).

So all these streams will be managed by the Movim daemon. This daemon needs to be launched with the same user and rights as the web-server (most of the time using the ``www-data`` user).

## Wide Conference Calls

Movim also provides an XMPP service that handles the audio and video streams when doing conference calls. Once you have successfully finished this tutorial, follow the [configuration advice](#configuring-wide-conferences) at the end to set this up.

# Getting Started

Movim can be deployed on any Linux distribution given the following are installed:
  * A webserver (configuration advice for Apache, nginx, and Caddy are provided in the tutorial)
  * A PHP process manager (like `php-fpm`)
  * An SQL server
     * PostgreSQL (**_strongly recommended_** some features are only available on this database)
     * MariaDB 10.2 or higher with utf8mb4 encoding (necessary for emojis 😃 support) AND `utf8mb4_bin` collation
     * MySQL is __NOT__ supported and will throw errors during the migrations, please use PostgreSQL or MariaDB
  * **PHP 8.4 minimum** with the following extensions:
     * PHP mbstring (package `php-mbstring`)
     * PHP ImageMagick and GD for the picture processing (package `php-imagick` and `php-gd`)
     * Your database PHP driver (package `php-pgsql` or `php-mysql` depending on the type of database server you want to use).
     * PHP XML (package `php-xml`)
     * PHP Zip (package `php-zip`) to import Emojis
 * [Composer](https://getcomposer.org/), the PHP package manager

Installing Git is also recommended to download Movim's source code.

### Debian/Ubuntu

The required PHP dependencies can be installed with this command:

```bash
apt install composer php-fpm php-mbstring php-imagick php-gd php-pgsql php-xml php-zip
```

# Installation Steps

## 1. Download the Movim Source

You can download a stable snapshot of this repository on our [Release page](https://github.com/movim/movim/releases) or use git to clone the repository.

The source code needs to be extracted or cloned into a directory owned by your webserver user

> [!NOTE]
> The `www-data` user is the most common for GNU/Linux web servers and will be used in the rest of this tutorial, please ensure to use the correct user on your system.

Here is a full example using git:

```bash
cd /var/www/ # Server directory
mkdir movim
chown www-data:www-data movim
sudo -s -u www-data # We use the web-server user
# We copy the source code from the repository
git clone https://github.com/movim/movim.git
```

## 2. Install Dependencies

Movim requires several dependencies to work properly. These libraries are managed using [Composer](https://getcomposer.org/):

```bash
cd movim
composer install
```

> [!NOTE]
> You can install Composer in the Movim directory using the following command or by using your package manager:
> ```bash
> curl -sS https://getcomposer.org/installer | php
> ```
> Now you will be able to install the dependencies.
> ```bash
> # Finally install your project's dependencies
> php composer.phar install
> ```

## 3. Rights Check

Movim needs read permissions on its root folder and recursively to be deployed properly. It will try to create these folders on startup:

* `log/` for the Movim logs
* `cache/` for the internal cache (templates and other system files)
* `public/cache/` for the public caches (pictures, CSS, Javascript…)

You can create the folders in advance and Movim will skip this step, otherwise you must set write permissions on its root folder:

```bash
# Use the root user to do these commands
mkdir movim/cache movim/log movim/public/cache
chown -R www-data:www-data movim && chmod u+rwx movim
```

## 4. DotEnv Configuration

Movim relies on [DotEnv](https://github.com/vlucas/phpdotenv) for its configuration.

To configure Movim, copy the [`.env.example`](./.env.example) file to a new `.env` one and set it accordingly.

```bash
cp .env.example .env
nano .env
```

> [!NOTE]
> Feel free to leave the `GALENER_*` environment variables blank, this is covered after the tutorial.

## 5. Database Setup

Once the database is configured in your `.env` file, create or update the database structure using [Composer](https://getcomposer.org/):

```bash
composer movim:migrate
```

## 6. Start the Daemon

To let the browser communicate with the Movim server, you need to launch the daemon. It needs to be launched using the web server user:

```bash
cd /var/www/movim
sudo -s -u www-data
php daemon.php start # Launch the daemon
```

If everything runs as expected you should see:

```bash
Movim daemon launched
Base URL : {public url of your pod}
…
```

This daemon will be killed once your console is closed. Consider using `systemd` or `init` scripts to keep the daemon running in the foreground even after your disconnection. We provide some example startup files, like a `systemd` service file, in the [`etc/`](./etc/) directory.

## 7. Web Server Configuration

### 7.1. Virtualhost

Create a virtualhost on your preferred webserver and point the root to the `public/index.php` file. You can have a look at the [default configuration Apache, Caddy or nginx files that we provide](./etc) if you need some help about that part.

Movim is developed to work at the root of a domain or subdomain. __Don't try to deploy it as a subdirectory__ or you might face some unexpected issues.

### 7.2. Daemon Websocket Proxy

When you launch the daemon, **it will generate the configuration** required for your webserver to "proxify" the WebSockets and display it in the console.

These configurations are dynamically generated to fit your current setup. Whether you use Apache, Caddy, or nginx, each configuration will be displayed and will continue to be displayed even after you have successfully applied them.

### 7.3. Picture Proxy Cache

Movim automatically proxyfies external pictures to protect user IPs and prevent large pictures to be loaded without user consent. This internal proxy also asks the browser to cache the pictures for a few hours.

It is however **strongly recommended** to setup a server side cache to prevent multiple users to request the same resource through Movim. Please follow the advice for your webserver below.

### On Apache

You will need Apache mods `cache_disk`, `expires`, and `headers`. You can enable them with the a2enmod command. Example: `a2enmod cache_disk`

Then add this section to your Movim VirtualHost:

```
CacheQuickHandler on
CacheLock on
CacheLockPath /tmp/mod_cache-lock
CacheLockMaxAge 5
CacheIgnoreHeaders Set-Cookie
<Location /picture>
    CacheEnable disk
    CacheHeader on
    CacheDefaultExpire 6000
    CacheMaxExpire 3600
    CacheIgnoreNoLastMod On
    ExpiresActive on
    ExpiresDefault A3600
    Header set Cache-Control public
    Header merge Cache-Control max-age=604800
    FileETag All
</Location>
```

Afterwards, reload Apache.

### On nginx

To do so you can configure ```fastcgi_cache``` on nginx, [check the related documentation](http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_cache).

Add this to your `nginx.conf` in the `http` section:

```
http {
    …
    fastcgi_cache_path /tmp/nginx_cache levels=1:2 keys_zone=nginx_cache:100m inactive=60m;
    fastcgi_cache_key "$scheme$request_method$host$request_uri";
}
```

And check the related documentation in our nginx example file [movim.conf](./etc/nginx/conf.d/movim.conf).

### On Caddy

You may want to take a look at xcaddy and compiling the server with the following modules: [cache-handler](https://github.com/caddyserver/cache-handler), [cdp-cache](https://github.com/sillygod/cdp-cache).

## 8. Success!

Load up the URL you configured for the `DAEMON_URL` environment variable in a web browser, and you should see Movim's login page. Once you log in with your XMPP account (to register it in the database), your account can be set as an instance admin with the following command:

```bash
sudo -u www-data php daemon.php setAdmin <JID>
```

The admin panel will then available in your settings.

Instance administrators will be listed on the login page.

> [!NOTE]
> Some of the admin configuration elements are only applied after the restart of the daemon.

# Post-Install Advice

## Updating

You can update your Movim instance with the following lines (always check the [release notes](https://github.com/movim/movim/releases) for any incompatibilities with your current version).

```bash
cd /var/www/movim/
git pull # To update the Movim source-code
composer install # To update the libraries
```

If the update comes with some database changes, please ensure you run the new migrations [as described above](#5-database-setup).

## Configuring Wide Conferences

You have a working Movim installation, but new in version `0.35` comes scalable conference calls! See the dedicated [Galener documentation](doc/GALENER.md) to set this up.
