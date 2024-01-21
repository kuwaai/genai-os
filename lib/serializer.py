import json
from sqlalchemy.orm import DeclarativeBase
from datetime import datetime

# Ref: https://stackoverflow.com/a/10664192
class AlchemyEncoder(json.JSONEncoder):

    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.visited_objs = []

    def default(self, obj):
        if isinstance(obj, datetime):
            return str(obj)
        if not isinstance(obj, DeclarativeBase):
            return json.JSONEncoder.default(self, obj)
        
        # Encode a SQLalchemy object
        self.visited_objs.append(obj)

        # Go through each field in this SQLalchemy class
        fields = {}
        for field in [x for x in dir(obj) if not x.startswith('_') and x not in ['metadata', 'registry']]:
            val = obj.__getattribute__(field)

            # Prevent circular reference
            fields[field] = None if val in self.visited_objs else val

        # A json-encodable dict
        return fields