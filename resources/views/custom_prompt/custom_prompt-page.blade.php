<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Text Editor</title>
    <style>
        .editor {
            width: 80%;
            height: 300px;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .button {
            margin-right: 10px;
        }
        .message {
            color: green;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <h2>Custom Prompt Editor</h2>
    <textarea id="editor" class="editor"></textarea>
    <br>
    <button class="button" onclick="saveText()">Save</button>
    <button class="button" onclick="downloadTemplate()">Download Template</button>
    <button class="button" onclick="goBack()">Back</button>
    <div id="message" class="message"></div>

    <script>
        // Fetch the current content when the page loads
        window.onload = function() {
            fetch('http://localhost:5000/custom-prompt')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editor').value = data.content;
                })
                .catch(error => console.error('Error fetching content:', error));
        };

        function saveText() {
            var content = document.getElementById('editor').value.trim();
            if (content === '') {
                console.log('No content to save');
                return;
            }

            fetch('http://localhost:5000/custom-prompt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content: content }),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                showMessage('Saved Successfully');
            })
            .catch(error => {
                console.error('Error saving content:', error);
            });
        }

        function showMessage(message) {
            var messageElement = document.getElementById('message');
            messageElement.innerText = message;
            messageElement.style.display = 'block';
            setTimeout(function() {
                messageElement.style.display = 'none';
            }, 2000);
        }

        function downloadTemplate() {
            window.location.href = '{{ route('custom_prompt.download') }}';
        }

        function goBack() {
            window.location.href = '/'; // Update if necessary
        }
    </script>
</body>
</html>
