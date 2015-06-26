#!/bin/sh

ini2po ../ messages
msgcat -n messages/app/widgets/*/*.po messages/locales/locales.po -o messages.pot
sed -i 's/\\"//g' messages.pot
