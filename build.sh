#!/bin/bash

SYSTEM_PATH=system
JAXL_REPO=git://gitorious.org/titine/jaxl-titine.git
JAXL_PATH="${SYSTEM_PATH}/Jaxl/"
DATAJAR_REPO=lp:datajar/trunk/
VERSION=`cat VERSION`
PACKAGENAME="movim-${VERSION}"

package() {
    # Exports the project's package with dependencies
    PACKAGEZIP="${PACKAGENAME}.zip"

    # OK, we export the code. $1 is the version number.
    bzr export $PACKAGENAME

    cd $PACKAGENAME
    jaxl
    rm -rf "$JAXL_PATH/.git"
    datajar
    rm -rf "$DATAJAR_PATH/.bzr"

    # Compressing
    cd ..
    zip --quiet -r $PACKAGEZIP $PACKAGENAME

    # Deleting useless folder
    rm -rf $PACKAGENAME

    # Signing, will create a $packagezip.sign file. Important stuff.
    gpg --armor --sign --detach-sign $PACKAGEZIP
}

jaxl() {
    if [ -d $JAXL_PATH ]
    then
        if [ -d "$JAXL_PATH/.git" ]
        then
            cd $JAXL_PATH
            git pull
            cd ..
        else
            rm -rf $JAXL_PATH
            # Checking out jaxl.
            git clone $JAXL_REPO $JAXL_PATH
        fi
    else
        # Checking out jaxl.
        git clone $JAXL_REPO $JAXL_PATH
    fi
}

datajar() {
    datajar_temp="datajar"
    # Checking out Datajar.
    bzr branch $DATAJAR_REPO $datajar_temp
    rm -rf "$SYSTEM_PATH/Datajar"
    cp -r "$datajar_temp/Datajar" $SYSTEM_PATH
    rm -rf $datajar_temp
}

clean() {
    rm -rf $JAXL_PATH
    rm -rf "${SYSTEM_PATH}/Datajar"
    rm -rf datajar
}

# Doing the job
case $1 in
    "datajar")  datajar;;
    "jaxl")  jaxl;;
    "package")  package;;
    "clean")  clean;;
    *)  datajar
        jaxl;;
esac
