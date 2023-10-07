#!/bin/sh
echo "start node service...."
cd /www/wwwroot/zhibb
nohup php think bxkj_node start >output_node.log 2>&1 &
echo "start MQ service...."
nohup php think bxkj_mq start >output_mq.log 2>&1 &