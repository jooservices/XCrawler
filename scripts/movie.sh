#!/bin/bash

ffprobe -v quiet -print_format json -show_format -show_streams $1 > $2
md5=($(md5sum $1))
filesize=($(wc -c $1))
data=$(cat $2)
url="http://127.0.0.1:8000/api/files?file=$1&hash=${md5}&size=${filesize}"

curl -s -o /dev/null --data "data=$data" "$url"
