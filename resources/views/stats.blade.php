<!DOCTYPE html>
<html>
<head>
    <title>Umrah4Them Stats</title>
    <script src="{{ asset('js/Chart.bundle.min.js') }}" type="text/javascript"></script>
</head>
<body>
    <canvas id="myChart" width="400" height="200"></canvas>
    <script>
    var ctx = document.getElementById("myChart");
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["Users", "Deceased", "Umrahs"],
            datasets: [{
                label: 'Total Count',
                data: {{ json_encode($data) }},
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            },
            title: {
                display: true,
                text: 'Umrah4Them Stats'
            }
        }
    });
    </script>
</body>
</html>
