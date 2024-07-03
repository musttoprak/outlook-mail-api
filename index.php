<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>E-posta Ara</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        #loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.5);
            z-index: 9999;
        }
        #loading-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-top: 0;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        #emailResults {
            display: none;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div id="loading">
        <div id="loading-text">Yükleniyor...</div>
    </div>
    <div class="container">
        <h1>E-posta Ara</h1>
        <form id="emailSearchForm">
            <label for="sender">E-posta:</label>
            <input type="text" id="sender" name="sender" required>
            <button type="submit">Ara</button>
        </form>

        <div id="emailResults"></div>
    </div>

    <script>
        document.getElementById('emailSearchForm').addEventListener('submit', function(event) {
            event.preventDefault();
            document.getElementById('loading').style.display = 'block'; // Show loading screen
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'example.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('emailResults').innerHTML = xhr.responseText;
                    document.getElementById('emailResults').style.display = 'block'; // Show results
                } else {
                    console.log('Request failed.  Returned status of ' + xhr.status);
                }
                document.getElementById('loading').style.display = 'none'; // Hide loading screen
            };
            xhr.send(formData);
        });
    </script>
</body>
</html>
