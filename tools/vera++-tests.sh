#!/bin/sh

# Copying and distribution of this file, with or without modification,
# are permitted in any medium without royalty provided this notice is
# preserved.  This file is offered as-is, without any warranty.
# Names of contributors must not be used to endorse or promote products
# derived from this file without specific prior written permission.


# See https://bitbucket.org/verateam/vera/wiki/Home

EXIT_SUCCESS=0
EXIT_FAILURE=1

if ! command -v vera++ > /dev/null
then
    >&2 echo 'There is no command "vera++" in the PATH'
    exit EXIT_FAILURE
fi

cmd='vera++ --error -s'
cmd="$cmd -R F001" # source files should not use the '\r' (CR) character
cmd="$cmd -R L004 -P max-line-length=350"
cmd="$cmd -R L005 -P max-consecutive-empty-lines=2"
cmd="$cmd -R L006 -P max-file-length=850"

exit_code=$EXIT_SUCCESS
for file_or_dir in "$@"
do
    if test ! -e "$file_or_dir"
    then
	>&2 echo "$file_or_dir does not exist!"
	exit_code=$EXIT_FAILURE
    else
	files=$(find "$file_or_dir" \( -name '*.php' \))
	echo "$files" | $cmd
	if test $? -ne $EXIT_SUCCESS
	then
	    exit_code=$EXIT_FAILURE
	fi
    fi
done
exit $exit_code
