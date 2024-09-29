<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Activity Tracker</title>
    <link href="{{ asset(mix('app.css', 'vendor/activity-tracker')) }}" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="app" v-cloak></div>
    <script>
        window.ActivityTracker = @json($scriptVariables);
    </script>
    <script src="{{ asset(mix('app.js', 'vendor/activity-tracker')) }}"></script>
</body>
</html>
