<?php

return [
    'route' => '管理',
    'interface.header' => '管理員管理介面',
    'button.delete' => '刪除',
    'button.update' => '更新',
    'button.create' => '建立',
    'button.save' => '儲存',
    'button.yes' => '是，我確定',
    'button.no' => '否，取消',
    'button.cancel' => '取消',
    'button.close' => '關閉',
    'button.accept' => '我同意',
    'button.export' => '匯出',
    'button.updateWeb' => '更新網站',
    'button.shutdown' => '關機',
    'button.refresh' => '重新整理',

    //Tabs
    'tab.groups' => '群組',
    'tab.users' => '使用者',
    'tab.llms' => '模型',
    'tab.settings' => '網站設定',
    'tab.kernel' => '核心',
    'tab.workers' => '任務處理器',

    //Groups
    'button.new_group' => '新增群組',
    'header.create_group' => '建立一個新群組',
    'label.tab_permissions' => '頁面權限',
    'label.invite_code' => '邀請碼',
    'label.group_name' => '名稱',
    'label.invite_code' => '邀請碼',
    'placeholder.invite_code' => '邀請碼',
    'label.describe' => '介紹',
    'placeholder.group_name' => '群組名稱',
    'placeholder.group_detail' => '群組註解',
    'label.read' => '讀取',
    'label.delete' => '刪除',
    'label.update' => '更新',
    'label.create' => '新增',
    'label.llm_permission.disabled' => '模型使用權限(已停用模型)',
    'label.llm_permission.enabled' => '模型使用權限(已啟用模型)',
    'header.edit_group' => '編輯群組',
    'hint.group_updated' => '群組更新成功！',
    'hint.group_created' => '群組建立成功！',
    'modal.delete_group.header' => '您確定要刪除該群組',

    //Users
    'header.menu' => '主選單',
    'header.group_selector' => '群組選擇器',
    'header.fuzzy_search' => '模糊搜尋器',
    'header.create_user' => '建立使用者',
    'label.group_selector' => '從群組開始篩選使用者',
    'label.fuzzy_search' => '使用名稱或信箱搜尋使用者',
    'label.create_user' => '建立一個使用者的設定檔',

    'create_user.header' => '建立一個新的帳號',
    'create_user.joined_group' => '加入的群組',
    'label.members' => '個成員',
    'label.other_users' => '無群組成員',
    'button.return_group_list' => '返回群組列表',
    'placeholder.search_user' => '搜尋信箱或名稱',
    'hint.enter_to_search' => '按下Enter來搜尋',

    'group_selector.header' => '編輯使用者',
    'placeholder.email' => '使用者信箱',
    'placeholder.username' => '使用者名稱',
    'label.name' => '名稱',
    'modal.delete_user.header' => '確定要刪除使用者',
    'button.cancel' => '取消',
    'label.email' => '電子郵件',
    'label.password' => '密碼',
    'label.update_password' => '更新密碼',
    'label.detail' => '詳細說明',
    'placeholder.new_password' => '新密碼',
    'label.require_change_password' => '下次登入要求修改密碼',
    'label.extra_setting' => '額外設定',
    'label.created_at' => '創建於',
    'label.updated_at' => '更新於',

    //LLMs
    'button.new_model' => '新增模型',
    'label.enabled_models' => '已啟用模型',
    'label.disabled_models' => '已停用模型',
    'header.create_model' => '建立模型設定檔',
    'modal.create_model.header' => '您確定要建立這個設定檔？',
    'label.model_image' => '模型頭像',
    'label.model_name' => '模型名稱',
    'label.order' => '展示順序',
    'label.link' => '外部連結',
    'placeholder.description' => '這個模型的相關介紹',
    'label.version' => '版本',
    'label.access_code' => '存取代碼',
    'placeholder.link' => '該模型的外部相關連結',
    'header.update_model' => '編輯模型設定檔',
    'label.description' => '敘述',
    'modal.update_model.header' => '您確定要更新這個語言模型設定檔嗎',
    'modal.delete_model.header' => '您確定要刪除這個語言模型設定檔嗎',
    'modal.confirm_setting_modal.shrink_max_upload_file_count' => '降低上傳檔案總數限制會刪除超出的使用者檔案，您確定嗎?',

    //setting
    'header.settings' => '網站設定',
    'header.updateWeb' => '網站更新進度',
    'header.confirmUpdate' => '是否確定更新網站',
    'label.reloginWarning' => '網站更新會暫時將所有Worker重啟，並在更新完畢後重新啟動10個Worker，確定要開始進行更新了嗎？',
    'label.settings' => '所有關於網站的設定都可在此調整',
    'label.allow_register' => '允許註冊',
    'button.reset_redis' => '重設Redis快取',
    'hint.saved' => '已儲存',
    'hint.redis_cache_cleared' => 'Redis快取已清除',
    'label.updateweb_git_ssh_command' =>'環境變數GIT_SSH_COMMAND',
    'label.updateweb_path' => '環境變數PATH',
    'label.need_invite' => '註冊必需有邀請碼',
    'label.footer_warning' => '對話底部警告',
    'label.anno' => '系統公告',
    'label.tos' => '服務條款',
    'label.upload_max_size_mb' => '上傳檔案大小限制(MB)',
    'label.upload_allowed_extensions' => '允許上傳的副檔名 (* 表示任意副檔名)',
    'label.upload_max_file_count' => '上傳檔案總數限制 (-1 表示不限制數量)',

    //kernel
    'label.kernel_location' => '核心連線位置',
    'label.safety_guard_API' => '安全過濾連線位置',
    'label.ready' => '就緒',
    'label.busy' => '忙碌',
    'label.accesscode' => '存取代碼',
    'label.endpoint' => '連線接口',
    'label.status' => '使用狀態',
    'label.historyid' => '紀錄ID',
    'label.userid' => '使用者ID',
    'button.new_executor' => '新增執行器',
    'label.edit_executor' => '編輯執行器',
    'label.create_executor' => '新增執行器',

    //Workers
    'label.failed' => '操作失敗。請再試一次。',
    'label.loading' => '加載中...',
    'label.last_refresh' => '最後刷新時間：:time',
    'label.current_worker_count' => '當前任務處理器數量',
    'label.error_fetching_worker_count' => '獲取任務處理器數量時出錯。',
    'label.last_refresh_time' => '最後刷新',
    'label.seconds_ago' => '秒前',
    'label.error' => '錯誤',
    'label.valid_worker_count' => '請輸入有效的任務處理器數量。',
    'label.worker_started' => '任務處理器啟動成功',
    'label.worker_start_failed' => '任務處理器啟動失敗：',
    'label.no_workers' => '沒有已啟動的任務處理器',
    'label.worker_stopped' => '任務處理器已停止',

    'button.start' => '啟動任務處理器',
    'button.stop' => '停止所有任務處理器',
    'button.confirm' => '確認',
    'button.cancel' => '取消',

    'modal.start.title' => '啟動任務處理器',
    'modal.start.label' => '任務處理器數量：',
    'modal.stop.title' => '停止所有任務處理器',
    'modal.stop.confirm' => '您確定要停止所有任務處理器嗎？',
];
