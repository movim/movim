#!/bin/bash

VERSION=`cat VERSION`
PACKAGENAME="movim-${VERSION}"

package() {
    # Exports the project's package with dependencies
    PACKAGEZIP="${PACKAGENAME}.zip"

    # OK, we export the code. $1 is the version number.
    bzr export $PACKAGENAME

    cd $PACKAGENAME
    
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

    rm composer.*

    # Compressing
    cd ..
    zip --quiet -r $PACKAGEZIP $PACKAGENAME

    # Deleting useless folder
    rm -rf $PACKAGENAME

    # Signing, will create a $packagezip.sign file. Important stuff.
    gpg --armor --sign --detach-sign $PACKAGEZIP
}

# Doing the job
case $1 in
    "package")  package;;
esac
