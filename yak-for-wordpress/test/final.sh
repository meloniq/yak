#! /bin/sh

. ./setenv.sh

if [ "${MYSQLPASS}" != "" ]; then
    export PASS=-p${MYSQLPASS}
else
    export PASS=""
fi

mysql -u${MYSQLUSER} $PASS wptest < create.sql
mysql -udba -pdba wptest < wpbackup.sql

