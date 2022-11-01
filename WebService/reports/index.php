<?php 
$mimeType = "text/html";
?>
<html>
    <head>
        <title>webOS Archive - App Museum II Stats</title>
        <script>
            //TODO: this would be better as a web service call
            const downloadReport = <?php include('getdownloaddata.php'); ?>;
            const updateReport = <?php include('getupdatedata.php'); ?>;
            const CHART_COLORS = {
                red: 'rgb(255, 99, 132)',
                orange: 'rgb(255, 159, 64)',
                yellow: 'rgb(255, 205, 86)',
                green: 'rgb(75, 192, 192)',
                blue: 'rgb(54, 162, 235)',
                purple: 'rgb(153, 102, 255)',
                grey: 'rgb(201, 203, 207)'
            };
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body>
    Charts go here!
    <canvas id="myChart" width="400" height="400"></canvas>
    <script>
    var updateLabels = [];
    var updateData = [];
    for (let key in updateReport.topApps) {
        if (updateReport.topApps.hasOwnProperty(key)) {
            console.log(key, updateReport.topApps[key]);
            updateLabels.push(updateReport.topApps[key].appName);
            updateData.push(updateReport.topApps[key].count);
        }
    }
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Most Active Apps'
                }
            }
        },
        data: {
        labels: updateLabels,
        datasets: [{ 
            data: updateData,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            }]
        },
    });
    </script>
    </body>
</html>