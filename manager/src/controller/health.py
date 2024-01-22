from fastapi import APIRouter

health_router = APIRouter(prefix="/v1/management")

# API endpoint for health check
@health_router.get("/health", status_code=204)
def health_check():
    return None