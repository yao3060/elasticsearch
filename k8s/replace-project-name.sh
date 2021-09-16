#!/bin/sh

find ./ -not -name '*.sh' -type f -exec sed -i -e 's/project-name/elasticsearch/g' {} \;
