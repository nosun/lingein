#!/bin/sh

mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"select uid, email from users" | while read uid email
do
	if [ "$uid" != "uid" ]
	then
		mysql -hlocalhost -uroot -pJZH@mingDA123 lingerieinterest -e"update orders set uid = $uid where delivery_email=\"$email\""
	fi
done
