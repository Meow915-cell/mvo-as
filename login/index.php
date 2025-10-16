<?php
session_start();
require_once '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard");
            } else {
                header("Location: ../user/me.php");
            }
            exit;
        } else {
            header("Location: ../login.php?error=Invalid password");
            exit;
        }
    } else {
        header("Location: ../login.php?error=User not found");
        exit;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>

  <link href="../src/output.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js" defer></script>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <style>

    
  </style>
</head>
<body class="bg-gray-50">
  <main class="p-4 md:p-6 h-screen flex justify-center items-center w-full">
    <div class="card w-md max-w-md">

      <header class=" flex justify-between items-center">
        <div>
                  <h2>Login to your account</h2>
        <p>Enter your details below to login to your account</p>
        </div>
        <div>
              <img class="size-18 object-cover rounded-full p-0" alt="logo" src="../logo.png" />
</div>
  
 
      </header>

      <section>
        <!-- Form now uses POST method -->
        <form class="form grid gap-6" method="POST" action="">
          <div class="grid gap-2">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="grid gap-2">
            <div class="flex items-center gap-2">
              <label for="password">Password</label>
              <a href="#" class="ml-auto inline-block text-sm underline-offset-4 hover:underline">Forgot your password?</a>
            </div>
            <input type="password" id="password" name="password" required>
          </div>

          <div class="cf-turnstile"
               data-sitekey="0x4AAAAAAB6rcIV7pi2pB8QC"
               data-theme="light"
               data-size="flexible"
               data-callback="onSuccess" style="width: 100% !important">
          </div>

          <button type="submit" class="btn w-full  bg-sky-500">Login</button>
        </form>
      </section>

      <footer class="flex flex-col items-center gap-2 mt-4">
        <p class="text-center text-sm">Don't have an account? 
          <a href="../signup" class="underline-offset-4 hover:underline">Sign up</a>
        </p>
      </footer>
    </div>
  </main>

  <script>
  // Disable the button initially
  const loginButton = document.querySelector('button[type="submit"]');
  loginButton.disabled = true;
  loginButton.classList.add('opacity-50', 'cursor-not-allowed');

  // Cloudflare Turnstile callback
  function onSuccess(token) {
    loginButton.disabled = false;
    loginButton.classList.remove('opacity-50', 'cursor-not-allowed');
  }
</script>

</body>
</html>
