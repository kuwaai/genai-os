#!/bin/bash

project=taide
container=web_agent

sudo docker-compose -p ${project} stop ${container} 
sudo docker-compose -p ${project} up -d ${container}
