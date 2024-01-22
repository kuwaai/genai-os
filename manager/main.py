import os
import logging
import uvicorn
from fastapi import FastAPI, APIRouter
from dotenv import load_dotenv



class EndpointFilter(logging.Filter):
    def filter(self, record: logging.LogRecord) -> bool:
        return record.getMessage().find("/health") == -1

# Run the FastAPI app with uvicorn
if __name__ == "__main__":
    load_dotenv()
    
    from src.controller.rule import rule_router
    from src.controller.internal import internal_router
    from src.controller.health import health_router

    # Filter out /health
    logging.getLogger("uvicorn.access").addFilter(EndpointFilter())

    # FastAPI app
    app = FastAPI()
    app.include_router(rule_router)
    app.include_router(internal_router)
    app.include_router(health_router)

    host=os.environ.get('SERVER_HOST', '0.0.0.0')
    port=int(os.environ.get('SERVER_PORT', '8000'))
    uvicorn.run(app, host=host, port=port)
