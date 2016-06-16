<!DOCTYPE html>
<html>
<head>
    <title>Umrah4Them Stats</title>
    <script src="{{ asset('js/Chart.bundle.min.js') }}" type="text/javascript"></script>
</head>
<body>
    <canvas id="data" width="400" height="200"></canvas>
    <script>
    var ctx = document.getElementById("data");
    var data = new Chart(ctx, {
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

    <hr/>

    <canvas id="umrahs_pie" width="400" height="200"></canvas>
    <script>
    var ctx = document.getElementById("umrahs_pie");
    var umrahs_pie = new Chart(ctx,{
        type: 'pie',
        data: data = {
            labels: [
                "Done",
                "In Progress"
            ],
            datasets: [
                {
                    data: {{ json_encode($umrahs_pie) }},
                    backgroundColor: [
                        "#00FF00",
                        "#FFCE56"
                    ],
                }]
        },
        options: []
    });
    </script>
</body>
</html>
