#!/bin/sh

mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"select oid, number from orders" | while read oid number
do
	if [ "$oid" != "oid" ]
	then
		mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"update orders_items set oid = $oid where name=\"$number\""
	fi
done
