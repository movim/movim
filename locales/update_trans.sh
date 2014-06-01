#!/bin/sh

touch files.list

#find ../ -name '*.php' | grep -v 'cache' > files.list
#find ../ -name '*.tpl' | grep -v 'cache' >> files.list

find ../ -name 'locales.php' > files.list

xgettext -e --no-wrap -kt -o messages.pot -L PHP -f files.list 
