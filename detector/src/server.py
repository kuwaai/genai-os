# Serving the incoming detection requests.
# 1. Get the corresponding MixedGuard with (model_id, chain)
# 2. Check the request with the MixedGuard pipeline
# 3. Response (action, message) based on the checking result