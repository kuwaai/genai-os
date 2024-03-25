<?php

return [
    'interface.header' => 'ダッシュボード管理インターフェース',
    'route' => 'ダッシュボード',

    'tab.statistics' => '統計情報',
    'tab.blacklist' => 'ブラックリスト',
    'tab.feedbacks' => 'フィードバック',
    'tab.logs' => 'ログ',
    'tab.safetyguard' => 'セーフティガード',
    'tab.inspect' => 'メッセージブラウザ',

    //Tab_logs
    'colName.Action' => 'アクション：',
    'colName.Description' => '説明：',
    'colName.UserID' => 'ユーザーID：',
    'colName.IP' => 'IPアドレス：',
    'colName.Timestamp' => 'タイムスタンプ：',
    'msg.NoRecord' => '記録なし',
    'filter.StartDate' => '開始：',
    'filter.EndDate' => '終了：',
    'filter.Action' => 'アクション：',
    'filter.Description' => '説明：',
    'filter.UserID' => 'ユーザーID：',
    'filter.IPAddress' => 'IPアドレス：',

    //Tab_feedback
    'hint.PasteRawDataHere' => '変換する元のデータを貼り付けるか、ここにファイルをドラッグアンドドロップしてください。',
    "hint.wip_option" => "作業中のオプションはありません",
    'header.ActiveModels' => 'アクティブモデル',
    'header.InactiveModels' => '非アクティブモデル',
    'header.ModelFilter' => 'モデルフィルター：',
    'header.ExportSetting' => 'エクスポート設定：',

    'button.ExportAndDownload' => 'エクスポートしてダウンロード',
    'button.LoadFile' => 'ファイルを読み込む',
    'button.ConvertAndDownload' => '変換してダウンロード',
    'msg.MustHave1Model' => 'エクスポートするモデルを少なくとも1つ選択する必要があります',
    'msg.InvalidJSONFormat' => 'JSON形式が無効です',

    //Tab_SafetyGuard
    "hint.safety_guard_offline" => "セーフティガードシステムはオフラインです",
    'header.create_rule' => 'フィルタールールの作成',
    'header.update_rule' => 'フィルタールールの更新',
    'rule.filter.keyword' => 'キーワードフィルタールール',
    'rule.filter.embedding' => '埋め込みフィルタールール',

    'action.overwrite' => 'システムによる上書き',
    'action.block' => 'ブロック、オプションで警告',
    'action.warn' => '警告のみ',
    'action.none' => 'なし',

    'msg.SomethingWentWrong' => '何かがうまくいきませんでした...',
    'msg.choose_target' => 'モデルを選択してください',
    'msg.create_rule' => 'このルールを作成してもよろしいですか？',
    'msg.delete_rule' => 'このルールを削除してもよろしいですか？',
    'msg.update_rule' => 'このルールを更新してもよろしいですか？',

    'rule.name' => 'ルール名',
    'rule.description' => 'ルールの説明',
    'rule.target' => 'ルールを適用するモデルを指定',
    'rule.action' => 'ルールのアクション',
    'rule.warning' => '警告メッセージ（オプション）',
    'rule.filter.input' => '入力フィルター',
    'rule.filter.output' => '出力フィルター',

    'button.create_rule' => 'ルールを作成',
    'button.create' => '作成',
    'button.cancel' => 'キャンセル',
    'button.delete' => '削除',
    'button.update' => '更新',
];
