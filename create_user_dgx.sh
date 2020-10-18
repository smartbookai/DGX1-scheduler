#!/bin/bash

IP_ADDRESS_OF_NFS_SHARE=192.168.0.1
RUNNER_USER_ID=1001

if [[ $# -ne 1 ]]; then
   echo "[$(date +'%F %T')] This script requires user name as parameter" 
   exit 1
fi
USER_NAME=$1

if [[ $EUID -ne 0 ]]; then
   echo "[$(date +'%F %T')] This script must be run as root" 
   exit 1
fi

chown root.root /home/runner/keys/${USER_NAME}.pub
chmod 600 /home/runner/keys/${USER_NAME}.pub
mkdir -p /home/runner/shared/${USER_NAME}
chown runner.runner /home/runner/shared/${USER_NAME}
sed -i -e "/.*shared\/${USER_NAME}.*/d" /etc/exports
echo "/home/runner/shared/${USER_NAME}      ${IP_ADDRESS_OF_NFS_SHARE}(rw,sync,no_subtree_check,all_squash,anonuid=${RUNNER_USER_ID},anongid=${RUNNER_USER_ID})" >> /etc/exports
exportfs -a
systemctl restart nfs-kernel-server
