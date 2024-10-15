<?php

return [
    'route' => 'Admin',
    'interface.header' => 'Admin Management Interface',
    'button.delete' => 'Delete',
    'button.update' => 'Update',
    'button.create' => 'Create',
    'button.save' => 'Save',
    'button.yes' => 'Yes, I\'m sure',
    'button.no' => 'No, cancel',
    'button.cancel' => 'Cancel',
    'button.close' => 'Close',
    'button.accept' => 'I agree',
    'button.export' => 'Export',
    'button.updateWeb' => 'Update Website',
    'button.shutdown' => 'Shutdown',

    //Tabs
    'tab.groups' => 'Groups',
    'tab.users' => 'Users',
    'tab.llms' => 'Models',
    'tab.settings' => 'Website Settings',
    'tab.kernel' => 'Kernel',
    'tab.workers' => 'Workers',

    //Groups
    'button.new_group' => 'New Group',
    'header.create_group' => 'Create a new group',
    'label.tab_permissions' => 'Page Permissions',
    'label.invite_code' => 'Invite Code',
    'label.group_name' => 'Name',
    'label.invite_code' => 'Invite Code',
    'placeholder.invite_code' => 'Invite Code',
    'label.describe' => 'Description',
    'placeholder.group_name' => 'Group Name',
    'placeholder.group_detail' => 'Group description',
    'label.read' => 'Read',
    'label.delete' => 'Delete',
    'label.update' => 'Update',
    'label.create' => 'Create',
    'label.llm_permission.disabled' => 'Model Usage Permissions (Disabled Models)',
    'label.llm_permission.enabled' => 'Model Usage Permissions (Enabled Models)',
    'header.edit_group' => 'Edit Group',
    'hint.group_updated' => 'Group updated successfully!',
    'hint.group_created' => 'Group created successfully!',
    'modal.delete_group.header' => 'Are you sure you want to delete this group?',

    //Users
    'header.menu' => 'Main Menu',
    'header.group_selector' => 'Group Selector',
    'header.fuzzy_search' => 'Fuzzy Search',
    'header.create_user' => 'Create User',
    'label.group_selector' => 'Filter users from groups',
    'label.fuzzy_search' => 'Search users by name or email',
    'label.create_user' => 'Create a user profile',

    'create_user.header' => 'Create a new account',
    'create_user.joined_group' => 'Joined Groups',
    'label.members' => 'Members',
    'label.other_users' => 'Members without group',
    'button.return_group_list' => 'Return to Group List',
    'placeholder.search_user' => 'Search by email or name',
    'hint.enter_to_search' => 'Press Enter to search',

    'group_selector.header' => 'Edit User',
    'placeholder.email' => 'User Email',
    'placeholder.username' => 'User Name',
    'label.name' => 'Name',
    'modal.delete_user.header' => 'Are you sure you want to delete this user?',
    'button.cancel' => 'Cancel',
    'label.email' => 'Email',
    'label.password' => 'Password',
    'label.update_password' => 'Update Password',
    'label.detail' => 'Details',
    'placeholder.new_password' => 'New Password',
    'label.require_change_password' => 'Require password change on next login',
    'label.extra_setting' => 'Extra Settings',
    'label.created_at' => 'Created At',
    'label.updated_at' => 'Updated At',

    //LLMs
    'button.new_model' => 'New Model',
    'label.enabled_models' => 'Enabled Models',
    'label.disabled_models' => 'Disabled Models',
    'header.create_model' => 'Create Model Profile',
    'modal.create_model.header' => 'Are you sure you want to create this profile?',
    'label.model_image' => 'Model Avatar',
    'label.model_name' => 'Model Name',
    'label.order' => 'Display Order',
    'label.link' => 'External Link',
    'placeholder.description' => 'Description of this model',
    'label.version' => 'Version',
    'label.access_code' => 'Access Code',
    'placeholder.link' => 'External link of this model',
    'header.update_model' => 'Edit Model Profile',
    'label.description' => 'Description',
    'modal.update_model.header' => 'Are you sure you want to update this Language Model profile?',
    'modal.delete_model.header' => 'Are you sure you want to delete this Language Model profile?',
    'modal.confirm_setting_modal.shrink_max_upload_file_count' => 'Reducing the maximum file upload limit will delete excess user files. Are you sure?',

    //setting
    'header.settings' => 'Website Settings',
    'header.updateWeb' => 'Website Update Progress',
    'label.settings' => 'All website related settings can be adjusted here',
    'label.allow_register' => 'Allow Registration',
    'button.reset_redis' => 'Reset Redis Cache',
    'hint.saved' => 'Saved',
    'hint.redis_cache_cleared' => 'Redis cache cleared',
    'label.updateweb_git_ssh_command' =>'Environment Variable GIT_SSH_COMMAND',
    'label.updateweb_path' => 'Environment Variable PATH',
    'label.need_invite' => 'Registration requires an invite code',
    'label.footer_warning' => 'Dialog footer warning',
    'label.anno' => 'System Announcement',
    'label.tos' => 'Terms of Service',
    'label.upload_max_size_mb' => 'Upload file size limit (MB)',
    'label.upload_allowed_extensions' => 'Allowed upload file extensions (* means any extension)',
    'label.upload_max_file_count' => 'Maximum file upload count (-1 means unlimited)',

    //kernel
    'label.kernel_location' => 'Kernel Connection Location',
    'label.safety_guard_API' => 'Safety Guard API Connection Location',
    'label.ready' => 'Ready',
    'label.busy' => 'Busy',
    'label.accesscode' => 'Access Code',
    'label.endpoint' => 'Connection Endpoint',
    'label.status' => 'Status',
    'label.historyid' => 'History ID',
    'label.userid' => 'User ID',
    'button.new_executor' => 'New Executor',
    'label.edit_executor' => 'Edit Executor',
    'label.create_executor' => 'Create Executor',

    //Workers
    'label.failed' => 'Operation failed. Please try again.',
    'label.loading' => 'Loading...',
    'label.last_refresh' => 'Last refreshed at: :time',
    'label.current_worker_count' => 'Current Worker Count',
    'label.error_fetching_worker_count' => 'Error fetching worker count.',
    'label.last_refresh_time' => 'Last Refresh',
    'label.seconds_ago' => 'seconds ago',
    'label.error' => 'Error',
    'label.valid_worker_count' => 'Please enter a valid worker count.',

    'button.start' => 'Start Workers',
    'button.stop' => 'Stop All Workers',
    'button.confirm' => 'Confirm',
    'button.cancel' => 'Cancel',

    'modal.start.title' => 'Start Workers',
    'modal.start.label' => 'Worker Count:',
    'modal.stop.title' => 'Stop All Workers',
    'modal.stop.confirm' => 'Are you sure you want to stop all workers?',
];