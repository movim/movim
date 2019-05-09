#!/bin/mksh
#-
# Copyright © 2018
#    mirabilos <thorsten.glaser@teckids.org>
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
#-
# Needs the Debian packages mksh and unicode-data installed.

cd "$(dirname "$0")"
srcpath=../../../public/theme/img/emojis/svg

cd "$srcpath"
set -A files -- *.svg
cd "$OLDPWD"

cat >CompiledEmoji.php <<\EOF
<?php

/* GENERATED FILE, DO NOT EDIT! */

return [
EOF

php >>CompiledEmoji.php |&
print -pr -- '<?php
    $u = array();'
typeset -l codepoint
while IFS=';' read codepoint name rest; do
    [[ $name = *\<* ]] && continue
    print -pr -- "\$u['${codepoint##*(0)}'] = '$name';"
done </usr/share/unicode/UnicodeData.txt
print -pr -- '
    function lookup($name) {
        global $u;
        $x = "";
        $s = "";
        foreach (explode("-", $name) as $cp) {
            if (isset($u[$cp]))
                $x .= $s . $u[$cp];
            else
                $x .= $x . sprintf("<U%04X>", hexdec($cp));
            $s = " + ";
        }
        return $x;
    }
    function handle($name) {
        printf("    \"%s\" => \"%s\",\n", $name, lookup($name));
    }
'
for ff in "${files[@]}"; do
    f=${ff%.svg}
    if [[ $f != +([0-9a-f-]) ]]; then
        print -ru2 -- "W: source file $ff does not match pattern!"
        continue
    fi
    print -pr -- "handle('$f');"
done
exec 3>&p
exec 3>&-
wait
echo "];" >>CompiledEmoji.php
