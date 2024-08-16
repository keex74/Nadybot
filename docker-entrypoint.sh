#!/bin/ash
# shellcheck shell=dash

errorMessage() {
	echo "$*"
	exit 1
}

checkBool() {
	value=$(echo "$1" | tr '[:upper:]' '[:lower:]')
	if [ "$value" = "1" ] || [ "$value" = "true" ] || [ "$value" = "yes" ]; then
		echo "$2"
	else
		echo "$3"
	fi
}

[ -z "$CONFIG_LOGIN" ] && errorMessage "You have to specify the login by setting \$CONFIG_LOGIN"
[ -z "$CONFIG_PASSWORD" ] && errorMessage "You have to specify the password by setting \$CONFIG_PASSWORD"
[ -z "$CONFIG_BOTNAME" ] && errorMessage "You have to specify the name of the bot by setting \$CONFIG_BOTNAME"
[ -z "$CONFIG_SUPERADMIN" ] && errorMessage "You have to specify the name of the Superadmin by setting \$CONFIG_SUPERADMIN"
[ -z "$CONFIG_DB_TYPE" ] && errorMessage "You have to specify the database type by setting \$CONFIG_DB_TYPE to sqlite or mysql"
[ -z "$CONFIG_DB_NAME" ] && errorMessage "You have to specify the name of the database by setting \$CONFIG_DB_NAME"
[ -z "$CONFIG_DB_HOST" ] && errorMessage "You have to specify the host/socket/directory of the database by setting \$CONFIG_DB_HOST"
[ -n "$CONFIG_LOG_LEVEL" ] && ( echo "$CONFIG_LOG_LEVEL" | grep -q -v -i -E '^(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)$' ) && errorMessage "You have specified an invalid \$CONFIG_LOG_LEVEL. Allowed values are DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT and EMERGENCY."
[ -z "$CONFIG_AUTO_UNFREEZE_LOGIN" ] || CONFIG_AUTO_UNFREEZE_LOGIN="\"${CONFIG_AUTO_UNFREEZE_LOGIN}\""
[ -z "$CONFIG_AUTO_UNFREEZE_PASSWORD" ] || CONFIG_AUTO_UNFREEZE_PASSWORD="\"${CONFIG_AUTO_UNFREEZE_PASSWORD}\""

cd /nadybot || exit
EXTRA_SETTINGS=$(set | grep '^CONFIG_SETTING_' | sed -e 's/^CONFIG_SETTING_//g'| while read -r SETTING; do
  KEY=$(echo "$SETTING" | cut -d '=' -f 1 | tr '[:upper:]' '[:lower:]')
  VALUE=$(echo "$SETTING" | cut -d '=' -f 2-)
  echo "$KEY = $VALUE"
done)
cat > /tmp/config.toml << DONE
$(if [ -n "$CONFIG_ORG_ID" ]; then echo "org_id = ${CONFIG_ORG_ID}"; fi)
[database]
type = "${CONFIG_DB_TYPE}"
name = "${CONFIG_DB_NAME}"
host = "${CONFIG_DB_HOST}"
username = "${CONFIG_DB_USER}"
password = "${CONFIG_DB_PASS}"

[paths]
cache = "${CONFIG_CACHEFOLDER:-./cache/}"
html = "./html/"
data = "./data/"
logs = "./logs/"
modules = ["./src/Modules", "./extras"]

[main]
login = "${CONFIG_LOGIN}"
password = "${CONFIG_PASSWORD}"
character = "${CONFIG_BOTNAME}"
dimension = ${CONFIG_DIMENSION:-5}

[general]
org_name = ""
super_admins = ["$(echo "${CONFIG_SUPERADMIN}" | sed -e 's/[, ]\+/", "/g')"]

