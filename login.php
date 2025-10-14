<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup - Municipal Veterinary Office</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .forgot-password {
            margin-top: 15px;
            font-size: 18px;
            display: inline-block;
            text-decoration: none;
            color: #007bff;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: blue;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container" id="container">
        <!-- Sign Up Form -->
        <div class="form-container sign-up-container">
            <form action="actions/signup_action.php" method="POST">
                <h2>Create Account</h2>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <input type="text" name="name" placeholder="Name" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <div class="input-wrapper">
                    <span class="input-prefix">+63</span>
                    <input type="text" name="phone" placeholder="9123456789" pattern="\d{10}" maxlength="10" required
                        title="Please enter a 10-digit Philippine mobile number (e.g. 9123456789)" />
                </div>

                <input type="text" name="address" placeholder="Address" />
                <select name="role" hidden>
                    <option value="user" selected>User</option>
                </select>
                <button type="submit">Sign Up</button>
            </form>
        </div>

        <!-- Sign In Form -->
        <div class="form-container sign-in-container">

            <form action="actions/login_action.php" method="POST">
                <div class="flex-container">
                    <img src="img/logo.png" alt="Logo">
                    <h1>Sign In</h1>
                </div>
                <div>


                </div>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit">Sign In</button>
                <a class="forgot-password" href="fp/forgot_password.php">Forgot Password</a>
            </form>
        </div>

        <!-- Overlay -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Create Your Account</h1>
                    <p>Please fill out the form to register a new account</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Welcome Back!</h1>
                    <p>Please enter your credentials to access your account</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('container');
        document.getElementById('signUp').addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });
        document.getElementById('signIn').addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });

        document.querySelectorAll('input:not([type="email"]):not([type="password"])')
            .forEach(input => {
                input.addEventListener('blur', () => {
                    input.value = input.value
                        .toLowerCase()
                        .replace(/\b\w/g, char => char.toUpperCase());
                });
            });
    </script>
</body>

</html>