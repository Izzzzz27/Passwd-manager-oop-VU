<?php
ini_set('session.save_path', __DIR__ . '/sessions');
session_start();
require_once 'classes/User.php';
require_once 'classes/PasswordGenerator.php';
require_once 'classes/PasswordManager.php';

$message = '';
$user = new User();

// Handle login
if (isset($_POST['login'])) {
    if ($user->login($_POST['username'], $_POST['password'])) {
        $_SESSION['user_id'] = $user->getId();
        $encryptionKey = $user->getEncryptionKey($_POST['password']);
        if ($encryptionKey) {
            $_SESSION['encryption_key'] = $encryptionKey;
            $message = "Login successful!";
        } else {
            $message = "Error retrieving encryption key";
        }
    } else {
        $message = "Invalid username or password";
    }
}

// Handle registration
if (isset($_POST['register'])) {
    if ($user->register($_POST['username'], $_POST['password'])) {
        $message = "Registration successful! Please login.";
    } else {
        $message = "Registration failed. Username might be taken.";
    }
}

// Handle password generation and storage
if (isset($_POST['generate']) && isset($_SESSION['user_id']) && isset($_SESSION['encryption_key'])) {
    $generator = new PasswordGenerator(
        $_POST['length'],
        $_POST['uppercase'],
        $_POST['lowercase'],
        $_POST['numbers'],
        $_POST['special']
    );
    
    $password = $generator->generate();
    $passwordManager = new PasswordManager($_SESSION['user_id'], $_SESSION['encryption_key']);
    
    if ($passwordManager->savePassword($_POST['website'], $password)) {
        $message = "Password saved successfully!";
    } else {
        $message = "Failed to save password.";
    }
}

// Handle manual password storage
if (isset($_POST['save_manual']) && isset($_SESSION['user_id']) && isset($_SESSION['encryption_key'])) {
    $passwordManager = new PasswordManager($_SESSION['user_id'], $_SESSION['encryption_key']);
    
    if ($passwordManager->savePassword($_POST['website'], $_POST['manual_password'])) {
        $message = "Password saved successfully!";
    } else {
        $message = "Failed to save password.";
    }
}

// Handle website password update
if (isset($_POST['update_website_password']) && isset($_SESSION['user_id']) && isset($_SESSION['encryption_key'])) {
    $passwordManager = new PasswordManager($_SESSION['user_id'], $_SESSION['encryption_key']);
    
    if ($passwordManager->updatePassword($_POST['website'], $_POST['new_password'])) {
        $message = "Website password updated successfully!";
    } else {
        $message = "Failed to update website password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>password keeper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9ecef;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-top: 25px;
        }
        .btn-primary {
            background-color: #4a90e2;
            border-color: #4a90e2;
        }
        .btn-success {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        .btn-warning {
            background-color: #e67e22;
            border-color: #e67e22;
        }
        .btn-danger {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }
        h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
        }
        .table {
            background-color: #ffffff;
        }
        .table-dark {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- Login/Register Form -->
            <div class="row">
                <div class="col-md-6">
                    <h2>sign in</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label>username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">sign in</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h2>new account</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label>username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-success">create account</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Password Management Forms -->
            <div class="row">
                <div class="col-md-6">
                    <!-- Generate Password Form -->
                    <h2>create password</h2>
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <label>website</label>
                            <input type="text" name="website" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>length</label>
                            <input type="number" name="length" class="form-control" value="12" min="8" max="32" required>
                        </div>
                        <div class="mb-3">
                            <label>uppercase letters</label>
                            <input type="number" name="uppercase" class="form-control" value="3" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>lowercase letters</label>
                            <input type="number" name="lowercase" class="form-control" value="3" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>numbers</label>
                            <input type="number" name="numbers" class="form-control" value="3" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label>special characters</label>
                            <input type="number" name="special" class="form-control" value="3" min="0" required>
                        </div>
                        <button type="submit" name="generate" class="btn btn-primary">generate & save</button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <!-- Manual Password Form -->
                    <h2>save password</h2>
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <label>website</label>
                            <input type="text" name="website" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>password</label>
                            <input type="password" name="manual_password" class="form-control" required>
                        </div>
                        <button type="submit" name="save_manual" class="btn btn-success">save</button>
                    </form>
                </div>
            </div>

            <!-- Display Saved Passwords -->
            <h2>saved passwords</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>website</th>
                            <th>password</th>
                            <th>created</th>
                            <th>action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $passwordManager = new PasswordManager($_SESSION['user_id'], $_SESSION['encryption_key']);
                        $passwords = $passwordManager->getPasswords();
                        foreach ($passwords as $pwd): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pwd['website']); ?></td>
                                <td><?php echo htmlspecialchars($pwd['password']); ?></td>
                                <td><?php echo $pwd['created_at']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" 
                                            onclick="showUpdateForm('<?php echo htmlspecialchars($pwd['website']); ?>')">
                                        update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Update Password Form (Hidden by default) -->
            <div id="updateForm" style="display: none;" class="mt-4">
                <h2>update website password</h2>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label>website</label>
                        <input type="text" name="website" id="updateWebsite" class="form-control" readonly required>
                    </div>
                    <div class="mb-3">
                        <label>new password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" name="update_website_password" class="btn btn-warning">update</button>
                    <button type="button" class="btn btn-secondary" onclick="hideUpdateForm()">cancel</button>
                </form>
            </div>

            <a href="logout.php" class="btn btn-danger">sign out</a>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-info mt-3">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showUpdateForm(website) {
            document.getElementById('updateWebsite').value = website;
            document.getElementById('updateForm').style.display = 'block';
        }
        
        function hideUpdateForm() {
            document.getElementById('updateForm').style.display = 'none';
        }
    </script>
</body>
</html> 