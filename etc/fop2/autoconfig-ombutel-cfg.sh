#!/bin/sh

manager_cfg_file=/etc/asterisk/ombutel/manager__60-fop2_secret.conf
manager_secret=`awk -F ' *= *' '/^secret/ {print $2}' $manager_cfg_file`
cat <<EOF
manager_host=localhost
manager_port=5038
manager_user=fop2
manager_secret=$manager_secret

web_dir=/usr/share/fop2/www



poll_interval      = 86400
poll_voicemail     = 1
monitor_ipaddress  = 0
blind_transfer     = 0
supervised_transfer = 1
spy_options="bq"
whisper_options="w"
monitor_filename=/var/spool/asterisk/monitor/\${ORIG_EXTENSION}_\${DEST_EXTENSION}_%h%i%s_\${UNIQUEID}_\${FOP2CONTEXT}
monitor_format=wav
monitor_mix=true
monitor_exec/usr/share/fop2/scripts/recording_fop2.pl
voicemail_path=/var/spool/asterisk/voicemail
ssl_certificate_file=/etc/pki/tls/certs/localhost.crt
ssl_certificate_key_file=/etc/pki/tls/private/localhost.key

EOF
