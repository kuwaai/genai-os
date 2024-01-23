import os, sys
from sqlalchemy.orm import Session

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from database import with_db_session
from model.rule import Rule, Target, ActionEnum
from model.detector import Detector, DetectorTypeEnum, ChainEnum
from model.embedding import Embedding

RESERVED_RULE_IDS = list(range(11))

# Hardcoded special rules since the current API can't create them.
# The rule ID from 1 to 10 are reserved for special rules.
@with_db_session
def add_special_rules(db: Session):
    special_rules = [
        Rule(
            id=10,
            name='簡體偵測/轉繁',
            description='偵測到輸出簡體字就執行指定行為。若規則行為為「改寫」，則會進行簡繁轉換，且有轉換錯誤的風險。',
            action=ActionEnum.overwrite,
            message='',
            targets=[],
            detectors=[
                Detector(
                    type=DetectorTypeEnum.charset_guard,
                    chain=ChainEnum.post_filter,
                    deny_list=[]
                )
            ]
        ),
        Rule(
            id=9,
            name='LLaMA Guard',
            description='使用 LLaMA Guard 防護六大類有害問答(暴力或仇恨言論、露骨內容、預備犯罪、槍枝及非法武器、管制或受控物質、自我傷害)。',
            action=ActionEnum.block,
            message='系統檢測到不安全內容，相關內容違反我們的使用者政策，因此停止輸出模型內容。',
            targets=[],
            detectors=[
                Detector(
                    type=DetectorTypeEnum.llama_guard,
                    chain=ChainEnum.pre_filter,
                    deny_list=[]
                ),
                Detector(
                    type=DetectorTypeEnum.llama_guard,
                    chain=ChainEnum.post_filter,
                    deny_list=[]
                )
            ]
        )
    ]

    note = " 為特殊規則，可停用但無法刪除。"
    for r in special_rules:
        r.description += note

    special_rules = [db.merge(r) for r in special_rules]
    db.add_all(special_rules)
    db.commit()