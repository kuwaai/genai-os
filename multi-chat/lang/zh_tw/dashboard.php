<?php

return [
    'interface.header' => '儀錶板管理介面',
    'route' => '儀錶板',

    'tab.statistics' => '統計資訊',
    'tab.blacklist' => '黑名單',
    'tab.feedbacks' => '回饋資料',
    'tab.logs' => '系統日誌',
    'tab.safetyguard' => '安全過濾',
    'tab.inspect' => '訊息瀏覽器',

    //Tab_logs
    'colName.Action' => '操作：',
    'colName.Description' => '敘述：',
    'colName.UserID' => '操作者ID：',
    'colName.IP' => 'IP位置：',
    'colName.Timestamp' => '時間戳：',
    'msg.NoRecord' => '無紀錄',
    'filter.StartDate' => '開始：',
    'filter.EndDate' => '結束：',
    'filter.Action' => '操作：',
    'filter.Description' => '敘述：',
    'filter.UserID' => '操作者ID：',
    'filter.IPAddress' => 'IP位置：',

    //Tab_feedback
    'hint.PasteRawDataHere' => '請貼上要轉換的原始資料，也可以將檔案拖移至此。',
    "hint.wip_option"=>"待完成，目前暫無選項",
    'header.ActiveModels' => '已啟用模型',
    'header.InactiveModels' => '已停用模型',
    'header.ModelFilter' => '過濾模型：',
    'header.ExportSetting' => '匯出設定：',

    'button.ExportAndDownload' => '匯出並下載',
    'button.LoadFile' => '載入檔案',
    'button.ConvertAndDownload' => '轉換並下載',
    'msg.MustHave1Model' => '你必須選取至少一個模型來匯出',
    'msg.InvalidJSONFormat' => 'JSON格式錯誤',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"安全過濾系統處於離線狀態",
    'header.create_rule' => '建立過濾規則',
    'header.update_rule' => '更新過濾規則',
    'rule.filter.keyword' => 'Keyword 規則',
    'rule.filter.embedding' => 'Embedding 規則',

    'action.overwrite' => '由系統改寫',
    'action.block' => '封鎖，可選警告',
    'action.warn' => '純警告',
    'action.none' => '無行為',

    'msg.SomethingWentWrong' => '有東西出錯了...',
    'msg.choose_target' => '請選擇模型',
    'msg.create_rule' => '你確定要建立該規則嗎？',
    'msg.delete_rule' => '你確定要刪除該規則嗎？',
    'msg.update_rule' => '你確定要更新該規則嗎？',

    'rule.name' => '規則名稱',
    'rule.description' => '規則敘述',
    'rule.target' => '指定套用規則模型',
    'rule.action' => '規則行為',
    'rule.warning' => '警告提示訊息(可選)',
    'rule.filter.input' => '輸入過濾',
    'rule.filter.output' => '輸出過濾',

    'button.create_rule' => '新增規則',
    'button.create' => '建立',
    'button.cancel' => '取消',
    'button.delete' => '刪除',
    'button.update' => '更新',
];
