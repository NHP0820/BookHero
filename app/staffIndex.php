<?php
require '_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'admin') {
    temp('info', 'Please login first');
    redirect("page/staffLogin.php");
    exit;
}

include '_staffHead.php';
?>

<style>
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
</style>


</head>

<body>

   
    <div style="width: 80%; margin: auto; text-align: center; margin-top: 20px;">

        
        <div class="button-group">
            <button id="dailyBtn" onclick="showChart('daily')">Daily</button>
            <button id="weeklyBtn" onclick="showChart('weekly')">Weekly</button>
            <button id="yearlyBtn" onclick="showChart('yearly')">Yearly</button>
        </div>


        
        <canvas id="salesChart" style="max-width: 100%;"></canvas>

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
    </script>

    <?php include '_foot.php'; ?>