#!/bin/bash
DIR=/var/www/html/movim
USER1=www-data
USER2=chunk
cd $DIR/..
sudo chown -R $USER1:$USER2 movim/
cd $DIR
sudo find $DIR -type f -exec chmod 664 {} \;
sudo find $DIR -type d -exec chmod 775 {} \;
sudo chmod +x set-permissions.sh
echo ""
echo "Success!"
echo ""
