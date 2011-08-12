#!/bin/bash

JAXL_REPO=git://gitorious.org/titine/jaxl-titine.git
JAXL_PATH=system/Jaxl/

if [ -d $JAXL_PATH ]
then
    if [ -d "$JAXL_PATH/.git" ]
    then
        cd $JAXL_PATH
        git pull
    else
        rm -rf $JAXL_PATH
        # Checking out jaxl.
        git clone $JAXL_REPO $JAXL_PATH
    fi
else
    # Checking out jaxl.
    git clone $JAXL_REPO $JAXL_PATH
fi

