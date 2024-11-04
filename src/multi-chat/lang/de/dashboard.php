<?php

return [
    'route' => 'Dashboard',

    'header.interface' => 'Dashboard-Verwaltungsoberfläche',
    'header.create_rule' => 'Filterregel erstellen',
    'header.update_rule' => 'Filterregel aktualisieren',

    'tab.statistics' => 'Statistiken',
    'tab.blacklist' => 'Schwarze Liste',
    'tab.feedbacks' => 'Feedback',
    'tab.logs' => 'Systemprotokolle',
    'tab.safetyguard' => 'Sicherheitsfilter',
    'tab.inspect' => 'Nachrichten-Browser',

    'colName.Action' => 'Aktion:',
    'colName.Description' => 'Beschreibung:',
    'colName.UserID' => 'Benutzer-ID:',
    'colName.IP' => 'IP-Adresse:',
    'colName.Timestamp' => 'Zeitstempel:',

    'filter.StartDate' => 'Start:',
    'filter.EndDate' => 'Ende:',
    'filter.Action' => 'Aktion:',
    'filter.Description' => 'Beschreibung:',
    'filter.UserID' => 'Benutzer-ID:',
    'filter.IPAddress' => 'IP-Adresse:',

    'placeholder.PasteRawDataHere' => 'Bitte fügen Sie die zu konvertierenden Rohdaten ein oder ziehen Sie eine Datei hierher.',
    
    'header.ActiveModels' => 'Aktive Modelle',
    'header.InactiveModels' => 'Inaktive Modelle',
    'header.ModelFilter' => 'Modelle filtern:',
    'header.ExportSetting' => 'Exporteinstellungen:',

    'button.ExportAndDownload' => 'Exportieren und Herunterladen',
    'button.LoadFile' => 'Datei laden',
    'button.ConvertAndDownload' => 'Konvertieren und Herunterladen',
    'button.create_rule' => 'Regel hinzufügen',
    'button.create' => 'Erstellen',
    'button.cancel' => 'Abbrechen',
    'button.delete' => 'Löschen',
    'button.update' => 'Aktualisieren',

    "hint.safety_guard_offline"=>"Sicherheitsfilter-System ist offline",
    "hint.wip_option"=>"In Arbeit, derzeit keine Optionen verfügbar",

    'action.overwrite' => 'Vom System überschreiben',
    'action.block' => 'Blockieren, Warnung optional',
    'action.warn' => 'Nur Warnung',
    'action.none' => 'Keine Aktion',

    'msg.SomethingWentWrong' => 'Etwas ist schiefgelaufen...',
    'msg.choose_target' => 'Bitte ein Modell auswählen',
    'msg.create_rule' => 'Möchten Sie diese Regel wirklich erstellen?',
    'msg.delete_rule' => 'Möchten Sie diese Regel wirklich löschen?',
    'msg.update_rule' => 'Möchten Sie diese Regel wirklich aktualisieren?',
    'msg.MustHave1Model' => 'Sie müssen mindestens ein Modell zum Exportieren auswählen',
    'msg.InvalidJSONFormat' => 'Ungültiges JSON-Format',
    'msg.NoRecord' => 'Keine Einträge gefunden',

    'rule.filter.keyword' => 'Keyword-Regel',
    'rule.filter.embedding' => 'Embedding-Regel',
    'rule.name' => 'Regelname',
    'rule.description' => 'Regelbeschreibung',
    'rule.target' => 'Modell für die Regel auswählen',
    'rule.action' => 'Regelaktion',
    'rule.warning' => 'Warnmeldung (optional)',
    'rule.filter.input' => 'Eingabefilter',
    'rule.filter.output' => 'Ausgabefilter',
];