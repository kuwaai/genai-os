<?php

return [
    'route' => '管理',
    'interface.header' => '管理员管理界面',
    'button.delete' => '删除',
    'button.update' => '更新',
    'button.create' => '创建',
    'button.save' => '保存',
    'button.yes' => '是，我确定',
    'button.no' => '否，取消',
    'button.cancel' => '取消',
    'button.close' => '关闭',
    'button.accept' => '我同意',
    'button.export' => '导出',
    'button.updateWeb' => '强制更新网站系统',
    'button.shutdown' => '关机',
    'button.refresh' => '刷新',

    //Tabs
    'tab.groups' => '群组',
    'tab.users' => '用户',
    'tab.llms' => '模型',
    'tab.settings' => '设置',
    'tab.kernel' => '核心',
    'tab.workers' => '任务处理器',

    //Groups
    'button.new_group' => '新增群组',
    'header.create_group' => '创建一个新群组',
    'label.tab_permissions' => '页面权限',
    'label.invite_code' => '邀请码',
    'label.group_name' => '名称',
    'label.invite_code' => '邀请码',
    'placeholder.invite_code' => '邀请码',
    'label.describe' => '介绍',
    'placeholder.group_name' => '群组名称',
    'placeholder.group_detail' => '群组注释',
    'label.read' => '读取',
    'label.delete' => '删除',
    'label.update' => '更新',
    'label.create' => '新增',
    'label.llm_permission.disabled' => '模型使用权限(已停用模型)',
    'label.llm_permission.enabled' => '模型使用权限(已启用模型)',
    'header.edit_group' => '编辑群组',
    'hint.group_updated' => '群组更新成功！',
    'hint.group_created' => '群组创建成功！',
    'modal.delete_group.header' => '您确定要删除该群组吗',

    //Users
    'header.menu' => '主菜单',
    'header.group_selector' => '群组选择器',
    'header.fuzzy_search' => '模糊搜索器',
    'header.create_user' => '创建用户',
    'label.group_selector' => '从群组开始筛选用户',
    'label.fuzzy_search' => '使用名称或邮箱搜索用户',
    'label.create_user' => '创建一个用户的配置文件',

    'create_user.header' => '创建一个新的账户',
    'create_user.joined_group' => '加入的群组',
    'label.members' => '个成员',
    'label.other_users' => '无群组成员',
    'button.return_group_list' => '返回群组列表',
    'placeholder.search_user' => '搜索邮箱或名称',
    'hint.enter_to_search' => '按下Enter来搜索',

    'group_selector.header' => '编辑用户',
    'placeholder.email' => '用户邮箱',
    'placeholder.username' => '用户名',
    'label.name' => '名称',
    'modal.delete_user.header' => '确定要删除用户吗',
    'button.cancel' => '取消',
    'label.email' => '电子邮箱',
    'label.password' => '密码',
    'label.update_password' => '更新密码',
    'label.detail' => '详细说明',
    'placeholder.new_password' => '新密码',
    'label.require_change_password' => '下次登录要求修改密码',
    'label.extra_setting' => '额外设置',
    'label.created_at' => '创建于',
    'label.updated_at' => '更新于',

    //LLMs
    'button.new_model' => '新增模型',
    'label.enabled_models' => '已启用模型',
    'label.disabled_models' => '已停用模型',
    'header.create_model' => '创建模型配置文件',
    'modal.create_model.header' => '您确定要创建这个配置文件吗？',
    'label.model_image' => '模型头像',
    'label.model_name' => '模型名称',
    'label.order' => '展示顺序',
    'label.link' => '外部链接',
    'placeholder.description' => '这个模型的相关介绍',
    'label.version' => '版本',
    'label.access_code' => '存取代码',
    'placeholder.link' => '该模型的外部相关链接',
    'header.update_model' => '编辑模型配置文件',
    'label.description' => '叙述',
    'modal.update_model.header' => '您确定要更新这个语言模型配置文件吗',
    'modal.delete_model.header' => '您确定要删除这个语言模型配置文件吗',
    'modal.confirm_setting_modal.shrink_max_upload_file_count' => '降低上传文件总数限制会删除超出的用户文件，您确定吗?',

    //setting
    'header.setting.ui' => '用户界面',
    'header.setting.storage' => '存储空间',
    'header.setting.env' => '运行环境',
    'header.setting.debug' => '调试',
    'header.updateWeb' => '系统更新进度',
    'header.confirmUpdate' => '是否确定更新网站',
    'header.setting.kernel' => '核心',
    'label.kernel_location' => '核心连接位置',
    'label.safety_guard_API' => '安全过滤连接位置',
    'label.reloginWarning' => '网站更新会暂时将所有Worker重启，并在更新完毕后重新启动10个Worker，确定要开始进行更新了吗？',
    'label.settings' => '所有关于网站的设置都可在此调整',
    'label.allow_register' => '允许注册',
    'button.reset_redis' => '重置Redis缓存',
    'hint.saved' => '已保存',
    'hint.smtp_not_configured' => '没有设置SMTP连接方式！将不会启用邮箱验证功能',
    'hint.redis_cache_cleared' => 'Redis缓存已清除',
    'label.updateweb_git_ssh_command' =>'环境变量GIT_SSH_COMMAND',
    'label.updateweb_path' => '环境变量PATH',
    'label.need_invite' => '注册必须有邀请码',
    'label.footer_warning' => '对话底部警告',
    'label.anno' => '系统公告',
    'label.tos' => '服务条款',
    'label.upload_max_size_mb' => '上传文件大小限制(MB)',
    'label.upload_allowed_extensions' => '允许上传的副檔名 (* 表示任意副檔名)',
    'label.upload_max_file_count' => '上传文件总数限制 (-1 表示不限制数量)',

    //kernel
    'label.ready' => '就绪',
    'label.busy' => '忙碌',
    'label.accesscode' => '存取代码',
    'label.endpoint' => '连接接口',
    'label.status' => '使用状态',
    'label.historyid' => '记录ID',
    'label.userid' => '用户ID',
    'button.new_executor' => '新增执行器',
    'label.edit_executor' => '编辑执行器',
    'label.create_executor' => '新增执行器',

    //Workers
    'label.failed' => '操作失败。请再试一次。',
    'label.loading' => '加载中...',
    'label.last_refresh' => '最后刷新时间：:time',
    'label.current_worker_count' => '当前任务处理器数量',
    'label.error_fetching_worker_count' => '获取任务处理器数量时出错。',
    'label.last_refresh_time' => '最后刷新',
    'label.seconds_ago' => '秒前',
    'label.error' => '错误',
    'label.valid_worker_count' => '请输入有效的任务处理器数量。',
    'label.worker_started' => '任务处理器启动成功',
    'label.worker_start_failed' => '任务处理器启动失败：',
    'label.no_workers' => '没有已启动的任务处理器',
    'label.worker_stopped' => '任务处理器已停止',

    'button.start' => '启动任务处理器',
    'button.stop' => '停止所有任务处理器',
    'button.confirm' => '确认',
    'button.cancel' => '取消',

    'modal.start.title' => '启动任务处理器',
    'modal.start.label' => '任务处理器数量：',
    'modal.stop.title' => '停止所有任务处理器',
    'modal.stop.confirm' => '您确定要停止所有任务处理器吗？',
];