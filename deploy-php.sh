#!/bin/sh

sh ./build-php.sh

HOST='ftp.roshow.net'
FILE='index.php'

ftp -n $HOST<< EOF
quote USER $FTP_USER
quote PASS $FTP_PASS
cd presidentialsitcom
put $FILE 
quit
EOF