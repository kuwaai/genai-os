#!/bin/bash

# Function to print messages
log() {
  echo "[INFO] $1"
}

# Function to print error messages
error() {
  echo "[ERROR] $1" >&2
}

# Function to get the current Git branch
get_current_branch() {
  git rev-parse --abbrev-ref HEAD
}

# Function to get the latest commit hash
get_latest_commit() {
  git rev-parse HEAD
}

# Check if git is available
if ! command -v git &> /dev/null
then
  error "git could not be found. Please install git and try again."
  exit 1
fi

# Display the current branch and commit before update
current_branch=$(get_current_branch)
current_commit=$(get_latest_commit)
log "Current branch: $current_branch"
log "Current commit: $current_commit"

# Stash local changes
log "Stashing local changes..."
git stash push -m "Auto stash before update"

# Pull the latest changes from the origin
log "Pulling the latest changes from the origin..."
if ! git pull origin "$current_branch"
then
  error "Failed to pull from origin. Please check your git configuration."
  exit 1
fi

# Apply stashed changes
log "Applying stashed changes..."
if git stash list | grep -q "Auto stash before update"
then
  if ! git stash pop
  then
    error "Failed to apply stashed changes. You may need to resolve conflicts manually."
    exit 1
  fi
else
  log "No stashed changes to apply."
fi

# Display the current branch and commit after update
updated_commit=$(get_latest_commit)
log "Updated branch: $current_branch"
log "Updated commit: $updated_commit"

# Build the Docker image
log "Building the Docker image..."
if ! ./run.sh build
then
  error "Failed to build the Docker image. Please check the build script."
  exit 1
fi

log "Update and build completed successfully."