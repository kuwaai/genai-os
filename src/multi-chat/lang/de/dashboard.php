<?php

return [
    'interface.header' => 'Dashboard Management Interface',
    'route' => 'Dashboard',

    'tab.statistics' => 'Statistiken',
    'tab.blacklist' => 'Schwarze Liste',
    'tab.feedbacks' => 'Feedbacks',
    'tab.logs' => 'Systemprotokolle',
    'tab.safetyguard' => 'Sicherheitsfilter',
    'tab.inspect' => 'Nachrichtenbrowser',

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
    'hint.PasteRawDataHere' => 'Bitte fügen Sie die zu konvertierenden Rohdaten hier ein. Sie können auch Dateien hierher ziehen.',
    "hint.wip_option"=>"Noch in Arbeit, derzeit keine Optionen verfügbar",
    'header.ActiveModels' => 'Aktivierte Modelle',
    'header.InactiveModels' => 'Deaktivierte Modelle',
    'header.ModelFilter' => 'Modellfilter:',
    'header.ExportSetting' => 'Exporteinstellungen:',

    'button.ExportAndDownload' => 'Exportieren und Herunterladen',
    'button.LoadFile' => 'Datei laden',
    'button.ConvertAndDownload' => 'Konvertieren und Herunterladen',
    'msg.MustHave1Model' => 'Sie müssen mindestens ein Modell zum Exportieren auswählen.',
    'msg.InvalidJSONFormat' => 'Ungültiges JSON-Format',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"Sicherheitsfiltersystem ist offline",
    'header.create_rule' => 'Filterregel erstellen',
    'header.update_rule' => 'Filterregel aktualisieren',
    'rule.filter.keyword' => 'Keyword-Regel',
    'rule.filter.embedding' => 'Embedding-Regel',

    'action.overwrite' => 'Vom System überschreiben',
    'action.block' => 'Blockieren, Warnung optional',
    'action.warn' => 'Nur Warnung',
    'action.none' => 'Keine Aktion',

    'msg.SomethingWentWrong' => 'Etwas ist schiefgelaufen...',
    'msg.choose_target' => 'Bitte wählen Sie ein Modell aus',
    'msg.create_rule' => 'Sind Sie sicher, dass Sie diese Regel erstellen möchten?',
    'msg.delete_rule' => 'Sind Sie sicher, dass Sie diese Regel löschen möchten?',
    'msg.update_rule' => 'Sind Sie sicher, dass Sie diese Regel aktualisieren möchten?',

    'rule.name' => 'Regelname',
    'rule.description' => 'Regelbeschreibung',
    'rule.target' => 'Modell, auf das die Regel angewendet werden soll',
    'rule.action' => 'Regelaktion',
    'rule.warning' => 'Warnmeldung (optional)',
    'rule.filter.input' => 'Eingabefilter',
    'rule.filter.output' => 'Ausgabefilter',

    'button.create_rule' => 'Regel hinzufügen',
    'button.create' => 'Erstellen',
    'button.cancel' => 'Abbrechen',
    'button.delete' => 'Löschen',
    'button.update' => 'Aktualisieren',
];