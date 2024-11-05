<?php

return [
    'route' => 'ダッシュボード',

    'header.create_rule' => 'フィルタルールを作成する',
    'header.update_rule' => 'フィルタルールを更新する',

    'tab.statistics' => '統計情報',
    'tab.blacklist' => 'ブラックリスト',
    'tab.feedbacks' => 'フィードバック',
    'tab.logs' => 'システムログ',
    'tab.safetyguard' => '安全フィルタ',
    'tab.inspect' => 'メッセージブラウザ',

    'colName.Action' => 'アクション：',
    'colName.Description' => '説明：',
    'colName.UserID' => '操作者ID：',
    'colName.IP' => 'IPアドレス：',
    'colName.Timestamp' => 'タイムスタンプ：',

    'filter.StartDate' => '開始：',
    'filter.EndDate' => '終了：',
    'filter.Action' => 'アクション：',
    'filter.Description' => '説明：',
    'filter.UserID' => '操作者ID：',
    'filter.IPAddress' => 'IPアドレス：',

    'placeholder.PasteRawDataHere' => '変換する生データを貼り付けるか、ファイルをここにドラッグアンドドロップしてください。',

    'header.ActiveModels' => '有効なモデル',
    'header.InactiveModels' => '無効なモデル',
    'header.ModelFilter' => 'モデルをフィルタする：',
    'header.ExportSetting' => 'エクスポート設定：',

    'button.ExportAndDownload' => 'エクスポートしてダウンロード',
    'button.LoadFile' => 'ファイルを読み込む',
    'button.ConvertAndDownload' => '変換してダウンロード',
    'button.create_rule' => 'ルールを作成する',
    'button.create' => '作成',
    'button.cancel' => 'キャンセル',
    'button.delete' => '削除',
    'button.update' => '更新',

    "hint.safety_guard_offline"=>"安全フィルタシステムはオフラインです",
    "hint.wip_option"=>"開発中、現在オプションはありません",

    'action.overwrite' => 'システムで書き換える',
    'action.block' => 'ブロック、警告オプション付き',
    'action.warn' => '警告のみ',
    'action.none' => 'なし',

    'msg.SomethingWentWrong' => '何かがうまくいきませんでした...',
    'msg.choose_target' => 'モデルを選択してください',
    'msg.create_rule' => 'このルールを作成してもよろしいですか？',
    'msg.delete_rule' => 'このルールを削除してもよろしいですか？',
    'msg.update_rule' => 'このルールを更新してもよろしいですか？',
    'msg.MustHave1Model' => 'エクスポートするには、少なくとも1つのモデルを選択する必要があります',
    'msg.InvalidJSONFormat' => 'JSON形式が不正です',
    'msg.NoRecord' => 'レコードがありません',

    'rule.filter.keyword' => 'キーワードルール',
    'rule.filter.embedding' => '埋め込みルール',
    'rule.name' => 'ルール名',
    'rule.description' => 'ルール説明',
    'rule.target' => 'ルールを適用するモデルを指定する',
    'rule.action' => 'ルールアクション',
    'rule.warning' => '警告メッセージ（オプション）',
    'rule.filter.input' => '入力フィルタ',
    'rule.filter.output' => '出力フィルタ',
];