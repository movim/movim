#!/bin/mksh
#-
# Copyright © 2018
#	mirabilos <thorsten.glaser@teckids.org>
#
# Provided that these terms and disclaimer and all copyright notices
# are retained or reproduced in an accompanying document, permission
# is granted to deal in this work without restriction, including un‐
# limited rights to use, publicly perform, distribute, sell, modify,
# merge, give away, or sublicence.
#
# This work is provided “AS IS” and WITHOUT WARRANTY of any kind, to
# the utmost extent permitted by applicable law, neither express nor
# implied; without malicious intent or gross negligence. In no event
# may a licensor, author or contributor be held liable for indirect,
# direct, other damage, loss, or other issues arising in any way out
# of dealing in the work, even if advised of the possibility of such
# damage or existence of a defect, except proven that it results out
# of said person’s immediate fault when using the work as intended.

cd "$(dirname "$0")"
srcpath=../../../theme/img/emojis/svg
saveIFS=$IFS

cd "$srcpath"
set -A files -- *.svg
cd "$OLDPWD"

if [[ -n $1 ]]; then
    set -A files -- "$@"
    print -ru2 -- "W: only testing $# files from command line"
fi

for f in "${files[@]}"; do
    x=${f%.svg}
    IFS=-
    set -A y -- $x
    IFS=$saveIFS
    s='print "'
    for z in "${y[@]}"; do
        s+="\\x{$z}"
    done
    print -r -- "$s\\n\";"
done | perl -C7 | php replace-test.php |&
n=-1
rv=0
match=0
mis=0
while IFS= read -pr line; do
    if [[ $line = '<img'*"/svg/${files[++n]}\""*\> ]]; then
        let ++match
        continue
    fi
    print -ru2 -- "W: file ${files[n]} not matched"
    [[ -n $1 ]] && print -ru2 -- "N: line: $line"
    rv=1
    let ++mis
done
(( rv )) || print -ru2 -- "I: all files matched"
print -ru2 -- "I: $match/${#files[*]} matched, $mis mismatched"
exit $rv
