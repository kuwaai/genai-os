<?php

return [
    'interface.header' => 'Dashboard-Verwaltungsoberfläche',
    'route' => 'Dashboard',

    'tab.statistics' => 'Statistik',
    'tab.blacklist' => 'Schwarze Liste',
    'tab.feedbacks' => 'Feedback',
    'tab.logs' => 'Systemprotokoll',
    'tab.safetyguard' => 'Sicherheitsfilter',
    'tab.inspect' => 'Nachrichten-Explorer',

    //Tab_logs
    'colName.Action' => 'Aktion:',
    'colName.Description' => 'Beschreibung:',
    'colName.UserID' => 'Benutzer-ID:',
    'colName.IP' => 'IP-Adresse:',
    'colName.Timestamp' => 'Zeitstempel:',
    'msg.NoRecord' => 'Kein Eintrag',
    'filter.StartDate' => 'Start:',
    'filter.EndDate' => 'Ende:',
    'filter.Action' => 'Aktion:',
    'filter.Description' => 'Beschreibung:',
    'filter.UserID' => 'Benutzer-ID:',
    'filter.IPAddress' => 'IP-Adresse:',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Füge die zu konvertierenden Rohdaten ein oder ziehe eine Datei hierher.',
    "hint.wip_option"=>"In Arbeit, derzeit keine Optionen",
    'header.ActiveModels' => 'Aktivierte Modelle',
    'header.InactiveModels' => 'Deaktivierte Modelle',
    'header.ModelFilter' => 'Modelle filtern:',
    'header.ExportSetting' => 'Exporteinstellungen:',

    'button.ExportAndDownload' => 'Exportieren und herunterladen',
    'button.LoadFile' => 'Datei laden',
    'button.ConvertAndDownload' => 'Konvertieren und herunterladen',
    'msg.MustHave1Model' => 'Du musst mindestens ein Modell für den Export auswählen',
    'msg.InvalidJSONFormat' => 'Ungültiges JSON-Format',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"Das Sicherheitsfiltersystem ist offline",
    'header.create_rule' => 'Filterregel erstellen',
    'header.update_rule' => 'Filterregel aktualisieren',
    'rule.filter.keyword' => 'Schlüsselwortregel',
    'rule.filter.embedding' => 'Einbettungregel',

    'action.overwrite' => 'Vom System überschreiben',
    'action.block' => 'Blockieren, optional warnen',
    'action.warn' => 'Nur warnen',
    'action.none' => 'Keine Aktion',

    'msg.SomethingWentWrong' => 'Da ist etwas schief gelaufen...',
    'msg.choose_target' => 'Bitte wähle ein Modell',
    'msg.create_rule' => 'Bist du sicher, dass du diese Regel erstellen willst?',
    'msg.delete_rule' => 'Bist du sicher, dass du diese Regel löschen willst?',
    'msg.update_rule' => 'Bist du sicher, dass du diese Regel aktualisieren willst?',

    'rule.name' => 'Regelname',
    'rule.description' => 'Regelbeschreibung',
    'rule.target' => 'Modell für Regel festlegen',
    'rule.action' => 'Regelaktion',
    'rule.warning' => 'Warnmeldung (optional)',
    'rule.filter.input' => 'Eingangsfilter',
    'rule.filter.output' => 'Ausgabefilter',

    'button.create_rule' => 'Regel erstellen',
    'button.create' => 'Erstellen',
    'button.cancel' => 'Abbrechen',
    'button.delete' => 'Löschen',
    'button.update' => 'Aktualisieren',
];