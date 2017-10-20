#!/bin/sh

# Copying and distribution of this file, with or without modification,
# are permitted in any medium without royalty provided this notice is
# preserved.  This file is offered as-is, without any warranty.
# Names of contributors must not be used to endorse or promote products
# derived from this file without specific prior written permission.


EXIT_FAILURE=1
exit_code=0

if command -v php > /dev/null
then
    for file_or_dir in "$@"
    do
	if test ! -e "$file_or_dir"
	then
	    >&2 echo "$file_or_dir does not exist!"
	    exit $EXIT_FAILURE
	else
	    files=$(find "$file_or_dir" -name '*.php')
	    for file in $files
	    do
		file_size=$(stat --format=%s "$file")
		if test "$file_size" != 0
		then
	            php --syntax-check "$file" > /dev/null
		    if test $? != 0
		    then
			>&2 echo "$file"
			exit_code=$EXIT_FAILURE
		    fi
		fi
	    done
	fi
    done
else
    >&2 echo 'There is no command "php" in the PATH'
    exit_code=$EXIT_FAILURE
fi

exit $exit_code
