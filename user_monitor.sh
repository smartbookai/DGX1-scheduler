#!/bin/bash

cd /home/runner/keys
ls -1 *.pub | \
while read key; do
    user=${key::-4}
    #echo "found user ${user}"
    grep -q ${user} /etc/exports
    if [ $? -ne 0 ]; then
        echo "[$(date +'%F %T')] prepare environment for new user ${user}"
        /home/runner/bin/create_user_dgx.sh ${user} 2>&1 >> /home/runner/logs/create_user.log
    fi
done
cd -

