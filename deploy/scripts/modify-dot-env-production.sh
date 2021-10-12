#!/usr/bin/env bash
set -aeuo pipefail

sed -i "/ENVIRONMENT=/c ENVIRONMENT=production" .env
sed -i '/DOCKER_NETWORK_IPAM_SUBNET/s/^#\ //g' .env
sed -i '/DOCKER_NETWORK_IPAM_GATEWAY/s/^#\ //g' .env
sed -i "/APP_ENV=/c APP_ENV=production" .env

# set k8s secrets
sed -i "/REDIS_PASSWORD=/c REDIS_PASSWORD=${REDIS_PASSWORD}" k8s/overlays/production/secrets.example.txt
sed -i "/REDIS_SESSION_PASSWORD=/c REDIS_SESSION_PASSWORD=${REDIS_SESSION_PASSWORD}" k8s/overlays/production/secrets.example.txt
sed -i "/REDIS_SEARCH_PASSWORD=/c REDIS_SEARCH_PASSWORD=${REDIS_SEARCH_PASSWORD}" k8s/overlays/production/secrets.example.txt
sed -i "/BACKEND_DB_PASSWORD=/c BACKEND_DB_PASSWORD=${BACKEND_DB_PASSWORD}" k8s/overlays/production/secrets.example.txt
sed -i "/ALIYUN_ACCESS_KEY_ID=/c ALIYUN_ACCESS_KEY_ID=${ALIYUN_ACCESS_KEY_ID}" k8s/overlays/production/secrets.example.txt
sed -i "/ALIYUN_ACCESS_KEY_SECRET=/c ALIYUN_ACCESS_KEY_SECRET=${ALIYUN_ACCESS_KEY_SECRET}" k8s/overlays/production/secrets.example.txt
