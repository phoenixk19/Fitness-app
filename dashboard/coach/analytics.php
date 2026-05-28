<?php
// File: dashboard/coach/analytics.php
require_once 'includes/header.php';

$monthlyData = $db->query("
    SELECT DATE_FORMAT(start_date, '%b') as month, 
           COUNT(*) as total,
           SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM client_workouts
    WHERE client_id IN (SELECT client_id FROM coaches_clients WHERE coach_id = $userId)
    AND start_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY MONTH(start_date)
    ORDER BY start_date
")->fetch_all(MYSQLI_ASSOC) ?? [];

$months = array_column($monthlyData, 'month');
$completionRates = array_map(function($row) { return round(($row['completed'] / max($row['total'],1)) * 100); }, $monthlyData);
?>
<div class="page-title"><i class="bi bi-bar-chart"></i><h2>Analytics</h2></div>

<div class="row">
    <div class="col-md-6"><div class="chart-container"><h5>Monthly Workout Completion</h5><canvas id="monthlyChart" style="height:250px"></canvas></div></div>
    <div class="col-md-6"><div class="chart-container"><h5>Client Distribution</h5><canvas id="clientChart" style="height:250px"></canvas></div></div>
</div>

<?php
$page_specific_scripts = '
<script>
new Chart(document.getElementById("monthlyChart"),{type:"bar",data:{labels:'.json_encode($months).',datasets:[{label:"Completion %",data:'.json_encode($completionRates).',backgroundColor:"rgba(74,111,165,0.7)"}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{max:100,ticks:{callback:v=>v+"%"}}}}});
new Chart(document.getElementById("clientChart"),{type:"doughnut",data:{labels:["Active","Inactive","New"],datasets:[{data:[70,20,10],backgroundColor:["#4A6FA5","#dc3545","#28a745"]}]},options:{responsive:true,maintainAspectRatio:false}});
</script>';
require_once 'includes/footer.php';
?>