#!/bin/bash

# Should we only update a language?
if [ $# -eq 1 ]
then
    echo "Updating language ${1}"
    msgmerge --no-wrap -o "${1}.po" "${1}.po" messages.pot
else
    echo "Updating all tranlations"
    for lang in *.po
    do
        echo ${lang%.po}
        msgmerge --no-wrap -o "$lang" "$lang" messages.pot
    done
fi
