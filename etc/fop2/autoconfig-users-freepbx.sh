#!/bin/bash

if [ -e /etc/amportal.conf ]; then

DBNAME=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBNAME | cut -d= -f2 | tail -n1`
DBUSER=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBUSER | cut -d= -f2 | tail -n1`
DBPASSLINE=`cat /etc/amportal.conf | grep ^AMPDBPASS | tail -n1`
DBSTRIP=`echo $DBPASSLINE | cut -d= -f1`
DBPASS=`echo $DBPASSLINE | sed "s/$DBSTRIP=//g"`
DBHOST=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPDBHOST | cut -d= -f2 | tail -n1`
AMPEXTENSIONS=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPEXTENSION | cut -d= -f2 | tail -n1`
AMPDIR=`cat /etc/amportal.conf | sed 's/ //g' | grep ^AMPWEBROOT | cut -d= -f2 | tail -n1`
PLUGINDIR=$AMPDIR/admin/modules/fop2admin/plugins

hash_insert () {
local name=$1 key=$2 val=$3
eval __hash_${name}_${key}=$val
}
hash_find () {
local name=$1 key=$2
local var=__hash_${name}_${key}
MICLAVE=${!var}
}

eval `cat /etc/asterisk/voicemail.conf | grep -v "^\;" | grep "=>" | cut -d, -f1 | sed 's/ //g' | sed  's/\([^=]*\)=>\(.*\)/hash_insert claves "\1" "\2";/g'`

# For multi server setups, reading groups from remote servers
# eval `ssh root@10.0.0.1 /usr/local/fop2/autoconfig-users-freepbx.sh | grep group | sed 's/ /_/g' | sed  's/\([^=]*\)=\([^:]*\):\(.*\)/hash_insert grupos "\2" "\3";/g'`

# Verify if the fop2admin freepbx table exists
FOP2ADMIN=0
while read line; do
let FOP2ADMIN=FOP2ADMIN+1
done < <( mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SHOW tables FROM \`$DBNAME\` LIKE 'fop2users'" )

# Verify if the fop2 plugins table exists
FOP2PLUGIN=0
while read line; do
let FOP2PLUGIN=FOP2PLUGIN+1
done < <( mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SHOW tables FROM \`$DBNAME\` LIKE 'fop2plugins'" )


if [ "$FOP2PLUGIN" -gt 0 ]; then
# Query including plugins from latest fop2admin
MAINQUERY="set @@group_concat_max_len=32768; SELECT CONCAT('user=',fop2users.exten,':',if(secret='','EMPTYSECRET',secret),':',permissions,':',(select IF(group_concat(fop2groups.name) is NULL,'',group_concat(fop2groups.name)) from fop2UserGroup left outer join fop2groups on fop2groups.id=fop2UserGroup.id_group where fop2UserGroup.exten=fop2users.exten),':') as user, (select IF(group_concat(fop2plugins.rawname) is NULL,'',group_concat(fop2plugins.rawname)) from fop2UserPlugin left outer join fop2plugins on fop2plugins.rawname=fop2UserPlugin.id_plugin WHERE fop2UserPlugin.exten=fop2users.exten) as plg1,
(SELECT concat('aapa',IF(group_concat(rawname) is NULL,'',group_concat(rawname))) FROM fop2plugins WHERE global=1 limit 1) as plg2 FROM fop2users"
else
# Query without including plugins from latest fop2admin
MAINQUERY="set @@group_concat_max_len=32768; SELECT CONCAT('user=',fop2users.exten,':',if(secret='','EMPTYSECRET',secret),':',permissions,':',(select IF(group_concat(fop2groups.name) is NULL,'',group_concat(fop2groups.name)) from fop2UserGroup left outer join fop2groups on fop2groups.id=fop2UserGroup.id_group where fop2UserGroup.exten=fop2users.exten),':') FROM fop2users"
fi

if [ "$FOP2ADMIN" -gt 0 ]; then

mysql -NB --raw -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SELECT value FROM fop2settings" | while read LINEA
do
echo $LINEA
done

if [ -d $PLUGINDIR ]; then
for PDIR in $PLUGINDIR/*
do
if [ -d $PDIR ]; then
subdir=`basename ${PDIR}`
echo "plugin=$subdir:$PDIR"
fi
done
fi

mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "set @@group_concat_max_len=32768; SELECT CONCAT('perm=',fop2permissions.name,':',permissions,':',IF(isnull(GROUP_CONCAT(device)),'',GROUP_CONCAT(device))) FROM fop2permissions LEFT JOIN fop2PermGroup on fop2permissions.name=fop2PermGroup.name LEFT JOIN fop2GroupButton on fop2GroupButton.group_name=name_group left join fop2buttons on id_button=fop2buttons.id group by fop2permissions.name" | while read LINEA
do
echo $LINEA
done

mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "set @@group_concat_max_len=32768; SELECT CONCAT('group=',group_name,':',GROUP_CONCAT(device)) FROM fop2GroupButton LEFT JOIN fop2buttons on id_button=fop2buttons.id WHERE id_button IS NOT NULL GROUP BY group_name" |  while read LINEA
do
# For multi server setups, read group
# MYEXTEN=`echo $LINEA | cut -d= -f2 | cut -d:  -f1| sed 's/ /_/g'`
# hash_find grupos "${MYEXTEN}" 
# echo $LINEA,$MICLAVE
echo $LINEA
done

mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "$MAINQUERY" | while read LINEA
do
MYEXTEN=`echo $LINEA | cut -d: -f1 | cut -d\= -f2`
hash_find claves "${MYEXTEN}" 
echo -n $LINEA | sed 's/EMPTYSECRET/'${MICLAVE}'/g' | sed 's/ aapa/,/g' |  sed 's/: /:/g' | sed 's/:,/:/g' | sed 's/,$//g'
echo
done

echo "buttonfile=autobuttons.cfg"
else
#Generacion de usuarios sin freepbx plugin
for A in `cat /etc/asterisk/voicemail.conf | grep "=>" | cut -d, -f1 | sed 's/ => /:/g'`; do echo user=$A:all; done
echo "buttonfile=autobuttons.cfg"

fi
else
#Generacion de usuarios sin freepbx plugin
for A in `cat /etc/asterisk/voicemail.conf | grep "=>" | cut -d, -f1 | sed 's/ => /:/g'`; do echo user=$A:all; done
echo "buttonfile=autobuttons.cfg"

fi

