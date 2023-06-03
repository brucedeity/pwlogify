# PW Logify
This is a PHP-based logging utility that captures and processes logs from a Perfect World Server.

## Description
Logify processes log entries, such as Chat, GM Actions, Item Crafting, Item Pickup, Auction Purchases, GM Commands, Title Acquisitions, Task Processings, Mail Sendings, and Player Login/Logout actions.

Each log entry is parsed and broken down into respective fields for further processing or storage. These parsed entries can be leveraged for numerous purposes such as tracking user behavior, debugging, auditing, and much more.

## Features
* Parse and process various types of game-related log entries
* Convert log entries into structured formats for easy analysis
* Easy-to-extend structure, allowing for new types of log entries to be quickly integrated

## Installation
To use this utility, follow these steps:

1. Clone the repo: `git clone https://github.com/brucedeity/pwlogify.git`
2. Install dependencies: `composer install`
3. Open the start_pwlogify.sh file and configure the `script_path` variable
4. Also open the world2_listener.sh file in the scripts folder and configure the `server_path` variable if needed.