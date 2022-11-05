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
        </script>
        <link rel="stylesheet" href="reports.css">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="reports.js"></script>
        </script>
    </head>
    <body onload="renderReports()">
        <?php include('../../menu.php'); ?>
        <div class="sectiontitle">Download Data</div>
        <div class="row">
            <div class="column" id="stats"><h2>Overall Stats</h2></div>    
            <div class="halfcolumn" style="margin-right: 20px;"><canvas id="appsChart"></canvas></div>
            <div class="halfcolumn"><canvas id="downloaderChart"></canvas></div>
        </div> 
        <div class="sectiontitle">Updater Activity Data</div>
        <div class="row">
            <div class="column"><canvas id="usageChart"></canvas></div>
            <div class="column"><canvas id="deviceChart"></canvas></div>
            <div class="column"><canvas id="osChart"></div>
        </div> 
        <div class="explainer">(Outer ring is Total Update Checks, Inner ring is Unique Device Update checks. As in: '# of inner ring devices have checked for updates # of outer ring times')</div>
    
        <!--Pop-up Geo Report-->
        <div id="geoModal" class="modal">
            <span class="close cursor" onclick="closeModal()">&times;</span>
            <div class="modal-content">
                <canvas id="geoChart">
            </div>
        </div>
    </body>
</html>