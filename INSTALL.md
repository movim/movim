Movim Deployment
===================

This tutorial describes the different steps you need to take to deploy Movim on your webserver.

# Get-Started

Movim requires some dependencies to be setup properly.
  * A fully working webserver like Apache, nginx or Caddy
  * A PHP process manager like php-fpm will usually be required for Nginx
  * Root access by SSH with access to the webserver user (most of the time via the user www-data)
  * A SQL server with a schema for Movim (more info in [Database Configuration](https://github.com/edhelas/movim/wiki/Install-Movim#2-database-configuration)).
     * PotgreSQL (**_strongly recommended_**)
     * MySQL 5.7 or higher with utf8mb4 encoding (necessary for emojis 😃 support) AND `utf8mb4_bin` collation.
     * MariaDB 10.2 or higher with utf8mb4 encoding (necessary for emojis 😃 support) AND `utf8mb4_bin` collation.
  * **PHP 7.4 minimum** with :
     * Curl (package ''**php-curl**'')
     * PHP mbstring (package ''**php-mbstring**'')
     * PHP ImageMagick and GD for the picture processing (package ''**php-imagick**'' and ''**php-gd**'')
     * Your database PHP driver (package ''**php-pgsql**'' or ''**php-mysql**'' depending on the type of database server you want to use).
     * And the PHP XML (package ''**php-xml**'')

### Debian/Ubuntu

    apt install composer php-fpm php-curl php-mbstring php-imagick php-gd php-pgsql php-xml

# General architecture

It's mandatory to understand the general architecture of the project to a certain extent before trying to deploy it.

When you use Movim, it acts as an intermediary between the user's browser and an XMPP server. All the data that is sent and received by these two parties are managed by the Movim core, some of them are also saved in a database (for cache purposes).

From the browser perspective, all communication with Movim is done using WebSockets (except for the "default" page loading). These sockets are proxied through your web-server to the Movim daemon. On the XMPP side Movim connects using pure TCP connections (like any XMPP client).

So all these streams will be managed by the Movim daemon. This daemon needs to be launched with the same user and rights as the web-server (most of the time using the ``www-data`` user).

# Installation

## Stable version

### Releases

You can simply follow the GIT tags or download a stable snapshot of this repository on our [Release page](https://github.com/movim/movim/releases). Then follow the steps from [Dependency installation](https://github.com/movim/movim/wiki/Install-Movim#dependency-installation).

### Docker

You can also deploy Movim using our official [Docker Compose repository](https://github.com/movim/movim_docker).

## Development version (repository)

The development version of Movim only comes with the core of the project. To install the dependencies you need to install Git to download the source codes from different repositories.
```bash
# Install Git so that Composer
# can clone the dependencies into your project
apt-get install git
```
### Downloading

Git is required to properly get the source code from the official repository. We recommend to execute the following commands with the `www-data` user (which is the common user for most of the GNU/Linux web servers).
```bash
cd /var/www/ # Server directory
sudo -s -u www-data # We use the web-server user
# We copy the source code from the repository
git clone https://github.com/movim/movim.git
```
### Dependency installation

Movim requires several dependencies to work properly. These libraries are managed using [Composer](https://getcomposer.org/).

#### If you already have composer

    cd movim
    composer install

#### If you have to install manually composer

You can install Composer in the Movim directory using the following command or by using your package manager:
```bash
curl -sS https://getcomposer.org/installer | php
```
Now you will be able to install the dependencies.
```bash
# Finally install your project's dependencies
php composer.phar install
```
### Update

You can also update your current Movim instance with the following lines (check anyway if the updates do not include any incompatibilities with your current version).
```bash
cd /var/www/movim/
git pull # To update the Movim source-code
composer install # To update the libraries
```
If the update comes with some database changes you can run the new migrations (see bellow).

# Deployment

This part of the tutorial can be followed for the stable and testing installation. They need to be applied **in the correct order**.

## 1. Rights check

Movim needs reading permissions on its root folder and recursively to be deployed properly. It will also try to create two folders:

  * **log/** for the PHP logs
  * **cache/** for the internal cache (templates and other system files)
  * **public/cache/** for the public caches (pictures, CSS, Javascript…)

You can create the folders in advance and it will skip this step, or you can let it by giving it writing permissions on its root folder:
```bash
# Use the root user to do the command
chown www-data movim && chown www-data movim/public && chmod u+rwx movim
```

You might have to replace the `www-data` user with the `apache` or `caddy` user in the last command regarding your OS or the web-server you have.

## 2. DotEnv configuration

Movim relies on [DotEnv](https://github.com/vlucas/phpdotenv) for its configuration.

To configure Movim copy the `.env.example` file in a new `.env` one and fill the different settings in it.

```bash
cp .env.example .env
nano .env
```

You can also set those settings using directly [environment variables like with Docker](https://docs.docker.com/compose/environment-variables/).

## 3. Database setup

Once the database is setup in the `.env` file create or update the database structure using [Composer](https://getcomposer.org/).
```bash
composer movim:migrate
```

## 4. Start the daemon

To let the browser communicate with the Movim server, you need to launch the daemon. It also needs to be launched using the web server user.
```bash
sudo -s -u www-data # If you are on Ubuntu
```
Then start the daemon using the parameters configured in the `.env` file.

```bash
cd /var/www/movim
php daemon.php start # Launch the daemon
```

If everything runs as expected you should see:

    Movim daemon launched
    Base URL : {public url of your pod}
    …

This daemon will be killed once your console is closed. Consider using `systemd` or `init` scripts to keep the daemon running in the foreground even after your disconnection. There are example startup files, like a `systemd` service file, in the [`etc/` directory](https://github.com/movim/movim/tree/master/etc).

## 5. Configure your web server

When you launch the daemon, it will generate the configuration to apply to your web server to "proxify" the WebSockets and display it in the console.

These configurations are dynamically generated to fit your current setup. Whether you use Apache or Nginx, both possible configuration will be displayed and will display even after you successfully applied them.

Here is an example of generated configuration:

### For Apache

> Enable the Proxy WebSocket module.

     # a2enmod proxy_wstunnel

> Add this in your configuration file (default-ssl.conf)

     ProxyPass /ws/ ws://localhost:8080/

Note that default-ssl.conf is the file to edit **if you enabled HTTPS**, otherwise you should edit 000-default.conf. Also remember to restart Apache to make sure your new configuration has been applied correctly.

### For Nginx

> Add the following configuration

     location /ws/ {
         proxy_pass http://127.0.0.1:8080/;
         proxy_http_version 1.1;
         proxy_set_header Upgrade $http_upgrade;
         proxy_set_header Connection "Upgrade";
         proxy_set_header Host $host;
         proxy_set_header X-Real-IP $remote_addr;
         proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
         proxy_set_header X-Forwarded-Proto https;
         proxy_redirect off;
     }

#### Picture proxy cache

Movim is automatically proxyfying the external pictures to protect its user IPs and prevent large pictures to be loaded without user consent.
This internal proxy is already asking the browser to cache the pictures for a few hours.

It is however **strongly recommended** to also setup a server side cache to prevent multiple users to request the same resource trough Movim.

To do so you can configure ```fastcgi_cache``` on nginx, [check the related documentation](http://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_cache).

Add this to your `nginx.conf` in the `http` section:

    http {
        …
        fastcgi_cache_path /tmp/nginx_cache levels=1:2 keys_zone=nginx_cache:100m inactive=60m;
        fastcgi_cache_key "$scheme$request_method$host$request_uri";
    }

Add the following configuration to your Movim virtual-host to enable the cache:

    location /picture {
        include fastcgi_params;

        add_header X-Cache $upstream_cache_status;
        fastcgi_ignore_headers "Cache-Control" "Expires" "Set-Cookie";
        fastcgi_cache nginx_cache;
        fastcgi_cache_key $request_method$host$request_uri;
        fastcgi_cache_valid any 7d;
    }

### For Caddy
You need of course to change the example file to your needs and usecase (like the php-fpm version would be different)

```Caddyfile
# Caddy automatically handles Certificates and updates the http connection to https
your.domain.tld {

    encode zstd gzip #Loseless compression

    # Paths that the server needs to serve
    @static path /stickers/* /cache/* /theme/* /scripts/*.js

    handle @static {
        root * /path-to/movim/public
        file_server
    }

    # All other request go throught fpm into the movim index.php
    handle {
        reverse_proxy unix//run/php/php7.4-fpm.sock {
            transport fastcgi {
                env SCRIPT_FILENAME /path-to/movim/public/index.php
            }
        }
    }

    # Pictures need an special php script to be served
    handle /picture/* {
        reverse_proxy unix//run/php/php7.4-fpm.sock {
            transport fastcgi {
                env SCRIPT_FILENAME /path-to/movim/public/picture/index.php
            }
        }
    }

    # The header is automatically updated in all request made under /ws/
    handle /ws/* {
        reverse_proxy localhost:8080
    }
}
```

For the rest of the configuration you can have a look at the [default configuration files that we provide](https://github.com/movim/movim/tree/master/etc).

#### Picture cache

For caddy you may want to take a look at xcaddy and compiling the server with the following modules: [cache-handler](https://github.com/caddyserver/cache-handler), [cdp-cache](https://github.com/sillygod/cdp-cache)

## 5. Admin panel

The admin panel is available directly from the Movim UI once an admin user is logged in.

To set a user admin login at least once (to register it in the database). You can then set him admin using the following command.

    php daemon.php setAdmin {jid}

The administrators will be listed on the login page of the instance.

Some of the configuration elements are only applied after the reboot of the daemon.
