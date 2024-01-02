<?php

/**
 * WEBSOCKET启动文件
 * CLI模式下运行
 */
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 定义运行目录
define('RUNTIME_PATH', __DIR__ . '/../runtime/');
// 绑定模块
define('BIND_MODULE','gateway/Sgateway');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
