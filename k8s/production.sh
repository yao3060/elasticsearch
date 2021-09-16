#!/bin/sh

if [ ! -f overlays/production/secrets.txt ]; then
  cp overlays/production/secrets.example.txt overlays/production/secrets.txt
fi

cp overlays/production/secrets.txt base/apps/secrets.txt
cp overlays/production/secrets.txt base/db/secrets.txt
kubectl apply -k overlays/production/
