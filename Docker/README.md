# Docker-related Files

- This directory is a temporary place to avoid direct modification of the contents in "/web/"
- It should be merged to other directory in the future

### Building Step
- The Workers and Agent are connected to the same network
```bash

# Containers of web server, database, and the Agent
cd Docker/web_agent
./rebuild.sh

# Container of Model Worker (Use ContextualCC as example)
cd Docker/model_workers
./build.sh
docker-compose -p taide-model up # The project name is casual

```

### Issue
- The `DefaultSeeder` of the web server won't seed default users to the database
    - Version of Laravel: 10.25.1
- The source code of both the web server and the Agent are not copying to the image
    - Current solution: Use volume to mount the source code