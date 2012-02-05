#!/bin/bash

# Exports the project's package with dependencies

if [ $# -lt 1 ]
then
    echo "Needs the version as argument."
    exit 1
fi

# OK, we export the code. $1 is the version number.

packagedir="movim-$1"
packagezip="movim-$1.zip"
bzr export $packagedir

cd $packagedir
./jaxl.sh
rm -rf system/Jaxl/.git

# Compressing
cd ..
zip -r $packagezip $packagedir

# Signing, will create a $packagezip.sign file. Important stuff.
gpg --armor --sign --detach-sign $packagezip

# That's all.
