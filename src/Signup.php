<?php
    session_start();

    require_once 'DBconnection.php';

    $username = $first_name = $last_name = $email = $password = "";
    $username_err = $first_name_err = $last_name_err = $email_err = $password_err = $signup_err = $signup_success = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate username (student_id)
        if (empty(trim($_POST["student_id"]))) {
            $username_err = "Student ID is required";
        } else {
            $username = trim($_POST["student_id"]);
            // Check if username or student_id already exists
            $sql = "SELECT user_id FROM afpro_users WHERE username = ? OR student_id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ss", $username, $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $username_err = "This Student ID is already taken.";
                }
                mysqli_stmt_close($stmt);
            }
        }

        // Validate first name
        if (empty(trim($_POST["first_name"]))) {
            $first_name_err = "First name is required";
        } else {
            $first_name = trim($_POST["first_name"]);
        }

        // Validate last name
        if (empty(trim($_POST["last_name"]))) {
            $last_name_err = "Last name is required";
        } else {
            $last_name = trim($_POST["last_name"]);
        }

        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Email is required";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invalid email format";
        } else {
            $email = trim($_POST["email"]);
            // Check if email already exists
            $sql = "SELECT user_id FROM afpro_users WHERE email = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $email_err = "This email is already taken.";
                }
                mysqli_stmt_close($stmt);
            }
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Password is required";
        } elseif (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must be at least 6 characters";
        } else {
            $password = trim($_POST["password"]);
        }

        // If no validation errors, insert new user
        if (empty($username_err) && empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err)) {
            $sql = "INSERT INTO afpro_users (username, student_id, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "ssssss", $username, $username, $first_name, $last_name, $email, $hashed_password);
                if (mysqli_stmt_execute($stmt)) {
                    $signup_success = "Account created successfully. Please log in.";
                    // Clear form fields
                    $username = $first_name = $last_name = $email = $password = "";
                    // Redirect to login page after a short delay
                    header("Refresh: 2; url=Login.php");
                } else {
                    $signup_err = "Something went wrong. Please try again.";
                }
                mysqli_stmt_close($stmt);
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
    <title>USTP OROQUIETA AFFROTECH - Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="Signup.css" rel="stylesheet">
    <link rel="icon" href="../img/logo.jpg">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="text-center">
                <h2>SIGN UP</h2>
            </div>
            
            <?php if (!empty($signup_err)): ?>
                <div class="alert alert-danger text-center"><?php echo $signup_err; ?></div>
            <?php endif; ?>

            <?php if (!empty($signup_success)): ?>
                <div class="alert alert-success text-center"><?php echo $signup_success; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="signupForm">
                <div class="mb-3">
                    <label for="signup-student-id" class="form-label">Student ID</label>
                    <input type="text" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="signup-student-id" name="student_id" placeholder="Enter Student ID" value="<?php echo htmlspecialchars($username); ?>">
                    <?php if (!empty($username_err)): ?>
                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="first-name" class="form-label">First Name</label>
                    <input type="text" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" id="first-name" name="first_name" placeholder="Enter First Name" value="<?php echo htmlspecialchars($first_name); ?>">
                    <?php if (!empty($first_name_err)): ?>
                        <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="last-name" class="form-label">Last Name</label>
                    <input type="text" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" id="last-name" name="last_name" placeholder="Enter Last Name" value="<?php echo htmlspecialchars($last_name); ?>">
                    <?php if (!empty($last_name_err)): ?>
                        <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" placeholder="Enter Email" value="<?php echo htmlspecialchars($email); ?>">
                    <?php if (!empty($email_err)): ?>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    <?php endif; ?>
                </div>
                <div class="mb-3 position-relative">
                    <label for="signup-password" class="form-label">Password</label>
                    <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="signup-password" name="password" placeholder="Enter Password">
                    <span class="toggle-password-icon" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                    <?php if (!empty($password_err)): ?>
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    <?php endif; ?>
                    <div id="caps-lock-warning" style="display:none; color:#dc3545; font-size:0.92em; margin-top:2px;">Caps Lock is ON</div>
                </div>
                <button type="submit" class="btn btn-login" id="signupBtn">
                    <span class="login-text">Sign Up</span>
                    <div class="spinner-wrapper">
                        <span class="spinner"></span>
                    </div>
                </button>
                <div class="signup-section">
                    <hr class="my-4">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <p class="text-muted mb-0">Already have an account?</p>
                        <a href="Login.php" class="btn btn-outline-primary btn-signup">
                            <i class="fas fa-sign-in-alt"></i> Log In
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
        .login-box {
            width: 100%;
            padding-top: 30px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Signup.js"></script>
</body>
</html>