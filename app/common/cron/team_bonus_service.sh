#!/bin/bash

cd /home/funfive/web/id.fun5exchange.com/public_html/app

DATE=$(date +"%Y-%m-%d_%H.%M.%S")
echo ${DATE}

echo User Team Information
php cli.php user calc

echo Investment Pay Daily
php cli.php bonus tra_lai_ngay

echo Team Bonus
php cli.php bonus team
