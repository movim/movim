#!/bin/bash

SYSTEM_PATH=system
MOXL_REPO="lp:~edhelas/moxl/trunk"
DATAJAR_REPO="lp:~edhelas/datajar/datajar"
VERSION=`cat VERSION`
PACKAGENAME="movim-${VERSION}"

package() {
    # Exports the project's package with dependencies
    PACKAGEZIP="${PACKAGENAME}.zip"

    # OK, we export the code. $1 is the version number.
    bzr export $PACKAGENAME

    cd $PACKAGENAME
    moxl
    rm -rf "$SYSTEM_PATH/Moxl/.bzr"
    datajar
    rm -rf "$SYSTEM_PATH/Datajar2/.bzr"

    # Compressing
    cd ..
    zip --quiet -r $PACKAGEZIP $PACKAGENAME

    # Deleting useless folder
    rm -rf $PACKAGENAME

    # Signing, will create a $packagezip.sign file. Important stuff.
    gpg --armor --sign --detach-sign $PACKAGEZIP
}

moxl() {
	moxl_temp="Moxl"
    # Checking out Moxl.
    bzr branch $MOXL_REPO $moxl_temp
    rm -rf "$SYSTEM_PATH/Moxl"
    cp -r "$moxl_temp/" $SYSTEM_PATH
    rm -rf $moxl_temp
}

datajar() {
    datajar_temp="datajar"
    # Checking out Datajar.
    bzr branch $DATAJAR_REPO $datajar_temp
    rm -rf "$SYSTEM_PATH/Datajar2"
    cp -r "$datajar_temp" "$SYSTEM_PATH/Datajar2"
    rm -rf $datajar_temp
}

clean() {
    rm -rf "${SYSTEM_PATH}/Moxl"
    rm -rf "${SYSTEM_PATH}/Datajar2"
    rm -rf datajar
    rm -rf Moxl
}

# Doing the job
case $1 in
    "datajar")  datajar;;
    "moxl")  moxl;;
    "package")  package;;
    "clean")  clean;;
    *)  datajar
        moxl;;
esac
