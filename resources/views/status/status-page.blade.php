<!-- resources/views/status/status-page.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Status</title>
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
        .status-container {
            background-color: #fff;
            border: 2px solid #d3d3d3;
            border-radius: 10px;
            width: 50%;
            max-width: 600px;
            text-align: center;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .hidden {
            display: none;
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .download-button {
            margin-bottom: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .download-button:hover {
            background-color: #218838;
        }
        .progress-bar {
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 20px;
            background-color: #007bff;
            transition: width 0.3s ease;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 123, 255, 0.3);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 0.8s linear infinite;
            margin-left: 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<button class="back-button hidden" id="backButton" onclick="goBack()">Back</button>

<div class="status-container">
    <h2>Status:</h2>
    <p id="progressText">Please wait...<span class="spinner"></span></p>
    <div class="progress-bar">
        <div id="progressBarFill" class="progress-bar-fill" style="width: 0;"></div>
    </div>
    <p>Processed file <span id="fileNumber">0</span> of <span id="totalFiles">0</span> (<span id="percentageComplete">0</span>% Complete)</p>
    <p id="timer">Time taken: 0 mins 0 sec</p>
    <button id="downloadButton" class="download-button hidden">Download</button>
</div>

<script>
    let sessionId;
    let totalFiles;
    let timerInterval;
    let startTime;
    let processingStartTime = null; // Track when processing should start
    let requested = false; // Flag to ensure request is made only once
    let incrementInterval; // Interval ID for triggering /incrementcache
    const startProcessDelay = 1000; // 5 seconds in milliseconds
    const incrementDelay = 3000; // 3 seconds in milliseconds

    function goBack() {
        window.location.href = '/'; // Redirect to home page
    }

    function updateProgress(sessionId) {
        fetch(`/progress/${sessionId}`)
            .then(response => response.json())
            .then(data => {
                const { current } = data;
                const total = localStorage.getItem('total_files'); // Retrieve totalFiles from localStorage
                const percentageComplete = Math.floor((current / total) * 100);
                document.getElementById('fileNumber').textContent = current;
                document.getElementById('totalFiles').textContent = total;
                document.getElementById('percentageComplete').textContent = percentageComplete;

                // Update progress bar width
                document.getElementById('progressBarFill').style.width = `${percentageComplete}%`;

                if (current === 0 && processingStartTime === null) {
                    // Record the time when processing should start
                    processingStartTime = Date.now();
                }

                if (!requested && processingStartTime !== null) {
                    const elapsedTime = Date.now() - processingStartTime;
                    if (elapsedTime >= startProcessDelay) {
                        processingStartTime = null; // Reset the start time to prevent multiple requests
                        requested = true; // Set flag to true after the request is made
                        // Trigger the start process route
                        fetch(`/createcache/${sessionId}/${total}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Cache created:', data);
                                // // Start triggering /incrementcache every 3 seconds
                                //incrementInterval = setInterval(() => {
                                //    fetch(`/incrementcache/${sessionId}`)
                                //        .then(response => response.json())
                                //        .then(data => {
                                //            console.log('Cache incremented:', data);
                                //            if (data.current >= total) {
                                //               // Stop triggering /incrementcache when current matches total
                                //                clearInterval(incrementInterval);
                                //            }
                                //        })
                                //        .catch(error => console.error('Error:', error));
                                // }, incrementDelay);
                            })
                            .catch(error => console.error('Error:', error));
                    }
                }

                if (current < total) {
                    setTimeout(() => updateProgress(sessionId), 2000); // Poll every 2 seconds
                } else {
                    document.getElementById('downloadButton').classList.remove('hidden');
                    document.getElementById('progressText').textContent = ''; // Remove "Please wait..." text
                    clearInterval(timerInterval); // Stop the timer
                    document.getElementById('backButton').classList.remove('hidden'); // Show the back button
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function startTimer() {
        startTime = Date.now();
        timerInterval = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        const elapsedTime = Date.now() - startTime;
        const minutes = Math.floor(elapsedTime / 60000);
        const seconds = Math.floor((elapsedTime % 60000) / 1000);
        document.getElementById('timer').textContent = `Time taken: ${minutes} mins ${seconds} sec`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        sessionId = urlParams.get('session_id');
        if (sessionId) {
            // Store session_id in localStorage
            localStorage.setItem('session_id', sessionId);
            totalFiles = localStorage.getItem('total_files'); // Get totalFiles from localStorage
            if (totalFiles) {
                startTimer(); // Start the timer when processing starts
                updateProgress(sessionId);

                document.getElementById('downloadButton').addEventListener('click', function() {
                    // Request file download from Flask server
                    fetch(`{{$apiUrl}}/download/${sessionId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('File not found');
                            }
                            return response.blob();  // Parse the response as a Blob (for downloading the file)
                        })
                        .then(blob => {
                            const link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);  // Create an object URL for the Blob
                            link.download = `${sessionId}.zip`;  // Set the file name for download
                            link.click();  // Trigger the download
                        })
                        .catch(error => {
                            // Handle errors such as file not found
                            alert('Error: The file could not be found or downloaded. Please try again later.');
                            console.error('Download error:', error);
                        });
                });
            }
        }
    });


</script>

</body>
</html>
