<?php

return [
    'interface.header' => '仪表板管理界面',
    'route' => '仪表板',

    'tab.statistics' => '统计信息',
    'tab.blacklist' => '黑名单',
    'tab.feedbacks' => '反馈数据',
    'tab.logs' => '系统日志',
    'tab.safetyguard' => '安全过滤',
    'tab.inspect' => '信息浏览器',

    //Tab_logs
    'colName.Action' => '操作：',
    'colName.Description' => '叙述：',
    'colName.UserID' => '操作者ID：',
    'colName.IP' => 'IP位置：',
    'colName.Timestamp' => '时间戳：',
    'msg.NoRecord' => '无记录',
    'filter.StartDate' => '开始：',
    'filter.EndDate' => '结束：',
    'filter.Action' => '操作：',
    'filter.Description' => '叙述：',
    'filter.UserID' => '操作者ID：',
    'filter.IPAddress' => 'IP位置：',

    //Tab_feedback
    'hint.PasteRawDataHere' => '请粘贴要转换的原始数据，也可以将文件拖移至此。',
    "hint.wip_option"=>"待完成，目前暂无选项",
    'header.ActiveModels' => '已启用模型',
    'header.InactiveModels' => '已停用模型',
    'header.ModelFilter' => '过滤模型：',
    'header.ExportSetting' => '导出设置：',

    'button.ExportAndDownload' => '导出并下载',
    'button.LoadFile' => '载入文件',
    'button.ConvertAndDownload' => '转换并下载',
    'msg.MustHave1Model' => '你必须选取至少一个模型来导出',
    'msg.InvalidJSONFormat' => 'JSON格式错误',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"安全过滤系统处于离线状态",
    'header.create_rule' => '创建过滤规则',
    'header.update_rule' => '更新过滤规则',
    'rule.filter.keyword' => '关键字规则',
    'rule.filter.embedding' => 'Embedding 规则',

    'action.overwrite' => '由系统改写',
    'action.block' => '封锁，可选警告',
    'action.warn' => '纯警告',
    'action.none' => '无行为',

    'msg.SomethingWentWrong' => '有东西出错了...',
    'msg.choose_target' => '请选择模型',
    'msg.create_rule' => '你确定要建立该规则吗？',
    'msg.delete_rule' => '你确定要删除该规则吗？',
    'msg.update_rule' => '你确定要更新该规则吗？',

    'rule.name' => '规则名称',
    'rule.description' => '规则叙述',
    'rule.target' => '指定套用规则模型',
    'rule.action' => '规则行为',
    'rule.warning' => '警告提示信息(可选)',
    'rule.filter.input' => '输入过滤',
    'rule.filter.output' => '输出过滤',

    'button.create_rule' => '新增规则',
    'button.create' => '建立',
    'button.cancel' => '取消',
    'button.delete' => '删除',
    'button.update' => '更新',
];