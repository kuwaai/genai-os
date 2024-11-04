<?php

return [
    'interface.header' => 'Správní rozhraní Dashboardu',
    'route' => 'Dashboard',

    'tab.statistics' => 'Statistiky',
    'tab.blacklist' => 'Černá listina',
    'tab.feedbacks' => 'Zpětná vazba',
    'tab.logs' => 'Systémové protokoly',
    'tab.safetyguard' => 'Bezpečnostní filtr',
    'tab.inspect' => 'Prohlížeč zpráv',

    //Tab_logs
    'colName.Action' => 'Akce:',
    'colName.Description' => 'Popis:',
    'colName.UserID' => 'ID uživatele:',
    'colName.IP' => 'IP adresa:',
    'colName.Timestamp' => 'Časové razítko:',
    'msg.NoRecord' => 'Žádné záznamy',
    'filter.StartDate' => 'Začátek:',
    'filter.EndDate' => 'Konec:',
    'filter.Action' => 'Akce:',
    'filter.Description' => 'Popis:',
    'filter.UserID' => 'ID uživatele:',
    'filter.IPAddress' => 'IP adresa:',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Vložte surová data, která chcete převést, nebo sem přetáhněte soubor.',
    "hint.wip_option"=>"V práci, momentálně nejsou k dispozici žádné možnosti",
    'header.ActiveModels' => 'Aktivované modely',
    'header.InactiveModels' => 'Deaktivované modely',
    'header.ModelFilter' => 'Filtr modelu:',
    'header.ExportSetting' => 'Nastavení exportu:',

    'button.ExportAndDownload' => 'Exportovat a stáhnout',
    'button.LoadFile' => 'Načíst soubor',
    'button.ConvertAndDownload' => 'Převést a stáhnout',
    'msg.MustHave1Model' => 'Musíte vybrat alespoň jeden model, který chcete exportovat',
    'msg.InvalidJSONFormat' => 'Chybějící formát JSON',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"Systém bezpečnostního filtru je offline",
    'header.create_rule' => 'Vytvořit filtrační pravidlo',
    'header.update_rule' => 'Aktualizovat filtrační pravidlo',
    'rule.filter.keyword' => 'Pravidlo klíčového slova',
    'rule.filter.embedding' => 'Pravidlo vložení',

    'action.overwrite' => 'Přepsat systémem',
    'action.block' => 'Blokovat, volitelně s varováním',
    'action.warn' => 'Pouze varování',
    'action.none' => 'Žádná akce',

    'msg.SomethingWentWrong' => 'Něco se pokazilo...',
    'msg.choose_target' => 'Vyberte model',
    'msg.create_rule' => 'Jste si jistí, že chcete toto pravidlo vytvořit?',
    'msg.delete_rule' => 'Jste si jistí, že chcete toto pravidlo smazat?',
    'msg.update_rule' => 'Jste si jistí, že chcete toto pravidlo aktualizovat?',

    'rule.name' => 'Název pravidla',
    'rule.description' => 'Popis pravidla',
    'rule.target' => 'Vyberte model, na který se má toto pravidlo vztahovat',
    'rule.action' => 'Akce pravidla',
    'rule.warning' => 'Zpráva varování (volitelné)',
    'rule.filter.input' => 'Vstupní filtr',
    'rule.filter.output' => 'Výstupní filtr',

    'button.create_rule' => 'Přidat pravidlo',
    'button.create' => 'Vytvořit',
    'button.cancel' => 'Zrušit',
    'button.delete' => 'Smazat',
    'button.update' => 'Aktualizovat',
];