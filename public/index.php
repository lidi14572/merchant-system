<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 路由处理
$route = $_GET['route'] ?? 'home';

switch($route) {
    case 'home':
        include '../public/home.php';
        break;
    case 'product':
        include '../public/product.php';
        break;
    default:
        include '../public/404.php';
}
