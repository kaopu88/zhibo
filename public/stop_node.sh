#!/bin/sh
echo "stopping..."
ID=`ps -ef | grep "bxkj_node" | grep -v "$0" | grep -v "grep" | awk '{print $2}'`
TOTAL=0
for tmp in $ID
do
if [ -n $tmp ];then
TOTAL=$[TOTAL+1]
fi
done
echo "running processes total: $TOTAL"
if [ $TOTAL -gt 0 ];then
for id in $ID
do
    kill -9 $id
    echo "killed $id"
done
echo "stopped"
else
echo "not found"
fi
