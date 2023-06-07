#!/bin/bash

source_path="../logs"
destination_path="/var/www/html/pwlogify/logs"

echo "Source path: $source_path"
echo "Destination path: $destination_path"

if [ ! -d "$destination_path" ]; then
  sudo mkdir -p "$destination_path"
fi

sudo ln -s "$source_path" "$destination_path"

if [ $? -eq 0 ]; then
  echo "Just created a symbolic link from $source_path to $destination_path successfully!"
fi
