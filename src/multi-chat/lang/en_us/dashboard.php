<?php

return [
    'interface.header' => 'Dashboard Management Interface',
    'route' => 'Dashboard',

    'tab.statistics' => 'Statistics',
    'tab.blacklist' => 'Blacklist',
    'tab.feedbacks' => 'Feedbacks',
    'tab.logs' => 'System Logs',
    'tab.safetyguard' => 'Safety Filter',
    'tab.inspect' => 'Message Browser',

    //Tab_logs
    'colName.Action' => 'Action:',
    'colName.Description' => 'Description:',
    'colName.UserID' => 'Operator ID:',
    'colName.IP' => 'IP Address:',
    'colName.Timestamp' => 'Timestamp:',
    'msg.NoRecord' => 'No records found',
    'filter.StartDate' => 'Start:',
    'filter.EndDate' => 'End:',
    'filter.Action' => 'Action:',
    'filter.Description' => 'Description:',
    'filter.UserID' => 'Operator ID:',
    'filter.IPAddress' => 'IP Address:',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Please paste the raw data you want to convert, you can also drag the file here.',
    "hint.wip_option"=>"Work in progress, no options available currently",
    'header.ActiveModels' => 'Active Models',
    'header.InactiveModels' => 'Inactive Models',
    'header.ModelFilter' => 'Filter Model:',
    'header.ExportSetting' => 'Export Settings:',

    'button.ExportAndDownload' => 'Export and Download',
    'button.LoadFile' => 'Load File',
    'button.ConvertAndDownload' => 'Convert and Download',
    'msg.MustHave1Model' => 'You must select at least one model to export',
    'msg.InvalidJSONFormat' => 'Invalid JSON format',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"Safety filter system is offline",
    'header.create_rule' => 'Create Filter Rule',
    'header.update_rule' => 'Update Filter Rule',
    'rule.filter.keyword' => 'Keyword Rule',
    'rule.filter.embedding' => 'Embedding Rule',

    'action.overwrite' => 'Overwrite by system',
    'action.block' => 'Block, optional warning',
    'action.warn' => 'Warning only',
    'action.none' => 'No action',

    'msg.SomethingWentWrong' => 'Something went wrong...',
    'msg.choose_target' => 'Please choose a model',
    'msg.create_rule' => 'Are you sure you want to create this rule?',
    'msg.delete_rule' => 'Are you sure you want to delete this rule?',
    'msg.update_rule' => 'Are you sure you want to update this rule?',

    'rule.name' => 'Rule Name',
    'rule.description' => 'Rule Description',
    'rule.target' => 'Specify the model to apply the rule',
    'rule.action' => 'Rule Action',
    'rule.warning' => 'Warning message (optional)',
    'rule.filter.input' => 'Input Filter',
    'rule.filter.output' => 'Output Filter',

    'button.create_rule' => 'Add Rule',
    'button.create' => 'Create',
    'button.cancel' => 'Cancel',
    'button.delete' => 'Delete',
    'button.update' => 'Update',
];