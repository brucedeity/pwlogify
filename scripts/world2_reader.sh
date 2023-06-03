#!/bin/bash

server_path="/home"

# Function to process log lines
process_log_line() {
    local log_line=$1
    php_output=$(php ../app/Logify.php "${log_line}" 2>> ../logs/errors.txt)
    echo "$php_output"
}

# Monitor and process log lines
stdbuf -oL tail -f -n0 \
    $server_path/logs/world2.formatlog \
    $server_path/logs/world2.log \
    $server_path/logs/world2.chat \
    | while IFS= read -r log_line; do
        # echo "Log line: $log_line"
        process_log_line "${log_line}"
done
