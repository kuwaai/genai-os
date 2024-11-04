<?php

return [
    'route' => 'ダッシュボード',

    'header.interface' => 'ダッシュボード管理インターフェース',
    'header.create_rule' => 'フィルタールールを作成',
    'header.update_rule' => 'フィルタールールを更新',

    'tab.statistics' => '統計情報',
    'tab.blacklist' => 'ブラックリスト',
    'tab.feedbacks' => 'フィードバック',
    'tab.logs' => 'システムログ',
    'tab.safetyguard' => 'セーフティガード',
    'tab.inspect' => 'メッセージブラウザ',

    'colName.Action' => '操作：',
    'colName.Description' => '説明：',
    'colName.UserID' => '操作者ID：',
    'colName.IP' => 'IPアドレス：',
    'colName.Timestamp' => 'タイムスタンプ：',

    'filter.StartDate' => '開始：',
    'filter.EndDate' => '終了：',
    'filter.Action' => '操作：',
    'filter.Description' => '説明：',
    'filter.UserID' => '操作者ID：',
    'filter.IPAddress' => 'IPアドレス：',

    'placeholder.PasteRawDataHere' => '変換する生データを貼り付けます。ファイルをドラッグアンドドロップすることもできます。',
    
    'header.ActiveModels' => '有効なモデル',
    'header.InactiveModels' => '無効なモデル',
    'header.ModelFilter' => 'モデルを絞り込む：',
    'header.ExportSetting' => 'エクスポート設定：',

    'button.ExportAndDownload' => 'エクスポートしてダウンロード',
    'button.LoadFile' => 'ファイルを読み込む',
    'button.ConvertAndDownload' => '変換してダウンロード',
    'button.create_rule' => 'ルールを追加',
    'button.create' => '作成',
    'button.cancel' => 'キャンセル',
    'button.delete' => '削除',
    'button.update' => '更新',

    "hint.safety_guard_offline"=>"セーフティガードシステムはオフラインです",
    "hint.wip_option"=>"開発中です。現在、オプションはありません。",

    'action.overwrite' => 'システムで上書き',
    'action.block' => 'ブロック（オプションで警告）',
    'action.warn' => '警告のみ',
    'action.none' => 'なし',

    'msg.SomethingWentWrong' => '何かがうまくいきませんでした...',
    'msg.choose_target' => 'モデルを選択してください',
    'msg.create_rule' => 'このルールを作成しますか？',
    'msg.delete_rule' => 'このルールを削除しますか？',
    'msg.update_rule' => 'このルールを更新しますか？',
    'msg.MustHave1Model' => 'エクスポートするには、少なくとも1つのモデルを選択する必要があります。',
    'msg.InvalidJSONFormat' => 'JSON形式が正しくありません。',
    'msg.NoRecord' => '記録がありません。',

    'rule.filter.keyword' => 'キーワードルール',
    'rule.filter.embedding' => '埋め込みルール',
    'rule.name' => 'ルール名',
    'rule.description' => 'ルール説明',
    'rule.target' => 'ルールを適用するモデルを指定',
    'rule.action' => 'ルールアクション',
    'rule.warning' => '警告メッセージ（オプション）',
    'rule.filter.input' => '入力フィルター',
    'rule.filter.output' => '出力フィルター',
];