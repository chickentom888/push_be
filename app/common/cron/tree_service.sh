#!/bin/bash

cd /home/funfive/web/id.fun5exchange.com/public_html/app

while true
do
    echo START
    php cli.php user tree
    sleep 2
done
