#!/bin/bash

fromdir="/tmp/images"
todir="/data/images"
rawfile="$3"
filename=`basename $rawfile`

tofile="${rawfile/#${fromdir}/${todir}}"
todir="${tofile%%${filename}}"

mkdir -p $todir

mv $rawfile $tofile


