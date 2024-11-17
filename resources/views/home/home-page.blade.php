<!-- resources/views/home/home-page.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
        }
        .logout-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #dc3545; /* Red background */
            color: white; /* White text */
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .logout-button:hover {
            background-color: #c82333; /* Darker red background on hover */
        }
        .custom-prompt-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #28a745; /* Green background */
            color: white; /* White text */
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .custom-prompt-button:hover {
            background-color: #218838; /* Darker green background on hover */
        }
        .upload-container {
            background-color: #fff;
            border: 2px dashed #d3d3d3;
            border-radius: 10px;
            width: 50%;
            max-width: 600px;
            text-align: center;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .upload-container.dragging {
            border-color: #6c63ff;
        }
        .upload-icon {
            font-size: 50px;
            color: #6c63ff;
            margin-bottom: 10px;
        }
        .upload-text {
            font-size: 18px;
            color: #6c63ff;
            margin-bottom: 5px;
        }
        .upload-link {
            color: #6c63ff;
            text-decoration: none;
            font-weight: bold;
        }
        .upload-link:hover {
            text-decoration: underline;
        }
        .upload-button {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto 0;
            padding: 10px 0;
            background-color: #6c63ff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }
        .upload-button:hover {
            background-color: #5751d9;
        }
        input[type="file"] {
            display: none;
        }
        .file-list {
            font-size: 14px;
            color: #333;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<a href="{{ url('/logout') }}" class="logout-button">Logout</a>
<button class="custom-prompt-button" onclick="goToCustomPrompt()">Custom Prompt Edit</button>

<div class="upload-container" id="uploadContainer">
    <div class="upload-icon">📁</div>
    <div class="upload-text">Drag & drop files or <a href="#" class="upload-link" id="browseLink">Browse</a></div>
    <div class="upload-formats">Supported formats: PDF</div>
    <div class="file-list" id="fileList"></div>

    <form id="uploadForm" action="{{$apiUrl}}/upload/{{ $session_id }}" method="POST" enctype="multipart/form-data">
    <input type="file" name="files[]" id="fileInput" accept=".pdf" multiple>
    <button type="submit">Upload Files</button>

</form>
</div>

<script>
    const uploadContainer = document.getElementById('uploadContainer');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const fileList = document.getElementById('fileList');

    document.getElementById('browseLink').addEventListener('click', function(event) {
        event.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        updateFileList();
    });

    uploadForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(uploadForm);
        
        fetch(uploadForm.action, {
            method: uploadForm.method,
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Parse JSON response
        })
        .then(result => {
            // Check if result contains session_id or error message
            console.log(result);
            if (result.session_id) {
                // Store total_files in localStorage
                localStorage.setItem('total_files', result.total_files);
                // Redirect to status page
                window.location.href = `/status?session_id=${result.session_id}`;
            } else {
                alert(result.error || 'An error occurred during file upload');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error occurred during upload. Make sure your backend is working');
        });
    });

    uploadContainer.addEventListener('dragover', function(event) {
        event.preventDefault();
        uploadContainer.classList.add('dragging');
    });

    uploadContainer.addEventListener('dragleave', function() {
        uploadContainer.classList.remove('dragging');
    });

    uploadContainer.addEventListener('drop', function(event) {
        event.preventDefault();
        uploadContainer.classList.remove('dragging');
        fileInput.files = event.dataTransfer.files;
        updateFileList();
    });

    function updateFileList() {
        const files = fileInput.files;
        const fileNames = Array.from(files).map(file => file.name);
        const truncatedFileNames = fileNames.map(name => name.length > 30 ? name.slice(0, 30) + '...' : name);
        fileList.textContent = truncatedFileNames.join(', ');
    }

    function goToCustomPrompt() {
        window.location.href = '{{ url("/custom-prompt") }}';
    }

    // Optionally, you can display the stored total_files value on page load
    document.addEventListener('DOMContentLoaded', () => {
        const totalFiles = localStorage.getItem('total_files');
        const sessionId = localStorage.getItem('session_id'); // Get session_id from localStorage

        if (sessionId) {
            // Trigger the clear route
            fetch(`http://localhost:5000/clear/${sessionId}`, {
                method: 'DELETE', // Change to 'POST' if your backend requires it
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Session cleared:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        if (totalFiles) {
            console.log(`Total files uploaded: ${totalFiles}`);
            // You can also display this on the page if needed
            // For example:
            // document.getElementById('fileList').textContent = `Total files uploaded: ${totalFiles}`;
        }
    });
</script>

</body>
</html>
