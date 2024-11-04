<?php

return [
    'interface.header' => 'ダッシュボード管理インターフェース',
    'route' => 'ダッシュボード',

    'tab.statistics' => '統計情報',
    'tab.blacklist' => 'ブラックリスト',
    'tab.feedbacks' => 'フィードバックデータ',
    'tab.logs' => 'システムログ',
    'tab.safetyguard' => '安全フィルター',
    'tab.inspect' => 'メッセージブラウザ',

    //Tab_logs
    'colName.Action' => '操作：',
    'colName.Description' => '説明：',
    'colName.UserID' => '操作者ID：',
    'colName.IP' => 'IPアドレス：',
    'colName.Timestamp' => 'タイムスタンプ：',
    'msg.NoRecord' => 'レコードがありません',
    'filter.StartDate' => '開始：',
    'filter.EndDate' => '終了：',
    'filter.Action' => '操作：',
    'filter.Description' => '説明：',
    'filter.UserID' => '操作者ID：',
    'filter.IPAddress' => 'IPアドレス：',

    //Tab_feedback
    'hint.PasteRawDataHere' => '変換する生データを貼り付けます。ファイルをドラッグ＆ドロップすることもできます。',
    "hint.wip_option"=>"未完成、現時点ではオプションがありません",
    'header.ActiveModels' => '有効なモデル',
    'header.InactiveModels' => '無効なモデル',
    'header.ModelFilter' => 'モデルフィルター：',
    'header.ExportSetting' => 'エクスポート設定：',

    'button.ExportAndDownload' => 'エクスポートしてダウンロード',
    'button.LoadFile' => 'ファイルを読み込み',
    'button.ConvertAndDownload' => '変換してダウンロード',
    'msg.MustHave1Model' => 'エクスポートするには、少なくとも1つのモデルを選択する必要があります',
    'msg.InvalidJSONFormat' => 'JSON形式が正しくありません',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"安全フィルターシステムはオフラインです",
    'header.create_rule' => 'フィルタールールを作成',
    'header.update_rule' => 'フィルタールールを更新',
    'rule.filter.keyword' => 'キーワードルール',
    'rule.filter.embedding' => '埋め込みルール',

    'action.overwrite' => 'システムによって上書き',
    'action.block' => 'ブロック、警告オプション',
    'action.warn' => '警告のみ',
    'action.none' => 'なし',

    'msg.SomethingWentWrong' => '何かが間違っています...',
    'msg.choose_target' => 'モデルを選択してください',
    'msg.create_rule' => 'このルールを作成しますか？',
    'msg.delete_rule' => 'このルールを削除しますか？',
    'msg.update_rule' => 'このルールを更新しますか？',

    'rule.name' => 'ルール名',
    'rule.description' => 'ルール説明',
    'rule.target' => 'ルールを適用するモデルを指定',
    'rule.action' => 'ルール動作',
    'rule.warning' => '警告メッセージ（オプション）',
    'rule.filter.input' => '入力フィルター',
    'rule.filter.output' => '出力フィルター',

    'button.create_rule' => 'ルールを追加',
    'button.create' => '作成',
    'button.cancel' => 'キャンセル',
    'button.delete' => '削除',
    'button.update' => '更新',
];