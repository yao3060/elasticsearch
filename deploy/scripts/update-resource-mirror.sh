#!/usr/bin/env bash
set -aeuo pipefail

# sed -i'.bak'  <<< for it to work on both GNU & BSD
sed -i'.bak' '/dl-cdn.alpinelinux.org/s/dl-cdn.alpinelinux.org/mirrors.tuna.tsinghua.edu.cn/g' .env
