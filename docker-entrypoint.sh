#!/usr/bin/env bash
set -euo pipefail

if ! [ -e daemon.php -a -e public/index.php ]; then
	echo >&2 "Movim not found in $PWD - copying now..."
	if [ "$(ls -A)" ]; then
		echo >&2 "WARNING: $PWD is not empty - press Ctrl+C now if this is an error!"
		( set -x; ls -A; sleep 10 )
	fi
	tar cf - --one-file-system -C /usr/src/movim-${MOVIM_VERSION} . | tar xf -
	echo >&2 "Complete! Movim ${MOVIM_VERSION} has been successfully copied to $PWD"
fi

chown -R www-data:www-data $PWD && chmod -R u+rwx $PWD

php vendor/bin/phinx migrate
php-fpm --daemonize

sleep 5
exec "$@"
