<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Admin API Test</h1>
    <button id="test-btn">Test API Call</button>
    <pre id="result"></pre>

    <script>
        $('#test-btn').click(function() {
            $('#result').text('Loading...');

            $.ajax({
                url: '{{ url("api/admins") }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    $('#result').text('SUCCESS!\n\n' + JSON.stringify(response, null, 2));
                },
                error: function(xhr, error, thrown) {
                    $('#result').text('ERROR!\n\nStatus: ' + xhr.status + '\nError: ' + error + '\n\nResponse:\n' + xhr.responseText);
                }
            });
        });
    </script>
</body>
</html>

