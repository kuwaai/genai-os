<?php

return [
    'interface.header' => 'ダッシュボード管理インターフェイス',
    'route' => 'ダッシュボード',

    'tab.statistics' => '統計情報',
    'tab.blacklist' => 'ブラックリスト',
    'tab.feedbacks' => 'フィードバックデータ',
    'tab.logs' => 'システムログ',
    'tab.safetyguard' => 'セーフティフィルタ',
    'tab.inspect' => 'メッセージブラウザ',

    //Tab_logs
    'colName.Action' => '操作:',
    'colName.Description' => '説明:',
    'colName.UserID' => 'オペレータID:',
    'colName.IP' => 'IPの位置:',
    'colName.Timestamp' => 'タイムスタンプ:',
    'msg.NoRecord' => '登録されていません。',
    'filter.StartDate' => '開始:',
    'filter.EndDate' => '終了:',
    'filter.Action' => '操作:',
    'filter.Description' => '説明:',
    'filter.UserID' => 'オペレータID:',
    'filter.IPAddress' => 'IPの位置:',

    //Tab_feedback
    'hint.PasteRawDataHere' => '変換する未加工のデータを貼り付けてください。ファイルをここにドラッグすることもできます。',
    "hint.wip_option"=>"未完成、現時点ではオプションはありません",
    'header.ActiveModels' => '有効化されたモデル',
    'header.InactiveModels' => '無効化されたモデル',
    'header.ModelFilter' => 'モデルのフィルタ:',
    'header.ExportSetting' => 'エクスポートの設定:',

    'button.ExportAndDownload' => 'エクスポートしてダウンロード',
    'button.LoadFile' => 'ファイルをロード',
    'button.ConvertAndDownload' => '変換してダウンロード',
    'msg.MustHave1Model' => 'エクスポートするモデルを少なくとも1つ選択する必要があります',
    'msg.InvalidJSONFormat' => 'JSONフォーマットが誤っています',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"セーフティフィルタシステムはオフラインです",
    'header.create_rule' => 'フィルタリングルールの作成',
    'header.update_rule' => 'フィルタリングルールの更新',
    'rule.filter.keyword' => 'キーワードルール',
    'rule.filter.embedding' => '埋め込みルール',

    'action.overwrite' => 'システムによって書き換え',
    'action.block' => 'ブロック、警告を選択可能',
    'action.warn' => '警告のみ',
    'action.none' => '操作なし',

    'msg.SomethingWentWrong' => '何かがうまくいきませんでした...',
    'msg.choose_target' => 'モデルを選択してください',
    'msg.create_rule' => 'このルールを作成しますか?',
    'msg.delete_rule' => 'このルールを削除しますか?',
    'msg.update_rule' => 'このルールを更新しますか?',

    'rule.name' => 'ルール名',
    'rule.description' => 'ルールの説明',
    'rule.target' => 'ルールを適用するモデルを指定',
    'rule.action' => 'ルールの動作',
    'rule.warning' => '警告プロンプトメッセージ(オプション)',
    'rule.filter.input' => '入力フィルタ',
    'rule.filter.output' => '出力フィルタ',

    'button.create_rule' => 'ルールを追加',
    'button.create' => '作成',
    'button.cancel' => 'キャンセル',
    'button.delete' => '削除',
    'button.update' => '更新',
];