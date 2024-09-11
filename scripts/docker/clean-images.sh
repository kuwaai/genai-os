#!/bin/bash

# Get a list of dangling image IDs
dangling_images=$(docker images --filter "dangling=true" -q --no-trunc)

# If there are dangling images, ask for confirmation before deleting
if [[ -n "$dangling_images" ]]; then
  read -p "This will delete all dangling images. Are you sure? (y/N) " -n 1 -r
  echo
  if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Deleting dangling images..."
    docker rmi $dangling_images
  else
    echo "Deletion cancelled."
  fi
fi