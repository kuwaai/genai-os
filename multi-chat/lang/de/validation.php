<?php

return [
    /*
     |------------------------------------------------- -------------------------
     | Validation Language Lines
     |------------------------------------------------- -------------------------
     |
     | The following language lines contain the default error messages used by
     | the validator class. Some of these rules have multiple versions such
     | as the size rules. Feel free to tweak each of these messages here.
     |
     */

    'accepted' => 'Das Feld :attribute muss akzeptiert werden.',
    'accepted_if' => 'Das Feld :attribute muss akzeptiert werden, wenn :other den Wert :value hat.',
    'active_url' => 'Das Feld :attribute muss eine gültige URL sein.',
    'after' => 'Das Feld :attribute muss ein Datum nach :date sein.',
    'after_or_equal' => 'Das Feld :attribute muss ein Datum nach oder gleich :date sein.',
    'alpha' => 'Das Feld :attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => 'Das Feld :attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num' => 'Das Feld :attribute darf nur Buchstaben und Zahlen enthalten.',
    'array' => 'Das Feld :attribute muss ein Array sein.',
    'ascii' => 'Das Feld :attribute darf nur ASCII-Zeichen enthalten.',
    'before' => 'Das Feld :attribute muss ein Datum vor :date sein.',
    'before_or_equal' => 'Das Feld :attribute muss ein Datum vor oder gleich :date sein.',
    'between' => [
        'array' => 'Das Feld :attribute muss zwischen :min und :max Elemente haben.',
        'file' => 'Das Feld :attribute muss zwischen :min und :max Kilobytes groß sein.',
        'numeric' => 'Das Feld :attribute muss zwischen :min und :max liegen.',
        'string' => 'Das Feld :attribute muss zwischen :min und :max Zeichen lang sein.',
    ],
    'boolean' => 'Das Feld :attribute muss true oder false sein.',
    'can' => 'Das Feld :attribute enthält einen unautorisierten Wert.',
    'confirmed' => 'Die Bestätigung des Feldes :attribute stimmt nicht überein.',
    'current_password' => 'Das Passwort ist nicht korrekt.',
    'date' => 'Das Feld :attribute muss ein gültiges Datum sein.',
    'date_equals' => 'Das Feld :attribute muss dem Datum :date entsprechen.',
    'date_format' => 'Das Feld :attribute entspricht nicht dem Format :format.',
    'decimal' => 'Das Feld :attribute muss :decimal Dezimalstellen haben.',
    'declined' => 'Das Feld :attribute muss abgelehnt werden.',
    'declined_if' => 'Das Feld :attribute muss abgelehnt werden, wenn :other den Wert :value hat.',
    'different' => 'Die Felder :attribute und :other müssen sich unterscheiden.',
    'digits' => 'Das Feld :attribute muss :digits Stellen haben.',
    'digits_between' => 'Das Feld :attribute muss zwischen :min und :max Stellen haben.',
    'dimensions' => 'Das Feld :attribute hat ungültige Bildabmessungen.',
    'distinct' => 'Das Feld :attribute hat einen doppelten Wert.',
    'doesnt_end_with' => 'Das Feld :attribute darf nicht mit einem der folgenden enden: :values.',
    'doesnt_start_with' => 'Das Feld :attribute darf nicht mit einem der folgenden beginnen: :values.',
    'email' => 'Das Feld :attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with' => 'Das Feld :attribute muss mit einem der folgenden enden: :values.',
    'enum' => 'Die ausgewählte Option für :attribute ist ungültig.',
    'exists' => 'Die ausgewählte Option für :attribute ist ungültig.',
    'extensions' => 'Das Feld :attribute muss eine der folgenden Dateierweiterungen haben: :values.',
    'file' => 'Das Feld :attribute muss eine Datei sein.',
    'filled' => 'Das Feld :attribute muss einen Wert enthalten.',
    'gt' => [
        'array' => 'Das Feld :attribute muss mehr als :value Elemente haben.',
        'file' => 'Das Feld :attribute muss größer als :value Kilobytes sein.',
        'numeric' => 'Das Feld :attribute muss größer als :value sein.',
        'string' => 'Das Feld :attribute muss mehr als :value Zeichen haben.',
    ],
    'gte' => [
        'array' => 'Das Feld :attribute muss :value Elemente oder mehr haben.',
        'file' => 'Das Feld :attribute muss größer oder gleich :value Kilobytes sein.',
        'numeric' => 'Das Feld :attribute muss größer oder gleich :value sein.',
        'string' => 'Das Feld :attribute muss größer oder gleich :value Zeichen haben.',
    ],

    'hex_color' => 'Das Feld :attribute muss eine gültige Hex-Farbe sein. ',
    'image' => 'Das Feld :attribute muss ein Bild sein. ',
    'in' => 'Die ausgewählte :attribute ist ungültig. ',
    'in_array' => 'Das Feld :attribute muss in :other vorhanden sein. ',
    'integer' => 'Das Feld :attribute muss eine Ganzzahl sein. ',
    'ip' => 'Das Feld :attribute muss eine gültige IP-Adresse sein. ',
    'ipv4' => 'Das Feld :attribute muss eine gültige IPv4-Adresse sein. ',
    'ipv6' => 'Das Feld :attribute muss eine gültige IPv6-Adresse sein. ',
    'json' => 'Das Feld :attribute muss eine gültige JSON-Zeichenfolge sein. ',
    'lowercase' => 'Das Feld :attribute muss klein geschrieben sein. ',
    'lt' => [
        'array' => 'Das Feld :attribute muss weniger als :value Elemente haben. ',
        'file' => 'Das Feld :attribute muss kleiner als :value Kilobyte sein. ',
        'numeric' => 'Das Feld :attribute muss kleiner als :value sein. ',
        'string' => 'Das Feld :attribute muss weniger als :value Zeichen haben. ',
    ],
    'lte' => [
        'array' => 'Das Feld :attribute darf nicht mehr als :value Elemente haben. ',
        'file' => 'Das Feld :attribute muss kleiner oder gleich :value Kilobyte sein. ',
        'numeric' => 'Das Feld :attribute muss kleiner oder gleich :value sein. ',
        'string' => 'Das Feld :attribute muss kleiner oder gleich :value Zeichen haben. ',
    ],
    'mac_address' => 'Das Feld :attribute muss eine gültige MAC-Adresse sein. ',
    'max' => [
        'array' => 'Das Feld :attribute darf nicht mehr als :max Elemente haben. ',
        'file' => 'Das Feld :attribute darf nicht größer als :max Kilobyte sein. ',
        'numeric' => 'Das Feld :attribute darf nicht größer als :max sein. ',
        'string' => 'Das Feld :attribute darf nicht mehr als :max Zeichen haben. ',
    ],
    'max_digits' => 'Das Feld :attribute darf nicht mehr als :max Ziffern haben. ',
    'mimes' => 'Das Feld :attribute muss eine Datei des Typs :values sein. ',
    'mimetypes' => 'Das Feld :attribute muss eine Datei des Typs :values sein. ',
    'min' => [
        'array' => 'Das Feld :attribute muss mindestens :min Elemente haben. ',
        'file' => 'Das Feld :attribute muss mindestens :min Kilobyte sein. ',
        'numeric' => 'Das Feld :attribute muss mindestens :min sein. ',
        'string' => 'Das Feld :attribute muss mindestens :min Zeichen haben. ',
    ],
    'min_digits' => 'Das Feld :attribute muss mindestens :min Ziffern haben. ',
    'missing' => 'Das Feld :attribute muss fehlen. ',
    'missing_if' => 'Das Feld :attribute muss fehlen, wenn :other :value ist. ',
    'missing_unless' => 'Das Feld :attribute muss fehlen, es sei denn, :other ist :value. ',
    'missing_with' => 'Das Feld :attribute muss fehlen, wenn :values vorhanden sind. ',
    'missing_with_all' => 'Das Feld :attribute muss fehlen, wenn alle :values vorhanden sind. ',
    'multiple_of' => 'Das Feld :attribute muss ein Vielfaches von :value sein. ',
    'not_in' => 'Die ausgewählte :attribute ist ungültig. ',
    'not_regex' => 'Das Format des Feldes :attribute ist ungültig. ',
    'numeric' => 'Das Feld :attribute muss eine Zahl sein. ',
    'password' => [
        'letters' => 'Das Feld :attribute muss mindestens einen Buchstaben enthalten. ',
        'mixed' => 'Das Feld :attribute muss mindestens einen Großbuchstaben und einen Kleinbuchstaben enthalten. ',
        'numbers' => 'Das Feld :attribute muss mindestens eine Zahl enthalten. ',
        'symbols' => 'Das Feld :attribute muss mindestens ein Symbol enthalten. ',
        'uncompromised' => 'Die angegebene :attribute wurde in einem Datenleck gefunden. Bitte wählen Sie eine andere :attribute. ',
    ],
    'present' => 'Das Feld :attribute muss vorhanden sein. ',
    'present_if' => 'Das Feld :attribute muss vorhanden sein, wenn :other :value ist. ',
    'present_unless' => 'Das Feld :attribute muss vorhanden sein, es sei denn, :other ist :value. ',
    'present_with' => 'Das Feld :attribute muss vorhanden sein, wenn :values vorhanden sind. ',
    'present_with_all' => 'Das Feld :attribute muss vorhanden sein, wenn alle :values vorhanden sind. ',
    'prohibited' => 'Das Feld :attribute ist verboten. ',
    'prohibited_if' => 'Das Feld :attribute ist verboten, wenn :other :value ist. ',
    'prohibited_unless' => 'Das Feld :attribute ist verboten, es sei denn, :other ist in :values. ',
    'prohibits' => 'Das Feld :attribute verbietet :other. ',
    'regex' => 'Das Format des Feldes :attribute ist ungültig. ',
    'required' => 'Das Feld :attribute ist erforderlich. ',
    'required_array_keys' => 'Das Feld :attribute muss die folgenden Schlüssel enthalten: :values. ',
    'required_if' => 'Das Feld :attribute ist erforderlich, wenn :other :value ist. ',
    'required_if_accepted' => 'Das Feld :attribute ist erforderlich, wenn :other akzeptiert wird. ',
    'required_unless' => 'Das Feld :attribute ist erforderlich, es sei denn, :other ist in :values. ',
    'required_with' => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden sind. ',
    'required_with_all' => 'Das Feld :attribute ist erforderlich, wenn alle :values vorhanden sind. ',
    'required_without' => 'Das Feld :attribute ist erforderlich, wenn :values nicht vorhanden sind. ',
    'required_without_all' => 'Das Feld :attribute ist erforderlich, wenn keine der :values vorhanden ist. ',
    'same' => 'Das Feld :attribute und :other müssen übereinstimmen. ',
    'size' => [
        'array' => 'Das Feld :attribute muss :size Elemente enthalten. ',
        'file' => 'Das Feld :attribute muss :size Kilobyte sein. ',
        'numeric' => 'Das Feld :attribute muss :size sein. ',
        'string' => 'Das Feld :attribute muss :size Zeichen haben. ',
    ],
    'starts_with' => 'Das Feld :attribute muss mit einem der folgenden Werte beginnen: :values. ',
    'string' => 'Das Feld :attribute muss eine Zeichenfolge sein. ',
    'timezone' => 'Das Feld :attribute muss eine gültige Zeitzone sein. ',
    'unique' => 'Das Feld :attribute ist bereits vergeben. ',
    'uploaded' => 'Das :attribute konnte nicht hochgeladen werden. ',
    'uppercase' => 'Das Feld :attribute muss großgeschrieben sein. ',
    'url' => 'Das Feld :attribute muss eine gültige URL sein. ',
    'ulid' => 'Das Feld :attribute muss eine gültige ULID sein. ',
    'uuid' => 'Das Feld :attribute muss eine gültige UUID sein. ',
    'email_domain' => [
        'invalid' => 'Das :attribute muss eine gültige E-Mail-Domäne sein. ',
    ],
];
