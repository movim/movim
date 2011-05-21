#!/bin/bash

JAXL_REPO=git://gitorious.org/titine/jaxl-titine.git
JAXL_PATH=system/Jaxl/

if [ -d $JAXL_PATH ]; then
    rm -rf $JAXL_PATH
fi

# Checking out jaxl.
git clone $JAXL_REPO $JAXL_PATH
