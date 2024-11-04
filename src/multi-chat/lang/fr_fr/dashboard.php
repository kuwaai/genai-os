<?php

return [
    'interface.header' => 'Interface de gestion du tableau de bord',
    'route' => 'Tableau de bord',

    'tab.statistics' => 'Statistiques',
    'tab.blacklist' => 'Liste noire',
    'tab.feedbacks' => 'Commentaires',
    'tab.logs' => 'Journaux système',
    'tab.safetyguard' => 'Filtre de sécurité',
    'tab.inspect' => 'Navigateur de messages',

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
    'hint.PasteRawDataHere' => 'Collez les données brutes à convertir ici, ou faites-les glisser dans cette zone.',
    "hint.wip_option"=>"En cours de développement, il n’y a actuellement aucune option",
    'header.ActiveModels' => 'Modèles actifs',
    'header.InactiveModels' => 'Modèles désactivés',
    'header.ModelFilter' => 'Filtrer les modèles :',
    'header.ExportSetting' => 'Paramètres d’exportation :',

    'button.ExportAndDownload' => 'Exporter et télécharger',
    'button.LoadFile' => 'Charger le fichier',
    'button.ConvertAndDownload' => 'Convertir et télécharger',
    'msg.MustHave1Model' => 'Vous devez sélectionner au moins un modèle à exporter',
    'msg.InvalidJSONFormat' => 'Format JSON incorrect',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"Le système de filtrage de sécurité est hors ligne",
    'header.create_rule' => 'Créer une règle de filtrage',
    'header.update_rule' => 'Mettre à jour une règle de filtrage',
    'rule.filter.keyword' => 'Règle de mot-clé',
    'rule.filter.embedding' => 'Règle d’intégration',

    'action.overwrite' => 'Remplacé par le système',
    'action.block' => 'Bloquer, possibilité d’avertissement',
    'action.warn' => 'Avertissement uniquement',
    'action.none' => 'Aucune action',

    'msg.SomethingWentWrong' => 'Quelque chose ne va pas...',
    'msg.choose_target' => 'Veuillez choisir un modèle',
    'msg.create_rule' => 'Êtes-vous sûr de vouloir créer cette règle ?',
    'msg.delete_rule' => 'Êtes-vous sûr de vouloir supprimer cette règle ?',
    'msg.update_rule' => 'Êtes-vous sûr de vouloir mettre à jour cette règle ?',

    'rule.name' => 'Nom de la règle',
    'rule.description' => 'Description de la règle',
    'rule.target' => 'Modèle pour lequel la règle est appliquée',
    'rule.action' => 'Action de la règle',
    'rule.warning' => 'Message d’avertissement (facultatif)',
    'rule.filter.input' => 'Filtrage d’entrée',
    'rule.filter.output' => 'Filtrage de sortie',

    'button.create_rule' => 'Ajouter une règle',
    'button.create' => 'Créer',
    'button.cancel' => 'Annuler',
    'button.delete' => 'Supprimer',
    'button.update' => 'Mettre à jour',
];