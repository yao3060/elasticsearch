#!/usr/bin/env bash
set -aeuo pipefail

sed -i "/ENVIRONMENT=/c ENVIRONMENT=production" .env
sed -i '/DOCKER_NETWORK_IPAM_SUBNET/s/^#\ //g' .env
sed -i '/DOCKER_NETWORK_IPAM_GATEWAY/s/^#\ //g' .env
sed -i "/APP_ENV=/c APP_ENV=production" .env
