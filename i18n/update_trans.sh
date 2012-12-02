#!/bin/sh

ls ../*.php > files.list
ls ../admin/*.php >> files.list
ls ../system/*.php >> files.list
ls ../system/Moxl/*.php >> files.list
ls ../system/Moxl/*/*.php >> files.list
ls ../system/Moxl/*/*/*.php >> files.list
ls ../system/Moxl/*/*/*/*.php >> files.list
ls ../system/Controller/*.php >> files.list
ls ../system/Widget/widgets/*/*.php >> files.list

xgettext -kt -o messages.pot -L PHP -f files.list
