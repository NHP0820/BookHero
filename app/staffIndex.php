<?php
require '_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'admin') {
    temp('info', 'Please login first');
    redirect("page/staffLogin.php");
    exit;
}

$topProductStmt = $_db->query("
    SELECT p.name, SUM(od.quantity) AS total_sold
    FROM order_detail od
    JOIN product p ON od.product_id = p.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$topProducts = $topProductStmt->fetchAll(PDO::FETCH_ASSOC);

$topProductLabels = array_column($topProducts, 'name');
$topProductData = array_column($topProducts, 'total_sold');


include '_staffHead.php';
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding-bottom: 50px;
}

.section {
    background-color: #ffffff;
    width: 90%;
    max-width: 1000px;
    margin: 30px auto;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.section h2 {
    font-size: 26px;
    color: #333;
    margin-bottom: 20px;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

.button-group {
    margin-bottom: 20px;
}

.button-group button {
    background-color: #03A9F4;
    border: none;
    color: white;
    padding: 10px 20px;
    margin: 0 5px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

.button-group button:hover {
    background-color: #0288D1;
    transform: scale(1.05);
}

.button-group button.active {
    background-color: #01579B;
}

.chart-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    padding: 20px;
}

.chart-box {
    flex: 1 1 450px;
    max-width: 480px;
}
</style>

</head>

<body>

    <div class="chart-wrapper">
        <div class="section chart-box">
            <h2>📊 Top 5 Best-Selling Books</h2>
            <canvas id="topProductsChart"></canvas>
        </div>

        <div class="section chart-box">
            <h2>📈 Book Sales Report</h2>
            <div class="button-group">
                <button id="dailyBtn" onclick="showChart('daily')">Daily</button>
                <button id="weeklyBtn" onclick="showChart('weekly')">Weekly</button>
                <button id="yearlyBtn" onclick="showChart('yearly')">Yearly</button>
            </div>
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php
    
    $dailyStmt = $_db->query("
    SELECT DATE(order_date) AS date, SUM(quantity) AS total
    FROM `order` o
    JOIN order_detail od ON o.order_id = od.order_id
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date) ASC
");
    $dailySales = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

    $weeklyStmt = $_db->query("
    SELECT YEAR(order_date) AS year, WEEK(order_date) AS week, SUM(quantity) AS total
    FROM `order` o
    JOIN order_detail od ON o.order_id = od.order_id
    GROUP BY YEAR(order_date), WEEK(order_date)
    ORDER BY year, week ASC
");
    $weeklySales = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

    $yearlyStmt = $_db->query("
    SELECT YEAR(order_date) AS year, SUM(quantity) AS total
    FROM `order` o
    JOIN order_detail od ON o.order_id = od.order_id
    GROUP BY YEAR(order_date)
    ORDER BY year ASC
");
    $yearlySales = $yearlyStmt->fetchAll(PDO::FETCH_ASSOC);

    
    $dailyLabels = array_column($dailySales, 'date');
    $dailyData = array_column($dailySales, 'total');

    $weeklyLabels = array_map(function ($row) {
        return "{$row['year']} - Week {$row['week']}";
    }, $weeklySales);
    $weeklyData = array_column($weeklySales, 'total');

    $yearlyLabels = array_column($yearlySales, 'year');
    $yearlyData = array_column($yearlySales, 'total');
    ?>

    
    <script>
        let salesChart;

        
        const chartData = {
            daily: {
                labels: <?= json_encode($dailyLabels) ?>,
                data: <?= json_encode($dailyData) ?>,
                label: 'Daily Sales'
            },
            weekly: {
                labels: <?= json_encode($weeklyLabels) ?>,
                data: <?= json_encode($weeklyData) ?>,
                label: 'Weekly Sales'
            },
            yearly: {
                labels: <?= json_encode($yearlyLabels) ?>,
                data: <?= json_encode($yearlyData) ?>,
                label: 'Yearly Sales'
            }
        };

       
        function initChart(type) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData[type].labels,
                    datasets: [{
                        label: chartData[type].label,
                        data: chartData[type].data,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Book Sales Report'
                        }
                    },
                    scales: {
                        x: {
                            stacked: false
                        },
                        y: {
                            stacked: false
                        }
                    }
                }
            });
        }

        
        function showChart(type) {
            if (salesChart) {
                salesChart.destroy(); 
            }
            initChart(type); 
        }

        
        window.onload = function() {
            showChart('daily');
        };

        function renderTopProductsChart() {
            const ctx = document.getElementById('topProductsChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($topProductLabels) ?>,
                    datasets: [{
                        label: 'Top 5 Products',
                        data: <?= json_encode($topProductData) ?>,
                        backgroundColor: [
                            '#42A5F5',
                            '#66BB6A',
                            '#FFA726',
                            '#AB47BC',
                            '#FF7043'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 5 Best-Selling Books'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        window.onload = function () {
            showChart('daily');
            renderTopProductsChart(); // Call this after sales chart
        };

    </script>

    <?php include '_foot.php'; ?>