show_aoml_markup = $(checkBool "${CONFIG_SHOW_AOML_MARKUP:-0}" true false)
default_module_status = ${CONFIG_DEFAULT_MODULE_STATUS:-0}
enable_console_client = $(checkBool "${CONFIG_ENABLE_CONSOLE:-0}" true false)
enable_package_module = $(checkBool "${CONFIG_ENABLE_PACKAGE_MODULE:-0}" true false)
auto_org_name = false

[proxy]
enabled = $(checkBool "${CONFIG_USE_PROXY:-0}" true false)
server = "${CONFIG_PROXY_SERVER:-127.0.0.1}"
port = ${CONFIG_PROXY_PORT:-9993}

[auto-unfreeze]
enabled = $(checkBool "${CONFIG_AUTO_UNFREEZE:-false}" true false)
$(if [ -n "${CONFIG_AUTO_UNFREEZE_LOGIN}" ]; then echo "login = ${CONFIG_AUTO_UNFREEZE_LOGIN}"; fi)
$(if [ -n "${CONFIG_AUTO_UNFREEZE_PASSWORD}" ]; then echo "login = ${CONFIG_AUTO_UNFREEZE_PASSWORD}"; fi)
use_nadyproxy = true

[settings]
${EXTRA_SETTINGS}
DONE
SUFFIX=1
while [ -n "$(eval echo "\${PROXY_CHARNAME_$SUFFIX:-}")" ]; do
	if [ -n "$(eval echo "\${PROXY_USERNAME_$SUFFIX:-}")" ]; then
		LASTUSER=$(eval echo "\${PROXY_USERNAME_$SUFFIX:-}")
	fi
	if [ -n "$(eval echo "\${PROXY_PASSWORD_$SUFFIX:-}")" ]; then
		LASTPASS=$(eval echo "\${PROXY_PASSWORD_$SUFFIX:-}")
	fi
	cat >> /tmp/config.toml <<-END

		[[worker]]
		login = "${LASTUSER}"
		password = "${LASTPASS}"
		character = "$(eval echo "\${PROXY_CHARNAME_$SUFFIX:-}")"
		dimension = ${CONFIG_DIMENSION:-5}
	END
	SUFFIX=$((SUFFIX+1))
done

LOG_CONFIG=$(cat conf/logging.json | sed -e "s/\"\*\": \"notice\"/\"*\": \"${CONFIG_LOG_LEVEL:-notice}\"/")
echo $LOG_CONFIG > /tmp/logging.json
ERROR_MSG=$(set | grep '^CONFIG_LOGGING_' | sed -e 's/^CONFIG_LOGGING_/.monolog./g'| while read -r SETTING; do
  KEY=$(echo "$SETTING" | cut -d '=' -f 1 | sed -e 's/_/./g')
  eval VALUE=$(echo "$SETTING" | cut -d '=' -f 2-)
  if ! jq --argjson foo ${VALUE} --help &>/dev/null; then
    echo "${VALUE} is not a valid JSON value"
    exit 1
  fi
  LOG_CONFIG=$(jq --argjson foo ${VALUE} "${KEY}=\$foo" /tmp/logging.json)
  echo $LOG_CONFIG > /tmp/logging.json
done)
if [ $? = 1 ]; then
  echo "${ERROR_MSG}"
  exit 1
fi

PHP=$(which php82 php81 php8 php | head -n 1)
if [ -n "$CONFIG_JIT_BUFFER_SIZE" ]; then
	PHP_PARAMS="${PHP_PARAMS:-} -dopcache.enable_cli=1 -dopcache.jit_buffer_size=${JIT_BUFFER_SIZE} -dopcache.jit=1235"
fi

EXITCODE=255
while [ "$EXITCODE" -eq 255 ]; do
	trap "" TERM
	# shellcheck disable=SC2086
	"$PHP" ${PHP_PARAMS:-} -f main.php -- --log-config /tmp/logging.json /tmp/config.toml "$@"
	EXITCODE=$?
	trap - TERM
done
exit $EXITCODE
