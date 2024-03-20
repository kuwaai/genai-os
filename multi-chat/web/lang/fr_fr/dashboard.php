<?php

return [
    'interface.header' => 'Interface de gestion du tableau de bord',
    'route' => 'Tableau de bord',

    'tab.statistics' => 'Statistiques',
    'tab.blacklist' => 'Liste noire',
    'tab.feedbacks' => 'Retours',
    'tab.logs' => 'Journaux système',
    'tab.safetyguard' => 'Garde de sécurité',
    'tab.inspect' => 'Navigateur de messages',

    //Tab_logs
    'colName.Action' => 'Action :',
    'colName.Description' => 'Description :',
    'colName.UserID' => 'ID utilisateur :',
    'colName.IP' => 'Adresse IP :',
    'colName.Timestamp' => 'Horodatage :',
    'msg.NoRecord' => 'Aucun enregistrement',
    'filter.StartDate' => 'Début :',
    'filter.EndDate' => 'Fin :',
    'filter.Action' => 'Action :',
    'filter.Description' => 'Description :',
    'filter.UserID' => 'ID utilisateur :',
    'filter.IPAddress' => 'Adresse IP :',

    //Tab_feedback
    'hint.PasteRawDataHere' => 'Veuillez coller les données brutes à convertir, ou faites glisser un fichier ici.',
    "hint.wip_option"=>"En cours, aucune option pour le moment",
    'header.ActiveModels' => 'Modèles actifs',
    'header.InactiveModels' => 'Modèles inactifs',
    'header.ModelFilter' => 'Filtre de modèle :',
    'header.ExportSetting' => 'Paramètres d\'exportation :',

    'button.ExportAndDownload' => 'Exporter et télécharger',
    'button.LoadFile' => 'Charger un fichier',
    'button.ConvertAndDownload' => 'Convertir et télécharger',
    'msg.MustHave1Model' => 'Vous devez sélectionner au moins un modèle à exporter',
    'msg.InvalidJSONFormat' => 'Format JSON invalide',

    //Tab_SafetyGuard
    "hint.safety_guard_offline"=>"Le système de garde de sécurité est hors ligne",
    'header.create_rule' => 'Créer une règle de filtrage',
    'header.update_rule' => 'Mettre à jour une règle de filtrage',
    'rule.filter.keyword' => 'Règle de mot-clé',
    'rule.filter.embedding' => 'Règle d\'incorporation',

    'action.overwrite' => 'Écrasement par le système',
    'action.block' => 'Bloquer avec avertissement optionnel',
    'action.warn' => 'Avertissement uniquement',
    'action.none' => 'Aucune action',

    'msg.SomethingWentWrong' => 'Quelque chose s\'est mal passé...',
    'msg.choose_target' => 'Veuillez sélectionner un modèle',
    'msg.create_rule' => 'Voulez-vous vraiment créer cette règle ?',
    'msg.delete_rule' => 'Voulez-vous vraiment supprimer cette règle ?',
    'msg.update_rule' => 'Voulez-vous vraiment mettre à jour cette règle ?',

    'rule.name' => 'Nom de la règle',
    'rule.description' => 'Description de la règle',
    'rule.target' => 'Modèle à appliquer la règle',
    'rule.action' => 'Action de la règle',
    'rule.warning' => 'Message d\'avertissement (optionnel)',
    'rule.filter.input' => 'Filtre d\'entrée',
    'rule.filter.output' => 'Filtre de sortie',

    'button.create_rule' => 'Créer une règle',
    'button.create' => 'Créer',
    'button.cancel' => 'Annuler',
    'button.delete' => 'Supprimer',
    'button.update' => 'Mettre à jour',
];
