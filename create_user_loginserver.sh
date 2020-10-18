#!/bin/bash

DEFAULT_USER_PASSWORD="123456"

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 
   exit 1
fi

if [[ $# -ne 1 ]]; then
   echo "This script requires user name as parameter" 
   exit 1
fi
USER_NAME=$1

re='^[[:lower:]_][[:lower:][:digit:]_-]{2,30}$'

if [[ ! ${USER_NAME} =~ $re ]]; then
    echo "Invalid User name"
    exit 1
fi

id -u ${USER_NAME}
if [ $? -ne 0 ] ; then
	adduser --disabled-password --gecos "" -q ${USER_NAME}
fi

printf "${USER_NAME}:%s" "${DEFAULT_USER_PASSWORD}" | chpasswd

chmod 700 /home/${USER_NAME}
mkdir -p /home/${USER_NAME}/dgx-data
mkdir -p /home/${USER_NAME}/.ssh
chmod 700 /home/${USER_NAME}/.ssh
if [ ! -f /home/${USER_NAME}/.ssh/id_rsa ]; then
	ssh-keygen -t rsa -N '' -f /home/${USER_NAME}/.ssh/id_rsa
fi
chown -R ${USER_NAME}.${USER_NAME} /home/${USER_NAME}/.ssh
scp /home/${USER_NAME}/.ssh/id_rsa.pub runner@dgx1-host:~/keys/${USER_NAME}.pub


sed -i -e "/.*shared\/${USER_NAME}.*/d" /etc/fstab
echo "dgx1-host:/home/runner/shared/${USER_NAME} /home/${USER_NAME}/dgx-data nfs4" >> /etc/fstab

echo "Now go to root shell on DGX server and run create_user_dgx.sh script."
echo "execute \"mount -a\" command after you finish script on DGX server"
