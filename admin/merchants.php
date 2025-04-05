<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 检查登录状态
checkAdminLogin();

// 获取商户列表
function getMerchants() {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            m.*, 
            COUNT(DISTINCT p.id) as product_count,
            COUNT(DISTINCT o.id) as order_count,
            SUM(CASE WHEN o.status = 'paid' THEN o.amount ELSE 0 END) as total_sales
        FROM merchants m
        LEFT JOIN products p ON m.id = p.merchant_id
        LEFT JOIN orders o ON m.id = o.merchant_id
        GROUP BY m.id
        ORDER BY m.created_at DESC
    ");
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商户管理 - 商户订单系统</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .merchant-details {
            display: none;
        }
        .merchant-row.active {
            background-color: #f8f9fa;
        }
        .status-badge {
            min-width: 80px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- 左侧商户列表 -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">商户列表</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMerchantModal">
                            <i class="fas fa-plus"></i> 添加商户
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach (getMerchants() as $merchant): ?>
                            <a href="#" class="list-group-item list-group-item-action merchant-item" data-id="<?php echo $merchant['id']; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo sanitize($merchant['company_name']); ?></h6>
                                        <small class="text-muted">
                                            联系人: <?php echo sanitize($merchant['contact_name']); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $merchant['status'] === 'active' ? 'success' : 'danger'; ?> status-badge">
                                        <?php echo $merchant['status'] === 'active' ? '正常' : '禁用'; ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted">商品: <?php echo $merchant['product_count']; ?></small>
                                    <small class="text-muted">订单: <?php echo $merchant['order_count']; ?></small>
                                    <small class="text-muted">销售额: $<?php echo formatAmount($merchant['total_sales']); ?></small>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右侧详细信息 -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#details">基本信息</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#products">商品管理</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#orders">订单记录</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#statistics">统计报表</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- 基本信息 -->
                            <div class="tab-pane fade show active" id="details">
                                <div id="merchantDetails">
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-store fa-3x mb-3"></i>
                                        <p>请选择左侧商户查看详细信息</p>
                                    </div>
                                </div>
                            </div>

                            <!-- 商品管理 -->
                            <div class="tab-pane fade" id="products">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>商品列表</h5>
                                    <button type="button" class="btn btn-primary btn-sm" id="addProductBtn">
                                        <i class="fas fa-plus"></i> 添加商品
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="productsTable">
                                        <thead>
                                            <tr>
                                                <th>商品名称</th>
                                                <th>价格</th>
                                                <th>状态</th>
                                                <th>创建时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 订单记录 -->
                            <div class="tab-pane fade" id="orders">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="ordersTable">
                                        <thead>
                                            <tr>
                                                <th>订单号</th>
                                                <th>商品</th>
                                                <th>金额</th>
                                                <th>状态</th>
                                                <th>创建时间</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- 统计报表 -->
                            <div class="tab-pane fade" id="statistics">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">每日订单统计</h6>
                                                <canvas id="dailyOrdersChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">支付状态分布</h6>
                                                <canvas id="paymentStatusChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
