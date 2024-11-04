<?php

return [
    'route' => 'Dashboard',

    'header.interface' => 'Dashboard Management Interface',
    'header.create_rule' => 'Create Filter Rule',
    'header.update_rule' => 'Update Filter Rule',

    'tab.statistics' => 'Statistics',
    'tab.blacklist' => 'Blacklist',
    'tab.feedbacks' => 'Feedbacks',
    'tab.logs' => 'System Logs',
    'tab.safetyguard' => 'Safety Filter',
    'tab.inspect' => 'Message Browser',

    'colName.Action' => 'Action:',
    'colName.Description' => 'Description:',
    'colName.UserID' => 'Operator ID:',
    'colName.IP' => 'IP Address:',
    'colName.Timestamp' => 'Timestamp:',

    'filter.StartDate' => 'Start:',
    'filter.EndDate' => 'End:',
    'filter.Action' => 'Action:',
    'filter.Description' => 'Description:',
    'filter.UserID' => 'Operator ID:',
    'filter.IPAddress' => 'IP Address:',

    'placeholder.PasteRawDataHere' => 'Paste raw data to be converted here, or drag the file to this area.',
    
    'header.ActiveModels' => 'Enabled Models',
    'header.InactiveModels' => 'Disabled Models',
    'header.ModelFilter' => 'Filter Models:',
    'header.ExportSetting' => 'Export Settings:',

    'button.ExportAndDownload' => 'Export and Download',
    'button.LoadFile' => 'Load File',
    'button.ConvertAndDownload' => 'Convert and Download',
    'button.create_rule' => 'Add Rule',
    'button.create' => 'Create',
    'button.cancel' => 'Cancel',
    'button.delete' => 'Delete',
    'button.update' => 'Update',

    "hint.safety_guard_offline"=>"Safety filter system is offline",
    "hint.wip_option"=>"Work in progress, no options currently available",

    'action.overwrite' => 'Overwrite by system',
    'action.block' => 'Block, optional warning',
    'action.warn' => 'Warning only',
    'action.none' => 'No action',

    'msg.SomethingWentWrong' => 'Something went wrong...',
    'msg.choose_target' => 'Please choose a model',
    'msg.create_rule' => 'Are you sure you want to create this rule?',
    'msg.delete_rule' => 'Are you sure you want to delete this rule?',
    'msg.update_rule' => 'Are you sure you want to update this rule?',
    'msg.MustHave1Model' => 'You must select at least one model to export',
    'msg.InvalidJSONFormat' => 'Invalid JSON format',
    'msg.NoRecord' => 'No records',

    'rule.filter.keyword' => 'Keyword Rule',
    'rule.filter.embedding' => 'Embedding Rule',
    'rule.name' => 'Rule Name',
    'rule.description' => 'Rule Description',
    'rule.target' => 'Specify models to apply the rule',
    'rule.action' => 'Rule Action',
    'rule.warning' => 'Warning message (optional)',
    'rule.filter.input' => 'Input Filter',
    'rule.filter.output' => 'Output Filter',
];