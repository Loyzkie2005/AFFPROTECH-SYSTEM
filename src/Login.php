<?php
    session_start();

    // Handle logout if coming from logout action
    if (isset($_GET['logout'])) {
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Clear any remember me cookie if it exists
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }
        
        // Clear the success message from session if it exists
        unset($_SESSION['login_success']);
        
        // Redirect to login page with success message
        header("Location: Login.php?loggedout=1");
        exit();
    }

    // Show logout success message if just logged out
    $login_success = '';
    if (isset($_GET['loggedout'])) {
        $login_success = "You have been successfully logged out.";
    }

    require_once 'DBconnection.php';

    $username = $password = "";
    $username_err = $password_err = $login_err = "";

    // Handle Remember Me cookie for Student ID
    if (isset($_COOKIE['remember_me'])) {
        $username = $_COOKIE['remember_me'];
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate student_id
        if (empty(trim($_POST["student_id"]))) {
            $username_err = "Student ID is required";
        } else {
            $username = trim($_POST["student_id"]);
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Password is required";
        } else {
            $password = trim($_POST["password"]);
        }

        // If no validation errors, check credentials
        if (empty($username_err) && empty($password_err)) {
            // If the username is 'Admin2025', search by username column
            if ($username === 'Admin2025') {
                $sql = "SELECT user_id, username, first_name, last_name, password, student_id FROM afpro_users WHERE username = ?";
            } else {
                // For all other users, search by student_id column
                $sql = "SELECT user_id, username, first_name, last_name, password, student_id FROM afpro_users WHERE student_id = ?";
            }
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $user_id, $username_db, $first_name, $last_name, $hashed_password, $student_id);
                        mysqli_stmt_fetch($stmt);
                        
                        // Check if either password matches or student ID matches
                        if ($username_db === 'Admin2025') {
                            // Admin: only allow password
                            if (password_verify($password, $hashed_password)) {
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $user_id;
                                $_SESSION["username"] = $username_db;
                                $_SESSION["fullname"] = trim($first_name . ' ' . $last_name);
                                $_SESSION['user_id'] = $user_id;
                                $_SESSION["role"] = "admin";
                                
                                // Fetch and set profile image
                                $profileStmt = $conn->prepare("SELECT profile_image FROM afpro_users WHERE user_id = ?");
                                $profileStmt->bind_param("i", $user_id);
                                $profileStmt->execute();
                                $profileResult = $profileStmt->get_result();
                                if ($profileRow = $profileResult->fetch_assoc()) {
                                    $_SESSION["profile_image"] = !empty($profileRow['profile_image']) ? $profileRow['profile_image'] : '../img/profile.png';
                                } else {
                                    $_SESSION["profile_image"] = '../img/profile.png';
                                }
                                $profileStmt->close();

                                if (!empty($_POST['remember_me'])) {
                                    setcookie('remember_me', $username, time() + (86400 * 30), "/");
                                } else {
                                    setcookie('remember_me', '', time() - 3600, "/");
                                }
                                header("Location: Admin.php");
                                exit;
                            } else {
                                $login_err = "Invalid username or password.";
                            }
                        } else {
                            // Other users: allow password or student ID
                            if (password_verify($password, $hashed_password) || $password === $student_id) {
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $user_id;
                                $_SESSION["username"] = $username_db;
                                $_SESSION["fullname"] = trim($first_name . ' ' . $last_name);
                                $_SESSION['user_id'] = $user_id;
                                $_SESSION["role"] = "user";

                                // Fetch and set profile image
                                $profileStmt = $conn->prepare("SELECT profile_image FROM afpro_users WHERE user_id = ?");
                                $profileStmt->bind_param("i", $user_id);
                                $profileStmt->execute();
                                $profileResult = $profileStmt->get_result();
                                if ($profileRow = $profileResult->fetch_assoc()) {
                                    $_SESSION["profile_image"] = !empty($profileRow['profile_image']) ? $profileRow['profile_image'] : '../img/profile.png';
                                } else {
                                    $_SESSION["profile_image"] = '../img/profile.png';
                                }
                                $profileStmt->close();

                                if (!empty($_POST['remember_me'])) {
                                    setcookie('remember_me', $username, time() + (86400 * 30), "/");
                                } else {
                                    setcookie('remember_me', '', time() - 3600, "/");
                                }
                                header("Location: UserDashboard.php");
                                exit;
                            } else {
                                $login_err = "Invalid username or password.";
                            }
                        }
                    } else {
                        $login_err = "Invalid username or password.";
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
        mysqli_close($conn);
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>USTP OROQUIETA AFFROTECH</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="Login.css" rel="stylesheet">
        <link rel="icon" href="../img/logo.jpg">
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <img src="../img/affprotechicon.png" alt="Logo" class="login-logo">
                <div class="text-center">
                    <h2>WELCOME</h2>
                </div>
                
                <?php if (!empty($login_err)): ?>
                    <div class="alert alert-danger text-center"><?php echo $login_err; ?></div>
                <?php endif; ?>

                <?php if (!empty($login_success)): ?>
                    <div class="alert alert-success text-center"><?php echo $login_success; ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm">
                                <div class="mb-3">
                                    <label for="login-student-id" class="form-label">Username</label>
                                    <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="login-student-id" name="student_id" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
                                    <?php if (!empty($username_err)): ?>
                                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3 position-relative">
                                    <label for="login-password" class="form-label">Password</label>
                                    <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="login-password" name="password" placeholder="Password">
                                    <span class="toggle-password-icon" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                    <div id="caps-lock-warning" style="display:none; color:#dc3545; font-size:0.92em; margin-top:2px;">Caps Lock is ON</div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remember-me" name="remember_me" <?php if ((isset($_POST['remember_me']) && $_POST['remember_me']) || isset($_COOKIE['remember_me'])) echo 'checked'; ?>>
                                        <label class="form-check-label" for="remember-me">Remember Me</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-login" id="loginBtn">
                                    <span class="login-text">Log In</span>
                                    <div class="spinner-wrapper">
                                        <span class="spinner"></span>
                                    </div>
                                </button>
                                <div class="signup-section">
                                    <hr class="my-4">
                                    <div class="d-flex align-items-center justify-content-center gap-3">
                                        <p class="text-muted mb-0">Don't have an account?</p>
                                        <a href="Signup.php" class="btn btn-outline-primary btn-signup">
                                            <i class="fas fa-user-plus"></i> Sign Up
                                        </a>
                                    </div>
                                </div>
                            </form>
            </div>
        </div>

        <style>
            .signup-section hr {
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                margin: 15px auto;
                width: 80%;
            }
            .btn-signup {
                padding: 8px 25px;
                font-weight: 500;
                transition: all 0.3s ease;
                border-radius: 8px;
                white-space: nowrap;
                border-color: #000080;
                color: #000080;
            }
            .btn-signup:hover {
                background-color: #000080;
                border-color: #000080;
                color: white;
                transform: translateY(-1px);
            }
            .signup-section p {
                font-size: 0.95rem;
            }
            .gap-3 {
                gap: 1rem !important;
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('login-password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const eyeIcon = this.querySelector('i');
                if (type === 'password') {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                } else {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                }
            });

            // Caps Lock warning
            const capsLockWarning = document.getElementById('caps-lock-warning');
            passwordInput.addEventListener('keyup', function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsLockWarning.style.display = 'block';
                } else {
                    capsLockWarning.style.display = 'none';
                }
            });
            passwordInput.addEventListener('blur', function() {
                capsLockWarning.style.display = 'none';
            });

            // Login form submission
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');

            loginForm.addEventListener('submit', function(e) {
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
            });

            // Hide alert after 1 second
            document.addEventListener('DOMContentLoaded', function() {
                const alert = document.querySelector('.alert-danger');
                if (alert) {
                    setTimeout(() => {
                        alert.remove();
                    }, 1000);
                }
            });
        </script>
        <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="Login.js"></script>
    </body>
    </html>
