<?php

return [
    'route' => 'Administration',
    'interface.header' => 'Interface d\'administration des administrateurs',
    'button.delete' => 'Supprimer',
    'button.update' => 'Mettre à jour',
    'button.create' => 'Créer',
    'button.save' => 'Enregistrer',
    'button.yes' => 'Oui, je confirme',
    'button.no' => 'Non, annuler',
    'button.cancel' => 'Annuler',
    'button.close' => 'Fermer',
    'button.accept' => 'J\'accepte',
    'button.export' => 'Exporter',
    'button.updateWeb' => 'Mettre à jour le site web',
    'button.shutdown' => 'Arrêter',

    //Tabs
    'tab.groups' => 'Groupes',
    'tab.users' => 'Utilisateurs',
    'tab.llms' => 'Modèles',
    'tab.settings' => 'Paramètres du système',
    'tab.kernel' => 'Noyau',
    'tab.workers' => 'Traitements de tâches',

    //Groups
    'button.new_group' => 'Ajouter un groupe',
    'header.create_group' => 'Créer un nouveau groupe',
    'label.tab_permissions' => 'Permissions de page',
    'label.invite_code' => 'Code d\'invitation',
    'label.group_name' => 'Nom',
    'label.invite_code' => 'Code d\'invitation',
    'placeholder.invite_code' => 'Code d\'invitation',
    'label.describe' => 'Présentation',
    'placeholder.group_name' => 'Nom du groupe',
    'placeholder.group_detail' => 'Annotation du groupe',
    'label.read' => 'Lire',
    'label.delete' => 'Supprimer',
    'label.update' => 'Mettre à jour',
    'label.create' => 'Ajouter',
    'label.llm_permission.disabled' => 'Autorisation d\'utilisation du modèle (modèle désactivé)',
    'label.llm_permission.enabled' => 'Autorisation d\'utilisation du modèle (modèle activé)',
    'header.edit_group' => 'Modifier le groupe',
    'hint.group_updated' => 'Le groupe a été mis à jour avec succès !',
    'hint.group_created' => 'Le groupe a été créé avec succès !',
    'modal.delete_group.header' => 'Êtes-vous sûr de vouloir supprimer ce groupe',

    //Users
    'header.menu' => 'Menu principal',
    'header.group_selector' => 'Sélecteur de groupe',
    'header.fuzzy_search' => 'Recherche floue',
    'header.create_user' => 'Créer un utilisateur',
    'label.group_selector' => 'Commencer par filtrer les utilisateurs par groupe',
    'label.fuzzy_search' => 'Rechercher un utilisateur par nom ou adresse électronique',
    'label.create_user' => 'Créer un profil d\'utilisateur',

    'create_user.header' => 'Créer un nouveau compte',
    'create_user.joined_group' => 'Groupe joint',
    'label.members' => 'membres',
    'label.other_users' => 'Membres sans groupe',
    'button.return_group_list' => 'Retourner à la liste des groupes',
    'placeholder.search_user' => 'Rechercher une adresse électronique ou un nom',
    'hint.enter_to_search' => 'Appuyez sur Entrée pour rechercher',

    'group_selector.header' => 'Modifier l\'utilisateur',
    'placeholder.email' => 'Adresse électronique de l\'utilisateur',
    'placeholder.username' => 'Nom d\'utilisateur',
    'label.name' => 'Nom',
    'modal.delete_user.header' => 'Êtes-vous sûr de vouloir supprimer l\'utilisateur',
    'button.cancel' => 'Annuler',
    'label.email' => 'Courriel',
    'label.password' => 'Mot de passe',
    'label.update_password' => 'Mettre à jour le mot de passe',
    'label.detail' => 'Détails',
    'placeholder.new_password' => 'Nouveau mot de passe',
    'label.require_change_password' => 'Exiger le changement de mot de passe à la prochaine connexion',
    'label.extra_setting' => 'Paramètres supplémentaires',
    'label.created_at' => 'Créé le',
    'label.updated_at' => 'Mis à jour le',

    //LLMs
    'button.new_model' => 'Ajouter un modèle',
    'label.enabled_models' => 'Modèles activés',
    'label.disabled_models' => 'Modèles désactivés',
    'header.create_model' => 'Créer un profil de modèle',
    'modal.create_model.header' => 'Êtes-vous sûr de vouloir créer ce profil ?',
    'label.model_image' => 'Avatar du modèle',
    'label.model_name' => 'Nom du modèle',
    'label.order' => 'Ordre d\'affichage',
    'label.link' => 'Lien externe',
    'placeholder.description' => 'Présentation de ce modèle',
    'label.version' => 'Version',
    'label.access_code' => 'Code d\'accès',
    'placeholder.link' => 'Lien externe associé à ce modèle',
    'header.update_model' => 'Modifier le profil du modèle',
    'label.description' => 'Description',
    'modal.update_model.header' => 'Êtes-vous sûr de vouloir mettre à jour ce profil de modèle linguistique ?',
    'modal.delete_model.header' => 'Êtes-vous sûr de vouloir supprimer ce profil de modèle linguistique ?',
    'modal.confirm_setting_modal.shrink_max_upload_file_count' => 'Réduire la limite du nombre total de fichiers téléchargés supprimera les fichiers des utilisateurs qui dépassent la limite. Êtes-vous sûr ?',

    //setting
    'header.settings' => 'Paramètres du système',
    'header.updateWeb' => 'Progression de la mise à jour du site web',
    'label.settings' => 'Tous les paramètres relatifs au système peuvent être ajustés ici.',
    'label.agent_API' => 'Emplacement de la connexion de l\'API de l\'agent',
    'label.allow_register' => 'Autoriser l\'inscription',
    'button.reset_redis' => 'Réinitialiser le cache Redis',
    'hint.saved' => 'Enregistré',
    'hint.redis_cache_cleared' => 'Le cache Redis a été effacé',
    'label.need_invite' => 'L\'inscription nécessite un code d\'invitation',
    'label.footer_warning' => 'Avertissement au bas de la conversation',
    'label.safety_guard_API' => 'Emplacement de la connexion du filtre de sécurité',
    'label.anno' => 'Annonce système',
    'label.tos' => 'Conditions d\'utilisation',
    'label.upload_max_size_mb' => 'Limite de taille des fichiers téléchargés (Mo)',
    'label.upload_allowed_extensions' => 'Extensions de fichiers autorisées pour le téléchargement (* représente toutes les extensions)',
    'label.upload_max_file_count' => 'Limite du nombre total de fichiers téléchargés (-1 signifie qu\'il n\'y a pas de limite)',

    //kernel
    'label.ready' => 'Prêt',
    'label.busy' => 'Occupé',
    'label.accesscode' => 'Code d\'accès',
    'label.endpoint' => 'Point de terminaison',
    'label.status' => 'État d\'utilisation',
    'label.historyid' => 'ID de l\'historique',
    'label.userid' => 'ID de l\'utilisateur',
    'button.new_executor' => 'Ajouter un exécuteur',
    'label.edit_executor' => 'Modifier l\'exécuteur',
    'label.create_executor' => 'Ajouter un exécuteur',

    //Workers
    'label.failed' => 'Échec de l\'opération. Veuillez réessayer.',
    'label.loading' => 'Chargement...',
    'label.last_refresh' => 'Dernière mise à jour : :time',
    'label.current_worker_count' => 'Nombre actuel de traitements de tâches',
    'label.error_fetching_worker_count' => 'Erreur lors de la récupération du nombre de traitements de tâches.',
    'label.last_refresh_time' => 'Dernière mise à jour',
    'label.seconds_ago' => 'secondes auparavant',
    'label.error' => 'Erreur',
    'label.valid_worker_count' => 'Veuillez entrer un nombre valide de traitements de tâches.',

    // Buttons
    'button.start' => 'Démarrer le traitement des tâches',
    'button.stop' => 'Arrêter tous les traitements de tâches',
    'button.confirm' => 'Confirmer',
    'button.cancel' => 'Annuler',

    // Modal Titles
    'modal.start.title' => 'Démarrer le traitement des tâches',
    'modal.start.label' => 'Nombre de traitements de tâches :',

    'modal.stop.title' => 'Arrêter tous les traitements de tâches',
    'modal.stop.confirm' => 'Êtes-vous sûr de vouloir arrêter tous les traitements de tâches ?',
];