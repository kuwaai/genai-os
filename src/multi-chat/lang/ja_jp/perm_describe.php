<?php

return [
    'Profile_update_name' => '自分のアカウント名を更新する権限',
    'Profile_update_email' => '自分の電子メールアドレスを更新する権限',
    'Profile_update_password' => '自分のパスワードを更新する権限',
    'Profile_update_external_api_token' => 'サイトに保存されている外部APIキーを更新する権限',
    'Profile_delete_account' => '自分のアカウントを削除する権限',
    'Profile_read_api_token' => 'サイトAPIキーを読む権限',
    'Profile_update_api_token' => 'サイトAPIキーを更新する権限',

    'Room_update_detail_feedback' => '回答に詳細なフィードバックタグを与える',
    'Room_read_access_to_api' => 'サイトAPIを使用する権限',
    'Room_update_send_message' => '会話にメッセージを送信する権限（作成には影響しません）',
    'Room_update_new_chat' => '会話を作成する権限',
    'Room_update_feedback' => 'モデルの使用に関するフィードバックを送信する権限',
    'Room_delete_chatroom' => '会話を削除する権限',
    'Room_read_export_chat' => 'チャットログをエクスポートする権限',
    'Room_update_import_chat' => 'チャットログをインポートする権限（会話の作成権限は依然として必要です）',
    'Room_update_react_message' => 'メッセージに対して追加操作ボタン（引用、翻訳など）を使用する権限',
    'Room_update_ignore_upload_constraint' => 'システム設定のすべてのファイルアップロード制限を無視し、任意のファイルをアップロードできます',

    'Dashboard_read_statistics' => '統計情報にアクセスする',
    'Dashboard_read_blacklist' => 'ブラックリストにアクセスする',
    'Dashboard_read_feedbacks' => 'フィードバックデータにアクセスする',
    'Dashboard_read_logs' => 'システムログにアクセスする',
    'Dashboard_read_safetyguard' => '安全フィルターにアクセスする',
    'Dashboard_read_inspect' => 'メッセージブラウザにアクセスする',

    'Store_create_community_bot' => 'コミュニティボットを作成する権限',
    'Store_create_private_bot' => 'プライベートボットを作成する権限',
    'Store_create_group_bot' => 'グループボットを作成する権限',
    'Store_update_modify_bot' => '作成したボットを変更する権限',
    'Store_delete_delete_bot' => '作成したボットを削除する権限',
    'Store_read_discover_community_bots' => 'コミュニティボットを発見する権限',
    'Store_read_discover_system_bots' => 'システムボットを発見する権限',
    'Store_read_discover_private_bots' => '作成したボットを発見する権限',
    'Store_read_discover_group_bots' => 'グループボットを発見する権限',
    'Store_read_any_modelfile' => '任意のmodelfileを読む権限',
    
    'Cloud_read_my_files' => '個人ディレクトリのファイルを読む権限',
    'Cloud_update_upload_files' => '個人ディレクトリにファイルをアップロードする権限',
    'Cloud_delete_my_files' => '個人ディレクトリのファイルを削除する権限'
];