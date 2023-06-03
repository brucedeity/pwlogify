#!/bin/bash

server_path="/home"

# Get the directory of the current script
script_dir=$(dirname "$(readlink -f "$0")")

# Determine the path to Logify.php relative to the script directory
logify_script="${script_dir}/../app/Logify.php"

# Function to process log lines
process_log_line() {
    local log_line=$1
    php_output=$(php "${logify_script}" "${log_line}" 2>> "${script_dir}/../logs/errors.txt")
    printf "%s\n" "$php_output"
}


# Monitor and process log lines
stdbuf -oL tail -f -n0 \
    "${server_path}/logs/world2.formatlog" \
    "${server_path}/logs/world2.log" \
    "${server_path}/logs/world2.chat" \
    | while IFS= read -r log_line; do
        # echo "Log line: $log_line"
        process_log_line "${log_line}"
done