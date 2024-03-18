<?php

return [
    'interface.header' => 'Dashboard-Verwaltungsschnittstelle',
    'route' => 'Dashboard',

    'tab.statistics' => 'Statistiken',
    'tab.blacklist' => 'Blacklist',
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
    'msg.NoRecord' => 'Keine Aufzeichnung',
    'filter.StartDate' => 'Startdatum:',
    'filter.EndDate' => 'Enddatum:',
    'filter.Action' => 'Aktion:',
    'filter.Description' => 'Beschreibung:',
    'filter.UserID' => 'Benutzer-ID:',
    'filter.IPAddress' => 'IP-Adresse:',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Fügen Sie hier die zu konvertierenden Rohdaten ein oder ziehen Sie eine Datei hierher.',
    'hint.wip_option' => 'Work in Progress, derzeit keine Optionen verfügbar',
    'header.ActiveModels' => 'Aktive Modelle',
    'header.InactiveModels' => 'Inaktive Modelle',
    'header.ModelFilter' => 'Modellfilter:',
    'header.ExportSetting' => 'Exporteinstellung:',

    'button.ExportAndDownload' => 'Exportieren und Herunterladen',
    'button.LoadFile' => 'Datei laden',
    'button.ConvertAndDownload' => 'Konvertieren und Herunterladen',
    'msg.MustHave1Model' => 'Sie müssen mindestens ein Modell auswählen, um es zu exportieren',
    'msg.InvalidJSONFormat' => 'Ungültiges JSON-Format',

    //Tab_SafetyGuard
    'hint.safety_guard_offline' => 'Der Sicherheitsfilter ist offline',
    'header.create_rule' => 'Regel erstellen',
    'header.update_rule' => 'Regel aktualisieren',
    'rule.filter.keyword' => 'Schlüsselwort Regel',
    'rule.filter.embedding' => 'Einbettungsregel',

    'action.overwrite' => 'Von System überschreiben',
    'action.block' => 'Blockieren mit optionaler Warnung',
    'action.warn' => 'Nur warnen',
    'action.none' => 'Keine Aktion',

    'msg.SomethingWentWrong' => 'Etwas ist schiefgegangen...',
    'msg.choose_target' => 'Bitte wählen Sie ein Modell aus',
    'msg.create_rule' => 'Sind Sie sicher, dass Sie diese Regel erstellen möchten?',
    'msg.delete_rule' => 'Sind Sie sicher, dass Sie diese Regel löschen möchten?',
    'msg.update_rule' => 'Sind Sie sicher, dass Sie diese Regel aktualisieren möchten?',

    'rule.name' => 'Regelname',
    'rule.description' => 'Regelbeschreibung',
    'rule.target' => 'Zielmodell für Regel anwenden',
    'rule.action' => 'Regelverhalten',
    'rule.warning' => 'Warnhinweis (optional)',
    'rule.filter.input' => 'Eingabefilter',
    'rule.filter.output' => 'Ausgabefilter',

    'button.create_rule' => 'Regel erstellen',
    'button.create' => 'Erstellen',
    'button.cancel' => 'Abbrechen',
    'button.delete' => 'Löschen',
    'button.update' => 'Aktualisieren',
];
