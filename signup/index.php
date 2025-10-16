<?php
session_start();
require_once '../db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = 'user'; // users sign up as regular users only

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: signup.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $role);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['role'] = $role;
        header("Location: ../user/me.php");
        exit();
    } else {
        header("Location: signup.php?error=Signup failed");
        exit();
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
  <title>Sign Up</title>

  <link href="../src/output.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js" defer></script>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <style>
    .turnstile-wrapper {
      width: 100%;
      display: flex;
      justify-content: center;
    }
    .cf-turnstile iframe {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 280px;
    }
  </style>
</head>

<body class="bg-gray-50">
  <main class="p-4 md:p-6 flex justify-center items-center w-full my-5">
    <div class=" w-md max-w-lg">
      <header class="mb-8 flex justify-between items-center">
        <div>
             <p class="text-xl font-bold mb-2">Create your account</p>
            <p>Fill in your details to get started</p>
        </div>
        <div>
              <img class="size-18 object-cover rounded-full p-0" alt="logo" src="../logo.png" />
</div>
  
 
      </header>

      <section>
        <form class="form grid gap-6" method="POST" action="">
          <?php if (isset($_GET['error'])): ?>
            <p class="text-red-500 text-sm"><?= htmlspecialchars($_GET['error']) ?></p>
          <?php endif; ?>

          <div class="grid gap-2">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" required>
            <p class="text-muted-foreground text-sm">This is your display name shown to admin.</p>
          </div>

          <div class="grid gap-2">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required>
                        <p class="text-muted-foreground text-sm">Used for account recovery and verification.</p>
          </div>

          <div class="grid gap-2">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <p class="text-muted-foreground text-sm">Use at least 8 characters.</p>
          </div>

          <div class="grid gap-2">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" placeholder="+63 912 345 6789" required>
          </div>

          <div class="grid gap-2">
            <label for="address">Address</label>
            <textarea id="address" name="address" placeholder="123 Example St, Manila, Philippines" rows="2" required></textarea>
          </div>


          <div class="turnstile-wrapper">
            <div class="cf-turnstile"
                 data-sitekey="0x4AAAAAAB6rcIV7pi2pB8QC"
                 data-theme="light"
                 data-size="flexible"
                 data-callback="onSuccess" style="width: 100% !important">
            </div>
          </div>

          <button type="submit" class="btn w-full bg-sky-500">Sign Up</button>
        </form>
      </section>

      <footer class="flex flex-col items-center gap-2 mt-4">
        <p class="text-center text-sm">Already have an account? 
          <a href="../login" class="underline-offset-4 hover:underline">Login</a>
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
