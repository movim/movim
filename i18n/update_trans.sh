#!/bin/sh

ls ../*.php > files.list
ls ../lib/*.php >> files.list
ls ../lib/widgets/*/*.php >> files.list

xgettext -kt -o messages.pot -L PHP -f files.list
