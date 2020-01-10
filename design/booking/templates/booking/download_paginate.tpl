<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Exporting CSV</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="//code.jquery.com/jquery-1.12.0.min.js" type="application/javascript"></script>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
        var EndPoint = "{'openpa_booking/export'|ezurl(no)}/";
        var Data = {ldelim}
            'query': '{$query|urlencode()}',
            'download_id': '{$download_id}',
            'iteration': {$iteration},
            'last': {$last},
            'count': {$count},
            'limit': {$limit}
        {rdelim};
        {literal}
        $(document).ready(function(){

            var setProgressBar = function(data){
                var perc = parseFloat(data.iteration*100/(data.count/data.limit)).toFixed(2);
                if(perc > 100) perc = 100;
                perc += '%';
                $('.progress-bar').css('width', perc).html(perc);
            };

            var iterate = function(data){
                if (data.query != null) {
                    var query = data.query;
                    data.query = null;
                    $.get(EndPoint + query, data, function (response) {
                        setProgressBar(response);
                        iterate(response);
                    });
                }else{
                    $('.progress').hide();
                    $('h2').html('File is ready');
                    $('.download').attr( 'href', EndPoint+'?download=1&download_id='+data.download_id).show();
                }
            };

            iterate(Data);
        });
        {/literal}
    </script>

</head>

<body>

<div class="container">

    <div class="col-md-12">

        <h2 class="console">Attendere caricamento dati...</h2>

        <div class="progress">
            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em;">0%</div>
        </div>

        <div class="text-center">
            <a href="#" class="download btn btn-success btn-lg" style="display: none">Download file</a>
        </div>

        <div class="text-center" style="margin-top: 30px">
            <a href="{'openpa_booking/export'|ezurl(no)}/" class="btn btn-danger btn-lg">Torna a Esporta prenotazioni</a>
        </div>

    </div>


</div>


</body>
</html>
