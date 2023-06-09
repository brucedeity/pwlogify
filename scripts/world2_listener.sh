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
    "建立了队伍"
    "成为队员"
    "丢弃包裹"
    "丢弃装备"
    "执行了内部命令"
    "拣起金钱"
    "丢弃金钱"
    "从NPC购买了"
    "卖店"
    "得到金钱"
    "拣起"
    "升级到"
    "花掉金钱"
    "消耗了sp"
    "技能"
    "制造了"
    "采集得到"
    "孵化了宠物蛋"
    "还原了宠物蛋"
    "组队拣起用户"
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
