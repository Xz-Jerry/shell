<?php
session_start();

// Password hash (bcrypt) to verify
$correct_password_hash = '$2y$10$PvUkAKuHdPtm0Z4.F.LnZOaDzy9nbSW08Kwm0KFEzzcC0vRpZudmW';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['password'])) {
        // Handle login
        if (password_verify($_POST['password'], $correct_password_hash)) {
            $_SESSION['authenticated'] = true;
        } else {
            $error = "Password incorrect!";
        }
    } elseif (isset($_POST['directory']) && isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        // Handle permission change
        $directory = rtrim($_POST['directory'], '/'); 
        $dirPermissions = isset($_POST['dir_permissions']) ? $_POST['dir_permissions'] : null;
        $filePermissions = isset($_POST['file_permissions']) ? $_POST['file_permissions'] : null;

        function setPermissions($dir, $dirPerm = null, $filePerm = null) {
            if ($dirPerm) {
                chmod($dir, octdec($dirPerm));
            }

            $dirHandle = opendir($dir);
            if ($dirHandle) {
                while (($file = readdir($dirHandle)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($filePath)) {
                            setPermissions($filePath, $dirPerm, $filePerm); // Recursively set permissions on subdirectories
                        } else if ($filePerm) {
                            chmod($filePath, octdec($filePerm)); // Set file permissions
                        }
                    }
                }
                closedir($dirHandle);
            } else {
                echo "Error: Unable to open directory $dir";
            }
        }

        setPermissions($directory, $dirPermissions, $filePermissions);
        $successMessage = "Permissions telah diatur untuk direktori dan semua subdirektori serta file di dalamnya.";
    }
}

// Function to get list of directories
function getDirectories($dir) {
    $directories = [];
    $dirHandle = opendir($dir);

    if (!$dirHandle) {
        echo "Error: Unable to open directory $dir";
        return $directories;
    }

    while (($file = readdir($dirHandle)) !== false) {
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

        if ($file != '.' && $file != '..' && is_dir($fullPath)) {
            $directories[] = $file;
        }
    }

    closedir($dirHandle);
    return $directories;
}

// Get the base directory and list of directories
$baseDirectory = realpath(getcwd());
$directories = getDirectories($baseDirectory);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set File Permissions - Xjerry</title>
    <!-- Add CSS for styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Set File Permissions - Xjerry</h2>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true): ?>
                    <?php if (isset($successMessage)): ?>
                        <p class="text-success"><?php echo htmlspecialchars($successMessage); ?></p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="directory">Pilih direktori:</label>
                            <select class="form-control" id="directory" name="directory" required>
                                <?php foreach ($directories as $dir): ?>
                                    <option value="<?php echo htmlspecialchars($baseDirectory . DIRECTORY_SEPARATOR . $dir); ?>">
                                        <?php echo htmlspecialchars($dir); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dir_permissions">Permissions untuk direktori (contoh: 0755):</label>
                            <input type="text" class="form-control" id="dir_permissions" name="dir_permissions" placeholder="Contoh: 0755">
                        </div>
                        <div class="form-group">
                            <label for="file_permissions">Permissions untuk file (contoh: 0644):</label>
                            <input type="text" class="form-control" id="file_permissions" name="file_permissions" placeholder="Contoh: 0644">
                        </div>
                        <button type="submit" class="btn btn-primary">Set Permissions</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="password">Masukkan password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <?php if (isset($error)): ?>
                            <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add JavaScript for interactivity -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
