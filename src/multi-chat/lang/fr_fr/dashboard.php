<?php

return [
    'interface.header' => 'Interface de gestion du tableau de bord',
    'route' => 'Tableau de bord',

    'tab.statistics' => 'Statistiques',
    'tab.blacklist' => 'Liste noire',
    'tab.feedbacks' => 'Commentaires',
    'tab.logs' => 'Journal système',
    'tab.safetyguard' => 'Filtre de sécurité',
    'tab.inspect' => 'Navigateur de message',

    //Tab_logs
    'colName.Action' => 'Action :',
    'colName.Description' => 'Description :',
    'colName.UserID' => 'ID de l’utilisateur :',
    'colName.IP' => 'Adresse IP :',
    'colName.Timestamp' => 'Horodatage :',
    'msg.NoRecord' => 'Aucun enregistrement',
    'filter.StartDate' => 'Début :',
    'filter.EndDate' => 'Fin :',
    'filter.Action' => 'Action :',
    'filter.Description' => 'Description :',
    'filter.UserID' => 'ID de l’utilisateur :',
    'filter.IPAddress' => 'Adresse IP :',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Veuillez coller les données brutes à convertir ou faire glisser le fichier ici.',
    'hint.wip_option' => 'En cours, aucune option pour le moment',
    'header.ActiveModels' => 'Modèles activés',
    'header.InactiveModels' => 'Modèles désactivés',
    'header.ModelFilter' => 'Filtrer le modèle :',
    'header.ExportSetting' => 'Réglages d’export :',

    'button.ExportAndDownload' => 'Exporter et télécharger',
    'button.LoadFile' => 'Charger un fichier',
    'button.ConvertAndDownload' => 'Convertir et télécharger',
    'msg.MustHave1Model' => 'Vous devez sélectionner au moins un modèle à exporter',
    'msg.InvalidJSONFormat' => 'Format JSON non valide',

    //Tab_SafetyGuard
    'hint.safety_guard_offline' => 'Le système de filtrage de sécurité est hors ligne',
    'header.create_rule' => 'Créer une règle de filtrage',
    'header.update_rule' => 'Mettre à jour la règle de filtrage',
    'rule.filter.keyword' => 'Règle de mot-clé',
    'rule.filter.embedding' => 'Règle d’intégration',

    'action.overwrite' => 'Écrasé par le système',
    'action.block' => 'Bloquer, alerte facultative',
    'action.warn' => 'Alerte uniquement',
    'action.none' => 'Aucun comportement',

    'msg.SomethingWentWrong' => 'Un problème est survenu...',
    'msg.choose_target' => 'Veuillez choisir un modèle',
    'msg.create_rule' => 'Êtes-vous sûr de vouloir créer cette règle ?',
    'msg.delete_rule' => 'Êtes-vous sûr de vouloir supprimer cette règle ?',
    'msg.update_rule' => 'Êtes-vous sûr de vouloir mettre à jour cette règle ?',

    'rule.name' => 'Nom de la règle',
    'rule.description' => 'Description :',
    'rule.target' => 'Indiquer le modèle auquel appliquer la règle',
    'rule.action' => 'Comportement de la règle',
    'rule.warning' => 'Message d’alerte (facultatif)',
    'rule.filter.input' => 'Filtrage en entrée',
    'rule.filter.output' => 'Filtrage en sortie',

    'button.create_rule' => 'Ajouter une règle',
    'button.create' => 'Créer',
    'button.cancel' => 'Annuler',
    'button.delete' => 'Supprimer',
    'button.update' => 'Mettre à jour',
];