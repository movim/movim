#!/bin/sh

ini2po ../ messages --exclude info.ini
msgcat -n messages/app/Widgets/*/*.po messages/locales/locales.po -o messages.pot
sed -i 's/\\"//g' messages.pot
