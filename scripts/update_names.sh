#!/bin/sh

mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"select distinct pid from products" | while read pid
do
	if [ "$pid" != "pid" ]
	then
		echo "$pid"
		mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"update orders_items set name=(select name from products where pid=$pid) where pid=$pid"
	fi
done
