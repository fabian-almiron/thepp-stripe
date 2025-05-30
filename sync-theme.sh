#!/bin/bash

HOST='192.168.1.2'
USER='fabi'
PASS='Server.1234!!'

LOCAL_DIR='/Users/mac/Local Sites/piped-peony/app/public/wp-content/themes/hello-theme-child-master'
REMOTE_DIR='.'

lftp -u $USER,$PASS $HOST <<EOF
set ssl:verify-certificate no
set ftp:passive-mode on
set xfer:clobber on
mirror -R --only-newer --exclude-glob vendor/ --exclude-glob venv/ "$LOCAL_DIR" "$REMOTE_DIR"
bye
EOF
