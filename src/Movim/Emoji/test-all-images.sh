#!/bin/mksh
#-
# Copyright © 2018, 2019
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
srcpath=../../../public/theme/img/emojis/svg
saveIFS=$IFS

cd "$srcpath"
allfiles=1
set -A files -- *.svg
cd "$OLDPWD"

if [[ -n $1 ]]; then
    allfiles=0
    set -A files -- "$@"
    print -ru2 -- "W: only testing $# files from command line"
fi

for f in "${files[@]}" -; do
    if [[ $f = - ]]; then
        if (( !allfiles )); then
            # skip if only testing files from command line
            print -r -- 'print "-\n~\n";'
            break
        fi
        cat <<\EOF
            print "-\n";
            no warnings 'nonchar';
            my $i = 0x0020 - 1;
            while (++$i <= 0x10FFFF) {
                # skip (relevant) non-characters
                next if ($i & 0xFFFE) == 0xFFFE;
                # skip surrogates
                $i = 0xE000 if $i == 0xD800;
                # output characters not used by the SVGs
                printf("<%04X>=%c\n", $i, $i) unless exists($h{$i});
            }
            print "~\n";
EOF
        break
    fi
    x=${f%.svg}
    IFS=-
    set -A y -- $x
    IFS=$saveIFS
    s='print "'
    for z in "${y[@]}"; do
        s+="\\x{$z}"
    done
    print -r -- "$s\\n\"; \$h{0x${y[0]}}=1;"
done | perl -C7 | php replace-test.php |&
n=-1
rv=0
match=0
mis=0
cpnt=0
cnot=0
function out {
    print -ru2 -- "I: $match/${#files[*]} matched, $mis mismatched"
    (( allfiles )) && print -ru2 -- "I: $cnot/$cpnt codepoints (expectedly) did not match, $((cpnt-cnot)) mistakenly matched"
    (( rv & 1 )) && print -ru2 -- "E: some files did not match"
    (( rv & 2 )) && print -ru2 -- "E: incomplete processing of first half"
    (( rv & 4 )) && print -ru2 -- "E: some codepoints mistakenly matched"
    (( rv & 8 )) && print -ru2 -- "E: incomplete processing of second half"
    (( rv )) || if (( allfiles )); then
        print -ru2 -- "I: all files matched, all others didn’t ⇒ all OK"
    else
        print -ru2 -- "I: all files to be tested matched"
    fi
    exit $rv
}
print -nu2 -- "N: processing SVGs…\\r"
while IFS= read -pr line; do
    [[ $line = '-' ]] && break
    if [[ $line = '<img'*"/svg/${files[++n]}\""*\> ]]; then
        let ++match
        continue
    fi
    print -ru2 -- "W: file ${files[n]} not matched"
    (( allfiles )) || print -ru2 -- "N: line: $line"
    (( rv |= 1 ))
    let ++mis
done
if [[ $line != '-' ]]; then
    (( rv |= 2 ))
    out
fi
i=0
while IFS= read -pr line; do
    if [[ $line = '~' ]]; then
        read -pr x && print -ru2 -- "E: data after end of input: $x"
        break
    fi
    let ++cpnt
    (( i++ & 4095 )) || print -nu2 -- "N: processing ${line%%=*}…\\r"
    if [[ $line = *'<img'* ]]; then
        print -ru2 -- "W: mistaken match for codepoint $line"
    else
        let ++cnot
    fi
done
if [[ $line != '~' ]]; then
    (( rv |= 8 ))
    out
fi
out
