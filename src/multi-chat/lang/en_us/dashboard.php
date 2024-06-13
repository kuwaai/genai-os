<?php

return [
    'interface.header' => 'Dashboard Management Interface',
    'route' => 'Dashboard',

    'tab.statistics' => 'Statistics',
    'tab.blacklist' => 'Black List',
    'tab.feedbacks' => 'Feedbacks',
    'tab.logs' => 'System Logs',
    'tab.safetyguard' => 'SafetyGuard',
    'tab.inspect' => 'Message Viewer',

    //Tab_logs
    'colName.Action' => 'Action：',
    'colName.Description' => 'Description：',
    'colName.UserID' => 'Operator ID：',
    'colName.IP' => 'IP：',
    'colName.Timestamp' => 'Timestamp：',
    'msg.NoRecord' => 'No Record',
    'filter.StartDate' => 'Start：',
    'filter.EndDate' => 'End：',
    'filter.Action' => 'Action：',
    'filter.Description' => 'Description：',
    'filter.UserID' => 'Operator ID：',
    'filter.IPAddress' => 'IP：',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Please paste raw data here for conversion, or simply drag and drop file here.',
    "hint.wip_option"=>"Work in progress, currently no options available",
    'header.ActiveModels' => 'Active Models',
    'header.InactiveModels' => 'Inactive Models',
    'header.ModelFilter' => 'Models Filter：',
    'header.ExportSetting' => 'Export Setting：',

    'button.ExportAndDownload' => 'Export and Download',
    'button.LoadFile' => 'Load File',
    'button.ConvertAndDownload' => 'Convert and Download',
    'msg.MustHave1Model' => 'You must select at least one model to export',
    'msg.InvalidJSONFormat' => 'Invalid JSON format',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"SafetyGuard is offline",
    'header.create_rule' => 'Create Filter Rule',
    'header.update_rule' => 'Update Filter Rule',
    'rule.filter.keyword' => 'Keyword Filter',
    'rule.filter.embedding' => 'Embedding Filter',

    'action.overwrite' => 'System Override',
    'action.block' => 'Block, Warning optional',
    'action.warn' => 'Warning only',
    'action.none' => 'No action',

    'msg.SomethingWentWrong' => 'Something went wrong...',
    'msg.choose_target' => 'Please choose target',
    'msg.create_rule' => 'Are you sure you want to create this rule？',
    'msg.delete_rule' => 'Are you sure you want to delete this rule？',
    'msg.update_rule' => 'Are you sure you want to update this rule？',

    'rule.name' => 'Rule Name',
    'rule.description' => 'Rule Description',
    'rule.target' => 'Specify Target Model',
    'rule.action' => 'Rule Action',
    'rule.warning' => 'Warning Prompt (optional)',
    'rule.filter.input' => 'Input Filter',
    'rule.filter.output' => 'Output Filter',

    'button.create_rule' => 'Add Rule',
    'button.create' => 'Create',
    'button.cancel' => 'Cancel',
    'button.delete' => 'Delete',
    'button.update' => 'Update',
];