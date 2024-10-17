<?php

return [
    'route' => 'Administration',
    'interface.header' => 'Interface d\'administration de l\'administrateur',
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
    'button.refresh' => 'Rafraîchir',

    //Tabs
    'tab.groups' => 'Groupes',
    'tab.users' => 'Utilisateurs',
    'tab.llms' => 'Modèles',
    'tab.settings' => 'Paramètres du site web',
    'tab.kernel' => 'Noyau',
    'tab.workers' => 'Traiteurs de tâches',

    //Groups
    'button.new_group' => 'Nouveau groupe',
    'header.create_group' => 'Créer un nouveau groupe',
    'label.tab_permissions' => 'Permissions des pages',
    'label.invite_code' => 'Code d\'invitation',
    'label.group_name' => 'Nom',
    'label.invite_code' => 'Code d\'invitation',
    'placeholder.invite_code' => 'Code d\'invitation',
    'label.describe' => 'Description',
    'placeholder.group_name' => 'Nom du groupe',
    'placeholder.group_detail' => 'Remarque du groupe',
    'label.read' => 'Lire',
    'label.delete' => 'Supprimer',
    'label.update' => 'Mettre à jour',
    'label.create' => 'Créer',
    'label.llm_permission.disabled' => 'Autorisation d\'utilisation du modèle (modèle désactivé)',
    'label.llm_permission.enabled' => 'Autorisation d\'utilisation du modèle (modèle activé)',
    'header.edit_group' => 'Modifier le groupe',
    'hint.group_updated' => 'Le groupe a été mis à jour avec succès !',
    'hint.group_created' => 'Le groupe a été créé avec succès !',
    'modal.delete_group.header' => 'Êtes-vous sûr de vouloir supprimer ce groupe ?',

    //Users
    'header.menu' => 'Menu principal',
    'header.group_selector' => 'Sélecteur de groupe',
    'header.fuzzy_search' => 'Moteur de recherche floue',
    'header.create_user' => 'Créer un utilisateur',
    'label.group_selector' => 'Filtrer les utilisateurs à partir d\'un groupe',
    'label.fuzzy_search' => 'Rechercher des utilisateurs par nom ou par adresse e-mail',
    'label.create_user' => 'Créer un profil d\'utilisateur',

    'create_user.header' => 'Créer un nouveau compte',
    'create_user.joined_group' => 'Groupes auxquels l\'utilisateur appartient',
    'label.members' => 'membres',
    'label.other_users' => 'Utilisateurs sans groupe',
    'button.return_group_list' => 'Retourner à la liste des groupes',
    'placeholder.search_user' => 'Rechercher par adresse e-mail ou par nom',
    'hint.enter_to_search' => 'Appuyez sur Entrée pour rechercher',

    'group_selector.header' => 'Modifier l\'utilisateur',
    'placeholder.email' => 'Adresse e-mail de l\'utilisateur',
    'placeholder.username' => 'Nom d\'utilisateur',
    'label.name' => 'Nom',
    'modal.delete_user.header' => 'Êtes-vous sûr de vouloir supprimer l\'utilisateur ?',
    'button.cancel' => 'Annuler',
    'label.email' => 'Adresse e-mail',
    'label.password' => 'Mot de passe',
    'label.update_password' => 'Mettre à jour le mot de passe',
    'label.detail' => 'Détails',
    'placeholder.new_password' => 'Nouveau mot de passe',
    'label.require_change_password' => 'Exiger un changement de mot de passe à la prochaine connexion',
    'label.extra_setting' => 'Paramètres supplémentaires',
    'label.created_at' => 'Créé le',
    'label.updated_at' => 'Mis à jour le',

    //LLMs
    'button.new_model' => 'Nouveau modèle',
    'label.enabled_models' => 'Modèles activés',
    'label.disabled_models' => 'Modèles désactivés',
    'header.create_model' => 'Créer un profil de modèle',
    'modal.create_model.header' => 'Êtes-vous sûr de vouloir créer ce profil ?',
    'label.model_image' => 'Image du modèle',
    'label.model_name' => 'Nom du modèle',
    'label.order' => 'Ordre d\'affichage',
    'label.link' => 'Lien externe',
    'placeholder.description' => 'Description de ce modèle',
    'label.version' => 'Version',
    'label.access_code' => 'Code d\'accès',
    'placeholder.link' => 'Lien externe vers ce modèle',
    'header.update_model' => 'Modifier le profil du modèle',
    'label.description' => 'Description',
    'modal.update_model.header' => 'Êtes-vous sûr de vouloir mettre à jour ce profil de modèle de langage ?',
    'modal.delete_model.header' => 'Êtes-vous sûr de vouloir supprimer ce profil de modèle de langage ?',
    'modal.confirm_setting_modal.shrink_max_upload_file_count' => 'Réduire la limite du nombre total de fichiers téléchargeables supprimera les fichiers des utilisateurs qui dépassent la limite. Êtes-vous sûr ?',

    //setting
    'header.settings' => 'Paramètres du site web',
    'header.updateWeb' => 'Progression de la mise à jour du site web',
    'header.confirmUpdate' => 'Êtes-vous sûr de vouloir mettre à jour le site web ?',
    'label.reloginWarning' => 'Une nouvelle connexion sera nécessaire après la mise à jour du site web. Tous les travailleurs seront redémarrés. Êtes-vous sûr de vouloir mettre à jour ce site web ?',
    'label.settings' => 'Tous les paramètres concernant le site web peuvent être ajustés ici',
    'label.allow_register' => 'Autoriser l\'inscription',
    'button.reset_redis' => 'Réinitialiser le cache Redis',
    'hint.saved' => 'Enregistré',
    'hint.redis_cache_cleared' => 'Le cache Redis a été effacé',
    'label.updateweb_git_ssh_command' =>'Variable d\'environnement GIT_SSH_COMMAND',
    'label.updateweb_path' => 'Variable d\'environnement PATH',
    'label.need_invite' => 'Un code d\'invitation est requis pour l\'inscription',
    'label.footer_warning' => 'Avertissement en bas de la conversation',
    'label.anno' => 'Annonce système',
    'label.tos' => 'Conditions générales d\'utilisation',
    'label.upload_max_size_mb' => 'Limite de taille des fichiers téléchargeables (Mo)',
    'label.upload_allowed_extensions' => 'Extensions de fichiers autorisées pour le téléchargement (* représente toutes les extensions)',
    'label.upload_max_file_count' => 'Limite du nombre total de fichiers téléchargeables (-1 représente aucune limite)',

    //kernel
    'label.kernel_location' => 'Emplacement de la connexion du noyau',
    'label.safety_guard_API' => 'Emplacement de la connexion du filtre de sécurité',
    'label.ready' => 'Prêt',
    'label.busy' => 'Occupé',
    'label.accesscode' => 'Code d\'accès',
    'label.endpoint' => 'Point de terminaison de la connexion',
    'label.status' => 'Statut d\'utilisation',
    'label.historyid' => 'ID de l\'historique',
    'label.userid' => 'ID de l\'utilisateur',
    'button.new_executor' => 'Nouvel exécuteur',
    'label.edit_executor' => 'Modifier l\'exécuteur',
    'label.create_executor' => 'Ajouter un exécuteur',

    //Workers
    'label.failed' => 'Opération échouée. Veuillez réessayer.',
    'label.loading' => 'Chargement...',
    'label.last_refresh' => 'Dernière mise à jour : :time',
    'label.current_worker_count' => 'Nombre actuel de traiteurs de tâches',
    'label.error_fetching_worker_count' => 'Erreur lors de la récupération du nombre de traiteurs de tâches.',
    'label.last_refresh_time' => 'Dernière mise à jour',
    'label.seconds_ago' => 'secondes',
    'label.error' => 'Erreur',
    'label.valid_worker_count' => 'Veuillez saisir un nombre valide de traiteurs de tâches.',
    'label.worker_started' => 'Le traiteur de tâches a été démarré avec succès',
    'label.worker_start_failed' => 'Échec du démarrage du traiteur de tâches :',
    'label.no_workers' => 'Aucun traiteur de tâches n\'est démarré',
    'label.worker_stopped' => 'Le traiteur de tâches a été arrêté',

    'button.start' => 'Démarrer le traiteur de tâches',
    'button.stop' => 'Arrêter tous les traiteurs de tâches',
    'button.confirm' => 'Confirmer',
    'button.cancel' => 'Annuler',

    'modal.start.title' => 'Démarrer le traiteur de tâches',
    'modal.start.label' => 'Nombre de traiteurs de tâches :',
    'modal.stop.title' => 'Arrêter tous les traiteurs de tâches',
    'modal.stop.confirm' => 'Êtes-vous sûr de vouloir arrêter tous les traiteurs de tâches ?',
];