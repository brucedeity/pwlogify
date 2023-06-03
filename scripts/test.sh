#!/bin/bash
grep_pattern="formatlog:sendmail\|formatlog:rolelogin\|formatlog:rolelogout"
grep --line-buffered "${grep_pattern}" /home/logs/world2.formatlog
