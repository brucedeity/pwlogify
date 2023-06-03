#!/bin/bash

script_path="/home/pwlogify/scripts/world2_listener.sh"

pwlogify_pid=$(pidof -x $(basename -- "$script_path"))

if [ -n "$pwlogify_pid" ]; then
    echo "pwlogify is already running (PID: $pwlogify_pid)"
else
    echo "Starting pwlogify"
    cd $(dirname -- "$script_path")
    nohup "$script_path" > /dev/null 2>&1 &
fi
