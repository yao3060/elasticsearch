#!/usr/bin/env bash
set -aeuo pipefail

sed -i "/ENVIRONMENT=/c ENVIRONMENT=staging" .env
sed -i '/DOCKER_NETWORK_IPAM_SUBNET/s/^#\ //g' .env
sed -i '/DOCKER_NETWORK_IPAM_GATEWAY/s/^#\ //g' .env
sed -i "/REACT_HTTP_PORT=/c REACT_HTTP_PORT=7002" .env
sed -i "/LARAVEL_HTTP_PORT=/c LARAVEL_HTTP_PORT=7003" .env
sed -i "/DRUPAL_HTTP_PORT=/c LARAVEL_HTTP_PORT=7004" .env
sed -i "/APP_ENV=/c APP_ENV=staging" .env
sed -i "/APP_URL=/c APP_URL=https://staging.818ps.com" .env
sed -i "/API_URL=/c API_URL=https://staging-api.818ps.com" .env
sed -i "/ADMIN_BACKEND_DOMAIN=/c ADMIN_BACKEND_DOMAIN=staging-admin.818ps.com" .env
sed -i "/ADMIN_SECURE=/c ADMIN_SECURE=true" .env
