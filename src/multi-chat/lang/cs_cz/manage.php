<?php

return [
    'route' => 'Administrace',
    'interface.header' => 'Administrátorské rozhraní',
    'button.delete' => 'Smazat',
    'button.update' => 'Aktualizovat',
    'button.create' => 'Vytvořit',
    'button.save' => 'Uložit',
    'button.yes' => 'Ano, potvrzuji',
    'button.no' => 'Ne, zrušit',
    'button.cancel' => 'Zrušit',
    'button.close' => 'Zavřít',
    'button.accept' => 'Souhlasím',
    'button.export' => 'Exportovat',
    'button.updateWeb' => 'Aktualizovat web',
    'button.shutdown' => 'Vypnout',

    //Tabs
    'tab.groups' => 'Skupiny',
    'tab.users' => 'Uživatelé',
    'tab.llms' => 'Modely',
    'tab.settings' => 'Nastavení webu',
    'tab.kernel' => 'Jádro',
    'tab.workers' => 'Pracovní procesy',

    //Groups
    'button.new_group' => 'Nová skupina',
    'header.create_group' => 'Vytvořit novou skupinu',
    'label.tab_permissions' => 'Oprávnění stránek',
    'label.invite_code' => 'Pozvánkový kód',
    'label.group_name' => 'Název',
    'label.invite_code' => 'Pozvánkový kód',
    'placeholder.invite_code' => 'Pozvánkový kód',
    'label.describe' => 'Popis',
    'placeholder.group_name' => 'Název skupiny',
    'placeholder.group_detail' => 'Poznámka ke skupině',
    'label.read' => 'Číst',
    'label.delete' => 'Smazat',
    'label.update' => 'Aktualizovat',
    'label.create' => 'Vytvořit',
    'label.llm_permission.disabled' => 'Přístup k modelům (deaktivované modely)',
    'label.llm_permission.enabled' => 'Přístup k modelům (aktivované modely)',
    'header.edit_group' => 'Upravit skupinu',
    'hint.group_updated' => 'Skupina byla úspěšně aktualizována!',
    'hint.group_created' => 'Skupina byla úspěšně vytvořena!',
    'modal.delete_group.header' => 'Opravdu chcete smazat tuto skupinu?',

    //Users
    'header.menu' => 'Hlavní nabídka',
    'header.group_selector' => 'Výběr skupiny',
    'header.fuzzy_search' => 'Nejasné vyhledávání',
    'header.create_user' => 'Vytvořit uživatele',
    'label.group_selector' => 'Filtr uživatelů podle skupiny',
    'label.fuzzy_search' => 'Vyhledávání uživatelů podle jména nebo e-mailu',
    'label.create_user' => 'Vytvořit profil nového uživatele',

    'create_user.header' => 'Vytvořit nový účet',
    'create_user.joined_group' => 'Připojené skupiny',
    'label.members' => 'členů',
    'label.other_users' => 'Uživatelé bez skupin',
    'button.return_group_list' => 'Zpět na seznam skupin',
    'placeholder.search_user' => 'Hledejte e-mail nebo jméno',
    'hint.enter_to_search' => 'Stisknutím Enteru vyhledejte',

    'group_selector.header' => 'Upravit uživatele',
    'placeholder.email' => 'Uživatelský e-mail',
    'placeholder.username' => 'Uživatelské jméno',
    'label.name' => 'Jméno',
    'modal.delete_user.header' => 'Opravdu chcete smazat uživatele?',
    'button.cancel' => 'Zrušit',
    'label.email' => 'E-mail',
    'label.password' => 'Heslo',
    'label.update_password' => 'Aktualizovat heslo',
    'label.detail' => 'Detail',
    'placeholder.new_password' => 'Nové heslo',
    'label.require_change_password' => 'Vyžadovat změnu hesla při dalším přihlášení',
    'label.extra_setting' => 'Další nastavení',
    'label.created_at' => 'Vytvořeno',
    'label.updated_at' => 'Aktualizováno',

    //LLMs
    'button.new_model' => 'Nový model',
    'label.enabled_models' => 'Aktivované modely',
    'label.disabled_models' => 'Deaktivované modely',
    'header.create_model' => 'Vytvořit profil modelu',
    'modal.create_model.header' => 'Opravdu chcete vytvořit tento profil?',
    'label.model_image' => 'Ikona modelu',
    'label.model_name' => 'Název modelu',
    'label.order' => 'Zobrazovací pořadí',
    'label.link' => 'Externí odkaz',
    'placeholder.description' => 'Popis tohoto modelu',
    'label.version' => 'Verze',
    'label.access_code' => 'Přístupový kód',
    'placeholder.link' => 'Externí odkaz na tento model',
    'header.update_model' => 'Upravit profil modelu',
    'label.description' => 'Popis',
    'modal.update_model.header' => 'Opravdu chcete aktualizovat tento profil jazykového modelu?',
    'modal.delete_model.header' => 'Opravdu chcete smazat tento profil jazykového modelu?',
    'modal.confirm_setting_modal.shrink_max_upload_file_count' => 'Snížení limitu celkového počtu nahraných souborů způsobí odstranění souborů uživatele, které překračují limit. Jste si jistí?',

    //setting
    'header.settings' => 'Nastavení webu',
    'header.updateWeb' => 'Pokrok aktualizace webu',
    'label.settings' => 'Všechna nastavení webu lze upravit zde',
    'label.allow_register' => 'Povolit registraci',
    'button.reset_redis' => 'Resetovat Redis cache',
    'hint.saved' => 'Uloženo',
    'hint.redis_cache_cleared' => 'Redis cache byla vymazána',
    'label.updateweb_git_ssh_command' =>'Proměnná prostředí GIT_SSH_COMMAND',
    'label.need_invite' => 'Registrace vyžaduje pozvánkový kód',
    'label.footer_warning' => 'Varování v zápatí konverzace',
    'label.anno' => 'Systémové oznámení',
    'label.tos' => 'Podmínky služby',
    'label.upload_max_size_mb' => 'Limit velikosti nahraného souboru (MB)',
    'label.upload_allowed_extensions' => 'Povolené přípony souborů k nahrávání (* znamená libovolnou příponu)',
    'label.upload_max_file_count' => 'Limit celkového počtu nahraných souborů (-1 znamená bez omezení)',

    //kernel
    'label.kernel_location' => 'Umístění jádra',
    'label.safety_guard_API' => 'Bezpečnostní filtr umístění',
    'label.ready' => 'Připraveno',
    'label.busy' => 'Zaneprázdněno',
    'label.accesscode' => 'Přístupový kód',
    'label.endpoint' => 'Koncové rozhraní',
    'label.status' => 'Stav používání',
    'label.historyid' => 'ID historie',
    'label.userid' => 'ID uživatele',
    'button.new_executor' => 'Nový vykonavatel',
    'label.edit_executor' => 'Upravit vykonavatele',
    'label.create_executor' => 'Vytvořit vykonavatele',

    //Workers
    'label.failed' => 'Operace se nezdařila. Zkuste to prosím znovu.',
    'label.loading' => 'Načítání...',
    'label.last_refresh' => 'Poslední aktualizace: :time',
    'label.current_worker_count' => 'Aktuální počet pracovních procesů',
    'label.error_fetching_worker_count' => 'Chyba při získávání počtu pracovních procesů.',
    'label.last_refresh_time' => 'Poslední aktualizace',
    'label.seconds_ago' => 'sekund předtím',
    'label.error' => 'Chyba',
    'label.valid_worker_count' => 'Zadejte prosím platný počet pracovních procesů.',

    'button.start' => 'Spustit pracovní procesy',
    'button.stop' => 'Zastavit všechny pracovní procesy',
    'button.confirm' => 'Potvrdit',
    'button.cancel' => 'Zrušit',

    'modal.start.title' => 'Spustit pracovní procesy',
    'modal.start.label' => 'Počet pracovních procesů:',
    'modal.stop.title' => 'Zastavit všechny pracovní procesy',
    'modal.stop.confirm' => 'Opravdu chcete zastavit všechny pracovní procesy?',
];