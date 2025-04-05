<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商户订单系统 - 管理后台</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">商户订单系统</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="merchants.php">商户管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">商品管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">订单管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistics.php">统计报表</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">退出登录</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">商户总数</h5>
                        <p class="card-text" id="merchantCount">加载中...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">今日订单</h5>
                        <p class="card-text" id="todayOrders">加载中...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">今日销售额</h5>
                        <p class="card-text" id="todaySales">加载中...</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">支付成功率</h5>
                        <p class="card-text" id="paymentSuccess">加载中...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        最近订单
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>商户</th>
                                    <th>商品</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                    <th>创建时间</th>
                                </tr>
                            </thead>
                            <tbody id="recentOrders">
                                <tr>
                                    <td colspan="6" class="text-center">加载中...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // 加载统计数据
        function loadDashboardData() {
            $.ajax({
                url: 'api/dashboard.php',
                method: 'GET',
                success: function(response) {
                    $('#merchantCount').text(response.merchantCount);
                    $('#todayOrders').text(response.todayOrders);
                    $('#todaySales').text('$' + response.todaySales);
                    $('#paymentSuccess').text(response.paymentSuccess + '%');
                },
                error: function() {
                    alert('加载数据失败');
                }
            });
        }

        // 加载最近订单
        function loadRecentOrders() {
            $.ajax({
                url: 'api/recent_orders.php',
                method: 'GET',
                success: function(response) {
                    let html = '';
                    response.orders.forEach(function(order) {
                        html += `
                            <tr>
                                <td>${order.order_number}</td>
                                <td>${order.merchant_name}</td>
                                <td>${order.product_name}</td>
                                <td>$${order.amount}</td>
                                <td><span class="badge bg-${order.status_color}">${order.status}</span></td>
                                <td>${order.created_at}</td>
                            </tr>
                        `;
                    });
                    $('#recentOrders').html(html);
                },
                error: function() {
                    alert('加载订单失败');
                }
            });
        }

        // 页面加载完成后执行
        $(document).ready(function() {
            loadDashboardData();
            loadRecentOrders();
            // 每60秒刷新一次数据
            setInterval(function() {
                loadDashboardData();
                loadRecentOrders();
            }, 60000);
        });
    </script>
</body>
</html>
