#!/bin/bash

LIB_PATH=lib
MOXL_REPO="lp:moxl"
MODL_REPO="lp:modl"
VERSION=`cat VERSION`
PACKAGENAME="movim-${VERSION}"

package() {
    # Exports the project's package with dependencies
    PACKAGEZIP="${PACKAGENAME}.zip"

    # OK, we export the code. $1 is the version number.
    bzr export $PACKAGENAME

    cd $PACKAGENAME
    moxl
    rm -rf "$LIB_PATH/Moxl/.bzr"
    modl
    rm -rf "$LIB_PATH/Modl/.bzr"

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
    rm -rf "$LIB_PATH/Moxl"
    cp -r "$moxl_temp/" $LIB_PATH
    rm -rf $moxl_temp
}

modl() {
	modl_temp="Modl"
    # Checking out Modl.
    bzr branch $MODL_REPO $modl_temp
    rm -rf "$LIB_PATH/Modl"
    cp -r "$modl_temp/" $LIB_PATH
    rm -rf $modl_temp
}

clean() {
    rm -rf "${LIB_PATH}/Moxl"
    rm -rf "${LIB_PATH}/Modl"
    rm -rf Modl
    rm -rf Moxl
}

# Doing the job
case $1 in
    "modl")  modl;;
    "moxl")  moxl;;
    "package")  package;;
    "clean")  clean;;
    *)  modl
        moxl;;
esac
