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
                dimred: 'rgb(255, 99, 132)',
                orange: 'rgb(255,103,0)',
                yellow: 'rgb(255, 205, 86)',
                dimgreen: 'rgb(75, 192, 192)',
                blue: 'rgb(0,48,143)',
                purple: 'rgb(153, 102, 255)',
                brightred: 'rgb(211,33,45)',
                grey: 'rgb(201, 203, 207)',
                teal: 'rgb(77,166,255)',
                green: 'rgb(102,255,0)',
                lightblue: 'rgb(124,185,232)'
            };
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <style>
            .column {
            float: left;
            width: 33.33%;
            }

            /* Clear floats after the columns */
            .row:after {
            content: "";
            display: table;
            clear: both;
            }

            /* Responsive layout - makes the three columns stack on top of each other instead of next to each other */
            @media screen and (max-width: 800px) {
                .column {
                width: 100%;
                font-size: 12px;
                }
            }
        </style>
    </head>
    <body>
    <div style="text-align:center;font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 24px;margin-top:10px; margin-bottom: 18px;">Updater Activity Data</div>
    <div class="row">
        <div class="column"><canvas id="usageChart"></canvas></div>
        <div class="column"><canvas id="deviceChart"></canvas></div>
        <div class="column"><canvas id="osChart"></div>
    </div> 

    <script>
    var updateLabels = [];
    var updateTotals = [];
    var updateUniques = [];
    for (let key in updateReport.topApps) {
        if (updateReport.topApps.hasOwnProperty(key)) {
            console.log(key, updateReport.topApps[key]);
            updateLabels.push(updateReport.topApps[key].appName);
            updateTotals.push(updateReport.topApps[key].count);
            updateUniques.push(updateReport.topApps[key].uniqueDevices);
        }
    }
    var ctx = document.getElementById('usageChart');
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
            data: updateTotals,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            },
            { 
            data: updateUniques,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            }]
        },
    });
    </script>

    <script>
    var deviceLabels = [];
    var deviceTotals = [];
    var deviceUniques = [];
    for (let key in updateReport.topDevices) {
        if (updateReport.topDevices.hasOwnProperty(key)) {
            console.log(key, updateReport.topDevices[key]);
            deviceLabels.push(updateReport.topDevices[key].deviceString);
            deviceTotals.push(updateReport.topDevices[key].count);
            deviceUniques.push(updateReport.topDevices[key].uniqueDevices);
        }
    }
    var ctx = document.getElementById('deviceChart');
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
                    text: 'Most Active Devices'
                }
            }
        },
        data: {
        labels: deviceLabels,
        datasets: [{ 
            data: deviceTotals,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            },
            { 
            data: deviceUniques,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            }]
        },
    });
    </script>

    <script>
    var osLabels = [];
    var osTotals = [];
    var osUniques = [];
    for (let key in updateReport.topOSVersions) {
        if (updateReport.topOSVersions.hasOwnProperty(key)) {
            console.log(key, updateReport.topOSVersions[key]);
            osLabels.push(updateReport.topOSVersions[key].osVersionString);
            osTotals.push(updateReport.topOSVersions[key].count);
            osUniques.push(updateReport.topOSVersions[key].uniqueDevices);
        }
    }
    var ctx = document.getElementById('osChart');
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
                    text: 'Most Active OS Versions'
                }
            }
        },
        data: {
        labels: osLabels,
        datasets: [{ 
            data: osTotals,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            },
            { 
            data: osUniques,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            }]
        },
    });
    </script>
    </body>
</html>