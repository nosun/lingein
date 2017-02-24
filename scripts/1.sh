#!/bin/sh

mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"select distinct pid, type, sn, number, status, sell_price, list_price, wt from products" | while read pid type sn number status sell_price list_price wt
do
	if [ "$pid" != "pid" ]
	then
		#echo "$list_price"
		mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"update orders_items set sell_price=$sell_price, wt=$wt where pid = $pid"
	fi
done
