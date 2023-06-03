#!/bin/bash

pwlogify_pid=$(pidof -x world2_listener.sh)

if [ -n "$pwlogify_pid" ]; then
    echo "Stopping pwlogify process (PID: $pwlogify_pid)"
    kill $pwlogify_pid
else
    echo "No pwlogify process found."
fi
