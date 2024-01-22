import os
import sys
import enum

from pydantic import BaseModel, Field
from typing import Optional, List, get_type_hints

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from model.rule import Rule as RuleDbModel
from model.rule import Target, ActionEnum
from model.detector import Detector, DetectorTypeEnum, ChainEnum
from model.embedding import Embedding
from embedding_model_adapter import InfinityEmbeddingClient

embedding_model = InfinityEmbeddingClient(
    host=os.environ.get('EMBED_HOST'),
    model_name=os.environ.get('EMBED_MODEL'),
)

# Pydantic model for the Rule object in the API.
class RuleApiModel(BaseModel):
    name: str
    target: List[str]
    pre_filter: dict = Field(alias='pre-filter')
    post_filter: dict = Field(alias='post-filter')
    action: str

    id: Optional[int] = None
    description: Optional[str] = None
    retrieval_timestamp: Optional[int] = Field(default=None, alias='retrieval-timestamp')
    message: Optional[str] = None

class RuleModelConverter:
    @staticmethod
    async def api2db(api_rule: RuleApiModel) -> RuleDbModel:
        data = RuleModelConverter.get_common_field(api_rule)
        data['targets']=[Target(model_id=t) for t in api_rule.target]
        detectors = []
        detectors += await RuleModelConverter.filter2detector(api_rule.pre_filter, ChainEnum.pre_filter)
        detectors += await RuleModelConverter.filter2detector(api_rule.post_filter, ChainEnum.post_filter)
        data['detectors'] = detectors
        
        return RuleDbModel(**data)

    @staticmethod
    def db2api(db_rule: RuleDbModel) -> RuleApiModel:
        data = RuleModelConverter.get_common_field(db_rule)
        data['target']=[t.model_id for t in db_rule.targets]
        data['pre-filter']=RuleModelConverter.detector2filter(
            detectors=db_rule.detectors,
            filter_chain=ChainEnum.pre_filter
        )
        data['post-filter']=RuleModelConverter.detector2filter(
            detectors=db_rule.detectors,
            filter_chain=ChainEnum.post_filter
        )
        data['retrieval-timestamp'] = round(db_rule.update_at.timestamp())
        
        return RuleApiModel.parse_obj(data)
    
    @staticmethod
    def get_common_field(src) -> dict:
        result = {}
        common_field = ['id', 'name', 'description', 'action', 'message']
        for field in common_field:
            val = getattr(src, field, None)
            result[field] = val if not isinstance(val, enum.Enum) else val.value
        return result

    @staticmethod
    def detector2filter(detectors: [Detector], filter_chain: ChainEnum) -> dict:
        detectors = [d for d in detectors if d.chain == filter_chain]
        detector_type_map = {
            DetectorTypeEnum.keyword_guard: 'keyword',
            DetectorTypeEnum.vector_guard: 'embedding'
        }
        result = {
            detector_type_map[d.type.value]: d.deny_list
            for d in detectors if d.type in detector_type_map
        }

        return result

    @staticmethod
    async def filter2detector(filters: dict, filter_chain: ChainEnum) -> [Detector]:
        filter_type_map = {
            'keyword': DetectorTypeEnum.keyword_guard,
            'embedding': DetectorTypeEnum.vector_guard
        }

        result = []
        for filter_type, deny_list in filters.items():
            data = {}
            if filter_type == 'embedding':
                # Calculate embedding
                embeddings = await embedding_model.aembed(deny_list)
                embedding_objs = [
                    Embedding(
                        model=embedding_model.model_name,
                        sentence=s, embedding=e,
                    )
                    for s, e in zip(deny_list, embeddings)
                ]
                data['embeddings'] = embedding_objs

            data.update({
                'type': filter_type_map[filter_type],
                'chain': filter_chain,
                'deny_list': deny_list
            })
            result.append(Detector(**data))

        return result