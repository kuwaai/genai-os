<?php

return [
    'interface.header' => '대시 보드 관리 인터페이스',
    'route' => '대시 보드',

    'tab.statistics' => '통계 정보',
    'tab.blacklist' => '블랙리스트',
    'tab.feedbacks' => '피드백',
    'tab.logs' => '시스템 로그',
    'tab.safetyguard' => '안전 가드',
    'tab.inspect' => '메시지 브라우저',

    //Tab_logs
    'colName.Action' => '작업:',
    'colName.Description' => '설명:',
    'colName.UserID' => '사용자 ID:',
    'colName.IP' => 'IP 주소:',
    'colName.Timestamp' => '타임스탬프:',
    'msg.NoRecord' => '기록 없음',
    'filter.StartDate' => '시작:',
    'filter.EndDate' => '종료:',
    'filter.Action' => '작업:',
    'filter.Description' => '설명:',
    'filter.UserID' => '사용자 ID:',
    'filter.IPAddress' => 'IP 주소:',

    //Tab_feedback
    'hint.PasteRawDataHere' => '변환할 원본 데이터를 붙여 넣거나 파일을 여기로 끌어다 놓으세요.',
    "hint.wip_option"=>"진행 중인 작업, 현재 옵션이 없습니다",
    'header.ActiveModels' => '활성 모델',
    'header.InactiveModels' => '비활성 모델',
    'header.ModelFilter' => '모델 필터:',
    'header.ExportSetting' => '내보내기 설정:',

    'button.ExportAndDownload' => '내보내기 및 다운로드',
    'button.LoadFile' => '파일 로드',
    'button.ConvertAndDownload' => '변환 및 다운로드',
    'msg.MustHave1Model' => '내보내기할 모델을 하나 이상 선택해야 합니다',
    'msg.InvalidJSONFormat' => '잘못된 JSON 형식',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"안전 가드 시스템이 오프라인 상태입니다",
    'header.create_rule' => '필터 규칙 생성',
    'header.update_rule' => '필터 규칙 업데이트',
    'rule.filter.keyword' => '키워드 규칙',
    'rule.filter.embedding' => '임베딩 규칙',

    'action.overwrite' => '시스템에 의해 덮어쓰기',
    'action.block' => '차단, 선택적 경고',
    'action.warn' => '경고만',
    'action.none' => '동작 없음',

    'msg.SomethingWentWrong' => '문제가 발생했습니다...',
    'msg.choose_target' => '모델을 선택하세요',
    'msg.create_rule' => '이 규칙을 만들 것입니까?',
    'msg.delete_rule' => '이 규칙을 삭제하시겠습니까?',
    'msg.update_rule' => '이 규칙을 업데이트하시겠습니까?',

    'rule.name' => '규칙 이름',
    'rule.description' => '규칙 설명',
    'rule.target' => '적용할 모델 지정',
    'rule.action' => '규칙 동작',
    'rule.warning' => '경고 메시지(선택 사항)',
    'rule.filter.input' => '입력 필터',
    'rule.filter.output' => '출력 필터',

    'button.create_rule' => '규칙 생성',
    'button.create' => '생성',
    'button.cancel' => '취소',
    'button.delete' => '삭제',
    'button.update' => '업데이트',
];
