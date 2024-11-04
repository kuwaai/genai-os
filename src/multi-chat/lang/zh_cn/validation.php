<?php

return [
    'accepted' => ' :attribute 欄位必須接受。',
    'accepted_if' => '當 :other 為 :value 時， :attribute 欄位必須接受。',
    'active_url' => ' :attribute 欄位必須是有效的網址。',
    'after' => ' :attribute 欄位必須是 :date 之後的日期。',
    'after_or_equal' => ' :attribute 欄位必須是 :date 之後或等於該日期的日期。',
    'alpha' => ' :attribute 欄位只能包含字母。',
    'alpha_dash' => ' :attribute 欄位只能包含字母、數字、破折號和下劃線。',
    'alpha_num' => ' :attribute 欄位只能包含字母和數字。',
    'array' => ' :attribute 欄位必須是陣列。',
    'ascii' => ' :attribute 欄位只能包含單位元組的字母數字字元和符號。',
    'before' => ' :attribute 欄位必須是 :date 之前的日期。',
    'before_or_equal' => ' :attribute 欄位必須是 :date 之前或等於該日期的日期。',
    'between' => [
        'array' => ' :attribute 欄位必須有 :min 到 :max 個項目。',
        'file' => ' :attribute 欄位必須在 :min 到 :max KB之間。',
        'numeric' => ' :attribute 欄位必須在 :min 到 :max 之間。',
        'string' => ' :attribute 欄位必須在 :min 到 :max 個字元之間。',
    ],
    'boolean' => ' :attribute 欄位必須是 true 或 false。',
    'can' => ' :attribute 欄位包含未經授權的值。',
    'confirmed' => ' :attribute 欄位確認不符。',
    'current_password' => '密碼不正確。',
    'date' => ' :attribute 欄位必須是有效的日期。',
    'date_equals' => ' :attribute 欄位必須等於 :date。',
    'date_format' => ' :attribute 欄位必須符合格式 :format。',
    'decimal' => ' :attribute 欄位必須有 :decimal 小數位。',
    'declined' => ' :attribute 欄位必須被拒絕。',
    'declined_if' => '當 :other 為 :value 時， :attribute 欄位必須被拒絕。',
    'different' => ' :attribute 欄位和 :other 必須不同。',
    'digits' => ' :attribute 欄位必須是 :digits 位元數字。',
    'digits_between' => ' :attribute 欄位必須在 :min 到 :max 位元數字之間。',
    'dimensions' => ' :attribute 欄位具有無效的圖片尺寸。',
    'distinct' => ' :attribute 欄位具有重複的值。',
    'doesnt_end_with' => ' :attribute 欄位不能以以下之一結尾： :values。',
    'doesnt_start_with' => ' :attribute 欄位不能以以下之一開頭： :values。',
    'email' => ' :attribute 欄位必須是有效的電子郵件地址。',
    'ends_with' => ' :attribute 欄位必須以以下之一結尾： :values。',
    'enum' => '所選的 :attribute 無效。',
    'exists' => '所選的 :attribute 無效。',
    'extensions' => ' :attribute 欄位必須具有以下之一的擴充名稱： :values。',
    'file' => ' :attribute 欄位必須是檔案。',
    'filled' => ' :attribute 欄位必須有值。',
    'gt' => [
        'array' => ' :attribute 欄位必須有超過 :value 個項目。',
        'file' => ' :attribute 欄位必須大於 :value KB。',
        'numeric' => ' :attribute 欄位必須大於 :value。',
        'string' => ' :attribute 欄位必須大於 :value 字元。',
    ],
    'gte' => [
        'array' => ' :attribute 欄位必須有 :value 個項目或更多。',
        'file' => ' :attribute 欄位必須大於或等於 :value KB。',
        'numeric' => ' :attribute 欄位必須大於或等於 :value。',
        'string' => ' :attribute 欄位必須大於或等於 :value 字元。',
    ],

    'hex_color' => '字段 :attribute 必須是有效的十六進位顏色。',
    'image' => '欄位 :attribute 必須是圖像。',
    'in' => '所選的 :attribute 無效。',
    'in_array' => '字段 :attribute 必須存在於 :other 中。',
    'integer' => '欄位 :attribute 必須是整數。',
    'ip' => '欄位 :attribute 必須是有效的 IP 位址。',
    'ipv4' => '欄位 :attribute 必須是有效的 IPv4 位址。',
    'ipv6' => '欄位 :attribute 必須是有效的 IPv6 位址。',
    'json' => '字段 :attribute 必須是有效的 JSON 字串。',
    'lowercase' => '欄位 :attribute 必須為小寫。',
    'lt' => [
        'array' => '欄位 :attribute 必須少於 :value 項目。',
        'file' => '字段 :attribute 必須小於 :value 千字節。',
        'numeric' => '字段 :attribute 必須小於 :value。',
        'string' => '欄位 :attribute 必須少於 :value 字元。',
    ],
    'lte' => [
        'array' => '欄位 :attribute 不得多於 :value 項目。',
        'file' => '欄位 :attribute 必須小於或等於 :value 千字節。',
        'numeric' => '欄位 :attribute 必須小於或等於 :value。',
        'string' => '欄位 :attribute 必須小於或等於 :value 字元。',
    ],
    'mac_address' => '欄位 :attribute 必須是有效的 MAC 位址。',
    'max' => [
        'array' => '欄位 :attribute 不得多於 :max 個項目。',
        'file' => '字段 :attribute 不得大於 :max 千字節。',
        'numeric' => '欄位 :attribute 不得大於 :max。',
        'string' => '欄位 :attribute 不得多於 :max 個字元。',
    ],
    'max_digits' => '欄位 :attribute 不得超過 :max 位元數。',
    'mimes' => '欄位 :attribute 必須是類型為 :values 的檔案。',
    'mimetypes' => '欄位 :attribute 必須是類型為 :values 的檔案。',
    'min' => [
        'array' => '欄位 :attribute 必須至少有 :min 個項目。',
        'file' => '字段 :attribute 必須至少為 :min 千字節。',
        'numeric' => '欄位 :attribute 必須至少為 :min。',
        'string' => '欄位 :attribute 必須至少有 :min 個字元。',
    ],
    'min_digits' => '欄位 :attribute 必須至少有 :min 位元數字。',
    'missing' => '欄位 :attribute 必須缺失。',
    'missing_if' => '當 :other 為 :value 時，欄位 :attribute 必須缺失。',
    'missing_unless' => '除非 :other 為 :value，否則欄位 :attribute 必須缺失。',
    'missing_with' => '當 :values 存在時，欄位 :attribute 必須缺失。',
    'missing_with_all' => '當 :values 都存在時，欄位 :attribute 必須缺失。',
    'multiple_of' => '字段 :attribute 必須是 :value 的倍數。',
    'not_in' => '所選擇的 :attribute 無效。',
    'not_regex' => '字段 :attribute 格式無效。',
    'numeric' => '字段 :attribute 必須是數字。',
    'password' => [
        'letters' => '該 :attribute 欄位必須包含至少一個字母。',
        'mixed' => '該 :attribute 欄位必須包含至少一個大寫字母和一個小寫字母。',
        'numbers' => '該 :attribute 欄位必須包含至少一個數字。',
        'symbols' => '該 :attribute 欄位必須包含至少一個符號。',
        'uncompromised' => '提供的 :attribute 已出現在數據洩露中。 請選擇不同的 :attribute。',
    ],
    'present' => '該 :attribute 欄位必須存在。',
    'present_if' => '當 :other 是 :value 時，該 :attribute 欄位必須存在。',
    'present_unless' => '除非 :other 是 :value，否則該 :attribute 欄位必須存在。',
    'present_with' => '當 :values 存在時，該 :attribute 欄位必須存在。',
    'present_with_all' => '當 :values 都存在時，該 :attribute 欄位必須存在。',
    'prohibited' => '該 :attribute 欄位是禁止的。',
    'prohibited_if' => '當 :other 是 :value 時，該 :attribute 欄位是禁止的。',
    'prohibited_unless' => '除非 :other 在 :values 中，否則該 :attribute 欄位是禁止的。',
    'prohibits' => '該 :attribute 欄位禁止 :other 存在。',
    'regex' => '該 :attribute 欄位格式無效。',
    'required' => '該 :attribute 欄位是必需的。',
    'required_array_keys' => '該 :attribute 欄位必須包含下列項目：:values。',
    'required_if' => '當 :other 是 :value 時，該 :attribute 欄位是必需的。',
    'required_if_accepted' => '當 :other 被接受時，該 :attribute 欄位是必需的。',
    'required_unless' => '除非 :other 在 :values 中，否則該 :attribute 欄位是必要的。',
    'required_with' => '當 :values 存在時，該 :attribute 欄位是必需的。',
    'required_with_all' => '當 :values 都存在時，該 :attribute 欄位是必需的。',
    'required_without' => '當 :values 不存在時，該 :attribute 欄位是必需的。',
    'required_without_all' => '當 :values 都不存在時，該 :attribute 欄位是必需的。',
    'same' => '該 :attribute 欄位必須與 :other 相符。',
    'size' => [
        'array' => '該 :attribute 欄位必須包含 :size 個項目。',
        'file' => '該 :attribute 欄位必須為 :size 千字節。',
        'numeric' => '該 :attribute 欄位必須為 :size。',
        'string' => '該 :attribute 欄位必須為 :size 字元。',
    ],
    'starts_with' => '該 :attribute 欄位必須以以下之一開頭：:values。',
    'string' => '該 :attribute 欄位必須為字符串。',
    'timezone' => '該 :attribute 欄位必須是有效的時區。',
    'unique' => '該 :attribute 已經被使用。',
    'uploaded' => '無法上傳該 :attribute。',
    'uppercase' => '該 :attribute 欄位必須為大寫。',
    'url' => '該 :attribute 欄位必須為有效的 URL。',
    'ulid' => '該 :attribute 欄位必須為有效的 ULID。',
    'uuid' => '該 :attribute 欄位必須為有效的 UUID。',
    'email_domain' => [
        'invalid' => '該 :attribute 必須是有效的電子郵件網域。',
    ],
];