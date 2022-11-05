const CHART_COLORS = {
    blue: 'rgb(0,48,143)',
    purple: 'rgb(153, 102, 255)',
    dimred: 'rgb(255, 99, 132)',
    orange: 'rgb(255,103,0)',
    yellow: 'rgb(255, 205, 86)',
    dimgreen: 'rgb(75, 192, 192)',
    brightred: 'rgb(211,33,45)',
    grey: 'rgb(201, 203, 207)',
    teal: 'rgb(77,166,255)',
    green: 'rgb(102,255,0)',
    lightblue: 'rgb(124,185,232)'
};

var geoReport;
var geoRendered = false;

function renderReports() {
    //Overall Stats
    var statsDiv = document.getElementById("stats");
    statsDiv.innerHTML += "<p><b>Report Range:</b><br/>" + downloadReport.firstDate + " - " + downloadReport.lastDate + "</p>";
    statsDiv.innerHTML += "<p><b>Total Downloads:</b> " + downloadReport.totalDownloads + "</p>";
    statsDiv.innerHTML += "<p><b>Total Update Checks:</b> " + updateReport.totalChecks + "</p>";
    statsDiv.innerHTML += "<p><b>Unique Device Count:</b> " + updateReport.uniqueDevices + "</p>";

    //App Download Stats
    var appLabels = [];
    var appTotals = [];
    for (let key in downloadReport.topApps) {
        if (downloadReport.topApps.hasOwnProperty(key)) {
            //console.log(key, downloadReport.topApps[key]);
            appLabels.push(downloadReport.topApps[key].appName);
            appTotals.push(downloadReport.topApps[key].count);
        }
    }
    var ctx = document.getElementById('appsChart');
    var myChart = new Chart(ctx, {
        type: 'bar',
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                title: {
                    display: true,
                    text: 'Most Downloaded Apps'
                },
                legend: {
                    display: false
                }
            },
        },
        data: {
        labels: appLabels,
        datasets: [{ 
            label: 'Downloads',
            data: appTotals,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            }]
        },
    });

    //Download Device Stats
    var downloaderLabels = [];
    var downloaderTotals = [];
    for (let key in downloadReport.topClients) {
        if (downloadReport.topClients.hasOwnProperty(key)) {
            //console.log(key, downloadReport.topClients[key]);
            downloaderLabels.push(downloadReport.topClients[key].clientString);
            downloaderTotals.push(downloadReport.topClients[key].count);
        }
    }
    var ctx = document.getElementById('downloaderChart');
    var myChart = new Chart(ctx, {
        type: 'bar',
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Most Common Downloaders'
                },
                legend: {
                    display: false
                }
            },
        },
        data: {
        labels: downloaderLabels,
        datasets: [{ 
            label: 'Downloads',
            data: downloaderTotals,
            backgroundColor: Object.values(CHART_COLORS),
            fill: true,
            }]
        },
    });

    //App Update Stats
    var updateLabels = [];
    var updateTotals = [];
    var updateUniques = [];
    for (let key in updateReport.topApps) {
        if (updateReport.topApps.hasOwnProperty(key)) {
            //console.log(key, updateReport.topApps[key]);
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

    //Device Stats
    var deviceLabels = [];
    var deviceTotals = [];
    var deviceUniques = [];
    for (let key in updateReport.topDevices) {
        if (updateReport.topDevices.hasOwnProperty(key)) {
            //console.log(key, updateReport.topDevices[key]);
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

    //OS Stats
    var osLabels = [];
    var osTotals = [];
    var osUniques = [];
    for (let key in updateReport.topOSVersions) {
        if (updateReport.topOSVersions.hasOwnProperty(key)) {
            //console.log(key, updateReport.topOSVersions[key]);
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
    fetchGeoData();
}

function fetchGeoData() {
    var fetchingTxt = "<p><b>Fetching Geo Data <img src='spinner.gif' align='absmiddle'></b></p>";
    var readyTxt = "<p><b>Geo Data:</b> <a href='javascript:showGeoData();'>Click Here</a></p>";
    document.getElementById("stats").innerHTML += "<span id='geoShower'>" + fetchingTxt + "</span>";
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("DATA READY!");
            document.getElementById("geoShower").innerHTML = readyTxt;
            geoReport = JSON.parse(xhttp.responseText);
        }
    };
    xhttp.open("GET", "getgeodata.php", true);
    xhttp.send();
}

function showGeoData() {
    if (!geoRendered) {
        //Download Device Stats
        var geoLabels = [];
        var geoTotals = [];
        for (let key in geoReport.topRegions) {
            if (geoReport.topRegions.hasOwnProperty(key)) {
                console.log(key, geoReport.topRegions[key]);
                geoLabels.push(geoReport.topRegions[key].regionName);
                geoTotals.push(geoReport.topRegions[key].count);
            }
        }
        var ctx = document.getElementById('geoChart');
        var myChart = new Chart(ctx, {
            type: 'bar',
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Most Common Regions'
                    },
                    legend: {
                        display: false
                    }
                },
            },
            data: {
            labels: geoLabels,
            datasets: [{ 
                label: 'Regions',
                data: geoTotals,
                backgroundColor: Object.values(CHART_COLORS),
                fill: true,
                }]
            },
        });
        geoRendered = true;
    }
    openModal();
}

// Open the Modal
function openModal() {
    document.getElementById("geoModal").style.display = "block";
}

// Close the Modal
function closeModal() {
    document.getElementById("geoModal").style.display = "none";
}
