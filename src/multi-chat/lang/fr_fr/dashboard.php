<?php

return [
    'route' => 'Tableau de bord',

    'header.interface' => 'Interface de gestion du tableau de bord',
    'header.create_rule' => 'Créer une règle de filtrage',
    'header.update_rule' => 'Mettre à jour une règle de filtrage',

    'tab.statistics' => 'Statistiques',
    'tab.blacklist' => 'Liste noire',
    'tab.feedbacks' => 'Commentaires',
    'tab.logs' => 'Journaux système',
    'tab.safetyguard' => 'Filtrage de sécurité',
    'tab.inspect' => 'Navigateur de messages',

    'colName.Action' => 'Action :',
    'colName.Description' => 'Description :',
    'colName.UserID' => 'ID de l’opérateur :',
    'colName.IP' => 'Emplacement IP :',
    'colName.Timestamp' => 'Horodatage :',

    'filter.StartDate' => 'Début :',
    'filter.EndDate' => 'Fin :',
    'filter.Action' => 'Action :',
    'filter.Description' => 'Description :',
    'filter.UserID' => 'ID de l’opérateur :',
    'filter.IPAddress' => 'Emplacement IP :',

    'placeholder.PasteRawDataHere' => 'Collez les données brutes à convertir ici, ou faites glisser un fichier ici.',
    
    'header.ActiveModels' => 'Modèles activés',
    'header.InactiveModels' => 'Modèles désactivés',
    'header.ModelFilter' => 'Filtrer les modèles :',
    'header.ExportSetting' => 'Paramètres d’exportation :',

    'button.ExportAndDownload' => 'Exporter et télécharger',
    'button.LoadFile' => 'Charger un fichier',
    'button.ConvertAndDownload' => 'Convertir et télécharger',
    'button.create_rule' => 'Ajouter une règle',
    'button.create' => 'Créer',
    'button.cancel' => 'Annuler',
    'button.delete' => 'Supprimer',
    'button.update' => 'Mettre à jour',

    "hint.safety_guard_offline"=>"Le système de filtrage de sécurité est hors ligne",
    "hint.wip_option"=>"En cours de développement, aucune option n’est disponible pour le moment",

    'action.overwrite' => 'Remplacé par le système',
    'action.block' => 'Bloqué, possibilité de choisir un avertissement',
    'action.warn' => 'Avertissement uniquement',
    'action.none' => 'Aucune action',

    'msg.SomethingWentWrong' => 'Une erreur s’est produite…',
    'msg.choose_target' => 'Veuillez choisir un modèle',
    'msg.create_rule' => 'Êtes-vous sûr de vouloir créer cette règle ?',
    'msg.delete_rule' => 'Êtes-vous sûr de vouloir supprimer cette règle ?',
    'msg.update_rule' => 'Êtes-vous sûr de vouloir mettre à jour cette règle ?',
    'msg.MustHave1Model' => 'Vous devez sélectionner au moins un modèle à exporter',
    'msg.InvalidJSONFormat' => 'Format JSON incorrect',
    'msg.NoRecord' => 'Aucun enregistrement',

    'rule.filter.keyword' => 'Règle de mot-clé',
    'rule.filter.embedding' => 'Règle d’intégration',
    'rule.name' => 'Nom de la règle',
    'rule.description' => 'Description de la règle',
    'rule.target' => 'Modèle auquel appliquer la règle',
    'rule.action' => 'Action de la règle',
    'rule.warning' => 'Message d’avertissement (facultatif)',
    'rule.filter.input' => 'Filtrage d’entrée',
    'rule.filter.output' => 'Filtrage de sortie',
];