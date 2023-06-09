#!/bin/bash

server_path="/home"
script_dir=$(dirname "$(readlink -f "$0")")
logify_script="${script_dir}/../app/Logify.php"

patterns=(
    "GM:"
    "chat :"
    "obtain title"
    "formatlog:sendmail"
    "formatlog:rolelogin"
    "formatlog:rolelogout"
    "formatlog:trade"
    "formatlog:task"
    "formatlog:die"
    "formatlog:faction"
    "formatlog:gshop_trade"
    "formatlog:upgradefaction"
    "�����˶���"
    "��Ϊ��Ա"
    "��������"
    "����װ��"
    "ִ�����ڲ�����"
    "�����Ǯ"
    "������Ǯ"
    "��NPC������"
    "����"
    "�õ���Ǯ"
    "����"
    "������"
    "������Ǯ"
    "������sp"
    "����"
    "������"
    "�ɼ��õ�"
    "�����˳��ﵰ"
    "��ԭ�˳��ﵰ"
    "��Ӽ����û�"
)

process_log_line() {
    local log_line=$1
    php "${logify_script}" "${log_line}" 1>/dev/null 2>> "${script_dir}/../logs/errors.txt"
}

stdbuf -oL tail -f -n0 \
    "${server_path}/logs/world2.formatlog" \
    "${server_path}/logs/world2.log" \
    "${server_path}/logs/world2.chat" \
    | while IFS= read -r log_line; do
        for pattern in "${patterns[@]}"; do
            if [[ $log_line =~ $pattern ]]; then
                # echo "Found this log line that matches the pattern: '$pattern': $log_line"
                process_log_line "${log_line}"
                break
            fi
        done
done
