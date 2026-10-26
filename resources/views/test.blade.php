<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP SSE Example</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Server-Sent Events Test</h1>
    <div id="messages"></div>

    <script>
        // Create a new EventSource instance
        const eventSource = new EventSource('/sse');

        console.log(eventSource);

        // Listen for messages from the server
        eventSource.onmessage = function(event) {
            const newElement = document.createElement('div');
            newElement.textContent = `Message: ${event.data}`;
            document.getElementById('messages').appendChild(newElement);
        };

        // Handle errors
        eventSource.onerror = function(error) {
            console.error('EventSource failed:', error);
        };
    </script>
</body>
</html>
