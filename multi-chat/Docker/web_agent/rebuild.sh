#!/bin/bash

project=taide

sudo docker-compose -p ${project} down
sudo docker-compose -p ${project} up --build -d
