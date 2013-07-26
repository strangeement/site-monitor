#!/bin/sh

site=all

if [ $# -eq 1 ]; then
	site=$1
fi

php compress.php $site `expr 60 \* 60 \* 6` `expr 60 \* 60 \* 48`
php compress.php $site `expr 60 \* 60` `expr 60 \* 60 \* 6`
php compress.php $site `expr 60 \* 5` `expr 60 \* 60`