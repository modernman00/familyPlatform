<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP SSE Example</title>
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
