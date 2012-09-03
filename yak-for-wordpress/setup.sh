#! /bin/sh

. test/setenv.sh

if [ "${MYSQLPASS}" != "" ]; then
    export PASS=-p${MYSQLPASS}
else
    export PASS=""
fi

mysql -u${MYSQLUSER} $PASS wptest < test/create.sql
mysql -udba -pdba wptest < test/wptest.sql

