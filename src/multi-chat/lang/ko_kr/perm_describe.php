<?php

return [
    'Profile_update_name' => '자신의 계정 이름을 업데이트할 권한',
    'Profile_update_email' => '자신의 이메일 주소를 업데이트할 권한',
    'Profile_update_password' => '자신의 비밀번호를 업데이트할 권한',
    'Profile_update_external_api_token' => '웹사이트에 저장된 외부 API 키를 업데이트할 권한',
    'Profile_delete_account' => '자신의 계정을 삭제할 권한',
    'Profile_read_api_token' => '웹사이트 API 키를 읽을 권한',
    'Profile_update_api_token' => '웹사이트 API 키를 업데이트할 권한',

    'Room_update_detail_feedback' => '응답에 대한 자세한 피드백 태그를 제공할 권한',
    'Room_read_access_to_api' => '웹사이트 API를 사용할 권한',
    'Room_update_send_message' => '대화에 메시지를 보낼 권한 (생성에는 영향을 미치지 않음)',
    'Room_update_new_chat' => '대화를 생성할 권한',
    'Room_update_feedback' => '모델 사용 피드백을 보낼 권한',
    'Room_delete_chatroom' => '대화를 삭제할 권한',
    'Room_read_export_chat' => '채팅 기록을 내보낼 권한',
    'Room_update_import_chat' => '채팅 기록을 가져올 권한 (대화 생성 권한은 여전히 필요함)',
    'Room_update_react_message' => '메시지에 추가 작업 버튼을 사용할 권한(예: 인용, 번역 등 기능)',
    'Room_update_ignore_upload_constraint' => '시스템 설정에서 모든 파일 업로드 제한을 무시하고 임의의 파일을 업로드할 수 있습니다.',

    'Dashboard_read_statistics' => '통계 정보에 액세스할 권한',
    'Dashboard_read_blacklist' => '블랙리스트에 액세스할 권한',
    'Dashboard_read_feedbacks' => '피드백 데이터에 액세스할 권한',
    'Dashboard_read_logs' => '시스템 로그에 액세스할 권한',
    'Dashboard_read_safetyguard' => '안전 필터에 액세스할 권한',
    'Dashboard_read_inspect' => '메시지 브라우저에 액세스할 권한',

    'Store_create_community_bot' => '커뮤니티 봇을 생성하는 데 필요한 권한',
    'Store_create_private_bot' => '개인 봇을 생성하는 데 필요한 권한',
    'Store_create_group_bot' => '그룹 봇을 생성하는 데 필요한 권한',
    'Store_update_modify_bot' => '자체 제작 봇을 수정하는 데 필요한 권한',
    'Store_delete_delete_bot' => '자체 제작 봇을 삭제하는 데 필요한 권한',
    'Store_read_discover_community_bots' => '커뮤니티 봇을 읽는 데 필요한 권한',
    'Store_read_discover_system_bots' => '시스템 봇을 읽는 데 필요한 권한',
    'Store_read_discover_private_bots' => '자체 제작 봇을 읽는 데 필요한 권한',
    'Store_read_discover_group_bots' => '그룹 봇을 읽는 데 필요한 권한',
    'Store_read_any_modelfile' => '임의의 modelfile을 읽는 데 필요한 권한',
    
    'Cloud_read_my_files' => '개인 디렉토리 파일을 읽는 데 필요한 권한',
    'Cloud_update_upload_files' => '개인 디렉토리에 파일을 업로드하는 데 필요한 권한',
    'Cloud_delete_my_files' => '개인 디렉토리 파일을 삭제하는 데 필요한 권한'
];