#!/usr/bin/env bash
set -aeuo pipefail

docker login --username=$ALIYUN_USERNAME $ALIYUN_CONTAINER_REGISTRY --password=$DOCKER_PASSWORD

IMAGES=$@

NAMESPACE=818ps/ips
for image in ${IMAGES[*]}
do
  docker build --target $image --tag $ALIYUN_CONTAINER_REGISTRY/$NAMESPACE/${COMPOSE_PROJECT_NAME}_${image}:${VERSION} -f .docker/Dockerfile .
  docker push $ALIYUN_CONTAINER_REGISTRY/$NAMESPACE/${COMPOSE_PROJECT_NAME}_${image}:${VERSION}
  docker tag $ALIYUN_CONTAINER_REGISTRY/$NAMESPACE/${COMPOSE_PROJECT_NAME}_${image}:${VERSION} $ALIYUN_CONTAINER_REGISTRY/$NAMESPACE/${COMPOSE_PROJECT_NAME}_${image}:latest
  docker push $ALIYUN_CONTAINER_REGISTRY/$NAMESPACE/${COMPOSE_PROJECT_NAME}_${image}:latest
done
