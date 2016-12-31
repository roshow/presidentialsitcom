#!/bin/sh

sh ./build-php.sh

ftp -n ftp.roshow.net << EOF
quote USER $FTP_USER
quote PASS $FTP_PASS
cd presidentialsitcom
put index.php
quit
EOF