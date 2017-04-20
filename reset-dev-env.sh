#! /bin/bash

sed -i "s/^cache_dir.*//" $PWD/raw/config/image-cache.yml
sed -i "s/^#cache_dir/cache_dir/" $PWD/raw/config/image-cache.yml
sed -i "s/127.0.0.1:6800/172.20.0.1:6800/" $PWD/raw/config/image-cache.yml
