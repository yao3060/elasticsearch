# K8S configuration for 818PS ElasticSearch Service

## Access Cluster

List all pods

```bash
kubectl get pods --all-namespaces
```

## Staging

Deployment is done by running

```
./staging.sh
```

## Production

Production deployment is not scheduled to be done using Kubernetes.
Deployment will be done manually using a more standard infrastructure approach.

## Add TLS configuration

```bash
kubectl create secret tls wildcard-818ps-com \
  --cert=818ps.pem \
  --key=818ps.key
```

## hpa-test

```bash
#!/bin/sh

kubectl run -i --tty load-generator --rm \
  --image=busybox \
  --restart=Never \
  -- /bin/sh \
  -c "while sleep 0.01; do wget -q -O- http://elasticsearch-staging-backend-nginx/site/hpa; done"
```
