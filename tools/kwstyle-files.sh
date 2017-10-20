#!/bin/sh

# Copying and distribution of this file, with or without modification,
# are permitted in any medium without royalty provided this notice is
# preserved.  This file is offered as-is, without any warranty.
# Names of contributors must not be used to endorse or promote products
# derived from this file without specific prior written permission.


EXIT_FAILURE=1
exit_code=0
cmd='KWStyle -v -gcc'

for file_or_dir in "$@"
do
    if test ! -e "$file_or_dir"
    then
	>&2 echo "$file_or_dir does not exist!"
	exit $EXIT_FAILURE
    else
	files=$(find "$file_or_dir" -name '*' \
		     -and -not -name '*.jpg' \
		     -and -not -name '*.jpeg' \
		     -and -not -name '*.png' \
		     -and -not -name '*.gif' \
		     -and -not -name '*.webp' \
		     -and -not -name '*.ogg' \
		     -and -not -name '*.oga' \
		     -and -not -name '*.ogv' \
		     -and -not -name '*.webm' \
		     -and -not -name '*.mp4' \
		     -and -not -name '*.mp3' \
		     -and -not -name '*.flac' \
		     -and -not -name '*.opus')
	for file in $files
	do
	    if test ! -d "$file"
	    then
		$cmd "$file"
		if test $? != 0
		then
		    >&2 echo "$file"
	            exit_code=$EXIT_FAILURE
		fi
	    fi
	done
    fi
done

exit $exit_code
