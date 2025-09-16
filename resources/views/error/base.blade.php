<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .error-container {
            max-width: 600px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .error-details {
            color: #6c757d;
        }
        a.btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">@yield('code')</div>
        <div class="error-message">@yield('message')</div>
        <div class="error-details">@yield('details')</div>
        <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
    </div>
</body>
</html>
