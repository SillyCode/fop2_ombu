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
AMPDIR=$AMPDIR/admin/modules/framework/module.xml

AMPVERSION=`cat $AMPDIR | grep "<version>" | head -n 1 | sed -e 's/<[^>]*>//g' | cut -d\. -f 1,2 | sed 's/\s//g' | sed 's/\.//g'`

if [ $AMPVERSION -lt 26 ]; then
QUEUECONTEXT="from-internal"
else
QUEUECONTEXT="from-queue"
fi

hash_insert () {
local name=$1 key=$2 val=$3
eval __hash_${name}_${key}=$val
}

hash_get () {
local name=$1 key=$2
eval "v=__hash_${name}_${key}"
eval "$3=\$$v"
}

# Verify if the fop2 plugin table exists
FOP2PLUGIN=0 
while read line; do
let FOP2PLUGIN=FOP2PLUGIN+1
done < <( mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SHOW tables FROM \`$DBNAME\` LIKE 'fop2users'" )

VMPREFIX=`mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SELECT value FROM globals WHERE variable='VM_PREFIX'"`

if [ "$VMPREFIX" = "" ]; then
VMPREFIX=`mysql -NB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "SELECT IFNULL(customcode,defaultcode) AS code FROM featurecodes WHERE modulename='voicemail' AND featurename='directdialvoicemail'"`
fi

if [ "$FOP2PLUGIN" -gt 0 ]; then
#Configuration from FreePBX plugin


# Fields we want to query
WANTED_FIELDS="queuechannel originatechannel customastdb spyoptions external tags";

eval `mysql -E -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "DESC fop2buttons" | grep Field | sed 's/Field: //g' | while read LINEA; do echo "hash_insert existentFields $LINEA '1';"; done;`

EXISTING_FIELDS=""
for A in $WANTED_FIELDS
do
hash_get existentFields $A tiene
if [ "$tiene" = "1" ]; then
EXISTING_FIELDS="${EXISTING_FIELDS}$A,"
fi
done

FINAL_FIELDS=${EXISTING_FIELDS%?};

hash_get existentFields sortorder tiene
if [ "$tiene" = "1" ]; then
    ORDER="type,sortorder,(exten+0)"
else
    ORDER="type,(exten+0)"
fi


if [ "${AMPEXTENSIONS}" != "deviceanduser" ]; then
#si no tiene device and user, exclusimos los USER/
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e \
    "SET NAMES utf8; SELECT device AS channel,type,if(type<>'trunk',exten,'') AS extension,\
    label,IF(users.voicemail<>'novm',concat(users.extension,'@',users.voicemail),'') as mailbox, \
    context,'$QUEUECONTEXT' as queuecontext,IF(mailbox<>'',concat('$VMPREFIX',mailbox),'NULL') AS extenvoicemail, \
    privacy,\`group\`,IF(type='trunk',IF(email<>'',concat('splitme-',email),''),email) as email, \
    channel as extrachannel, \
    IF(channel='RTMP',1,0) as rtmp, $FINAL_FIELDS \
    FROM fop2buttons \
    LEFT JOIN users on exten=users.extension \
    WHERE device NOT LIKE 'USER/%' AND device<>'' AND exclude=0 \
    ORDER BY $ORDER" | sed '/\*\*/d' | sed 's/: /=/g' | sed '/.*=$/d' | while read LINEA
do
echo $LINEA | sed '/NULL/d' | sed 's/^channel=\(.*\)/\n[\1]/g' | sed 's/^extrachannel/channel/g'
echo $LINEA | grep -qi "^email=splitme"
if [ $? = 0 ]; then
RANGE=`echo $LINEA | sed 's/^email=splitme-//g' | sed 's/-/ /g'`
for ZAPNUM in `seq $RANGE`
do
echo "channel=DAHDI/$ZAPNUM"
echo "channel=DAHDI/i$ZAPNUM"
echo "channel=ZAP/$ZAPNUM"
done
fi
done

else

FINAL_FIELDS=`echo $FINAL_FIELDS | sed -e "s/originatechannel/IF(originatechannel='' AND type='extension', CONCAT('Local\/', exten, '@from-internal'), originatechannel) AS originatechannel/g"`

mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e \
    "SET NAMES utf8; SELECT if(type='extension',CONCAT('USER/',exten),device) AS channel,type,if(type<>'trunk',exten,' ') AS extension,\
    label,IF(users.voicemail<>'novm',concat(users.extension,'@',users.voicemail),'') as mailbox, \
    context,'$QUEUECONTEXT' as queuecontext,concat('$VMPREFIX',mailbox) AS extenvoicemail, \
    privacy,\`group\`,IF(type='trunk',IF(email<>'',concat('splitme-',email),''),email) as email, \
    $FINAL_FIELDS FROM fop2buttons \
    LEFT JOIN users on exten=users.extension \
    WHERE device<>'' AND exclude=0 ORDER BY $ORDER" | \
    sed '/\*\*/d' | sed 's/: /=/g' | sed '/.*=$/d' | while read LINEA
do
echo $LINEA | sed '/NULL/d' | sed 's/^channel=\(.*\)/\n[\1]/g' | sed 's/^extrachannel/channel/g'
echo $LINEA | grep -qi "^email=splitme"
if [ $? = 0 ]; then
RANGE=`echo $LINEA | sed 's/^email=splitme-//g' | sed 's/-/ /g'`
for ZAPNUM in `seq $RANGE`
do
echo "channel=DAHDI/$ZAPNUM"
echo "channel=DAHDI/i$ZAPNUM"
echo "channel=ZAP/$ZAPNUM"
done
fi
done
fi

#PARKSLOT=`/usr/sbin/asterisk -rx "dialplan show parkedcalls" | grep "=>" | cut -d= -f1 | sed s/\'//g | sed 's/ //g'`
#if [ "X${PARKSLOT}" != "X" ]; then
#echo
#echo "[PARK/default]"
#echo "extension=${PARKSLOT}"
#echo "context=parkedcalls"
#echo "type=park"
#echo "Label=Park ${PARKSLOT}"
#echo
#fi


else
#Configuration from FreePBX without plugin

if [ "${AMPEXTENSIONS}" != "deviceanduser" ]; then
# SIP EXTENSIONS
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('SIP/',extension) as channel,extension,name as label,s1.data as mailbox,s2.data as context,'$QUEUECONTEXT' as queuecontext,concat('$VMPREFIX',s1.data) as extenvoicemail from users as u left join sip as s1 on u.extension=s1.id and s1.keyword='mailbox' left join sip as s2 on u.extension=s2.id where s2.keyword='context' order by extension+0" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/channel=\(.*\)/\n[\1]\ntype=extension/g'
done

# IAX2 EXTENSIONS
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('IAX2/',extension) as channel,extension,name as label,s1.data as mailbox,s2.data as context,'$QUEUECONTEXT' as queuecontext,concat('$VMPREFIX',s1.data) as extenvoicemail from users as u left join iax as s1 on u.extension=s1.id and s1.keyword='mailbox' left join iax as s2 on u.extension=s2.id where s2.keyword='context' order by extension+0" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/channel=\(.*\)/\n[\1]\ntype=extension/g'
done

else

# FREEPBX DEVICEANDUSER
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('USER/',extension) as channel, extension, name as label, concat(extension,'@',voicemail) as mailbox, 'from-internal' as context, '$QUEUECONTEXT' as queuecontext,concat('$VMPREFIX',extension,'@from-internal') as extenvoicemail from users order by extension+0" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/channel=\(.*\)/\n[\1]\ntype=extension\n/g'
done

fi

# SIP TRUNKS
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('SIP/',s1.data) as trunk from sip left join sip as s1 on sip.id=s1.id and s1.keyword='account' where sip.keyword='host' and sip.data<>'dynamic'" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/trunk=\(.*\)/\n[\1]\ntype=trunk\nlabel=\1/g'
done

# IAX2 TRUNKS
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select concat('IAX2/',s1.data) as trunk from iax left join iax as s1 on iax.id=s1.id and s1.keyword='account' where iax.keyword='host' and iax.data<>'dynamic'" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/trunk=\(.*\)/\n[\1]\ntype=trunk\nlabel=\1/g'
done

#mysql -ENB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select extension,context,descr as label from extensions where application='Queue';" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select extension,'ext-queues',descr as label from queues_config order by extension+0" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/extension=\(.*\)/\n[QUEUE\/\1]\ntype=queue\nextension=\1\ncontext=ext-queues/g'
done

mysql -EB -u $DBUSER -p$DBPASS -h $DBHOST $DBNAME -e "select exten as extension,'ext-meetme' as context,description as label from meetme" | sed '/\*\*/d' | sed 's/: /=/g' | while read LINEA
do
echo $LINEA | sed 's/extension=\(.*\)/\n[CONFERENCE\/\1]\ntype=conference\nextension=\1/g'
done


DAHDI=`/usr/sbin/asterisk -rx "zap show channels" | grep -v from-internal | grep -v pseudo | grep -v Language | awk '{print $1}' | head -n 1` 
if [ "X${DAHDI}" != "X" ]; then
echo
echo "[DAHDI/$DAHDI]"
echo "type=trunk"
echo "label=DAHDI"

for LIN in `/usr/sbin/asterisk -rx "zap show channels" | grep -v from-internal | grep from | awk '{print $1}'`
do
echo "channel=ZAP/$LIN";
done
for LIN in `/usr/sbin/asterisk -rx "dahdi show channels" | grep -v from-internal | grep from | awk '{print $1}'`
do
echo "channel=DAHDI/$LIN";
echo "channel=DAHDI/i$LIN";
done

fi

PARKSLOT=`/usr/sbin/asterisk -rx "dialplan show parkedcalls" | grep -a "=>" | cut -d= -f1 | sed s/\'//g | sed 's/ //g'`
if [ "X${PARKSLOT}" != "X" ]; then
echo
echo "[PARK/default]"
echo "extension=${PARKSLOT}"
echo "context=parkedcalls"
echo "type=park"
echo "Label=Park ${PARKSLOT}"
echo
fi

fi

else
echo "Unable to find /etc/amportal.conf"
fi

echo 
if [ -f /usr/local/fop2/buttons_custom.cfg ]; then
cat /usr/local/fop2/buttons_custom.cfg
fi
