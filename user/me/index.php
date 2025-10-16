<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT name, email, phone, address, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT id, name, type, age, breed, favorite_activity, medical_history, image FROM pets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pets = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../../src/output.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js" defer></script>
    <style type="text/tailwindcss">
        @theme {
        --color-clifford: #da373d;
      }
    </style>
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar-user.php';
    ?>

    <main class="p-4 md:p-6">
        <div class="flex justify-between">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Me</a>
                </li>
            </ol>
        </div>
        <!-- <header>
        <button
          type="button"
          onclick="document.dispatchEvent(new CustomEvent('basecoat:sidebar'))"
        >
          Toggle sidebar
        </button>
      </header> -->


        <div class="main-content">
            <section class="mb-8">
                <div class="rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">My Profile</h2>

                    <div class="flex w-md gap-40">
                        <div class="flex-1 gap-8 flex flex-col">
                            <div>
                                <p class="text-sm text-muted-foreground">Name</p>
                                <p class="font-medium"><?php echo htmlspecialchars($user['name']); ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-muted-foreground">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>

                        <div class="flex-1 gap-8 flex flex-col">
                            <?php if (!empty($user['phone'])): ?>
                            <div>
                                <p class="text-sm text-muted-foreground">Phone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($user['phone']); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($user['address'])): ?>
                            <div>
                                <p class="text-sm text-muted-foreground">Address</p>
                                <p class="font-medium"><?php echo htmlspecialchars($user['address']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-4">
                        <button class="btn-sm-outline" onclick="openModal('editProfileModal')" id="editProfileBtn">Edit
                            Profile</button>
                        <button class="btn-sm-outline" onclick="openModal('changePasswordModal')" id="changePasswordBtn">Change
                            Password</button>
                        <form action="../logout.php" method="POST">
                            <button type="submit" class="btn-sm bg-rose-500" id="logoutBtn">Logout</button>
                        </form>
                    </div>


                </div>
            </section>

            <!-- Pets Section -->
            <section>
                <div class="rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">My Pets</h2>

                    <?php if ($pets->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($pet = $pets->fetch_assoc()): ?>
                        <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                            <img src="<?php echo $pet['image'] ? '../uploads/' . htmlspecialchars($pet['image']) : 'https://placehold.co/300x200?text=' . htmlspecialchars($pet['type']); ?>"
                                alt="<?php echo htmlspecialchars($pet['name']); ?>"
                                class="w-full h-48 object-cover rounded mb-4">

                            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <p class="text-sm text-muted-foreground">
                                <?php echo htmlspecialchars($pet['type'] . ' • ' . $pet['age'] . ' years old'); ?></p>

                            <div class="mt-3 space-y-1">
                                <p><span class="font-medium">Breed:</span>
                                    <?php echo htmlspecialchars($pet['breed'] ?? 'N/A'); ?></p>
                                <p><span class="font-medium">Favorite Activity:</span>
                                    <?php echo htmlspecialchars($pet['favorite_activity'] ?? 'N/A'); ?></p>
                                <p><span class="font-medium">Medical History:</span>
                                    <?php echo htmlspecialchars($pet['medical_history'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <p class="text-lg text-muted-foreground">You haven't added any pets yet.</p>

                    </div>
                    <?php endif; ?>


                    <div class="card w-xs">
  <header>
    <h2>Ming ming</h2>
    <p>Other • 16Year old</p>
  </header>
  <section class="px-0">
    <img
      alt="Photo by Drew Beamer"
      loading="lazy"
      width="500"
      height="500"
      class="aspect-video object-cover" style="color:transparent"
      srcset="
        https://images.unsplash.com/photo-1588345921523-c2dcdb7f1dcd?w=800&dpr=2&q=80&w=640&q=75 1x,
        https://images.unsplash.com/photo-1588345921523-c2dcdb7f1dcd?w=800&dpr=2&q=80&w=1080&q=75 2x
      "
      src="https://images.unsplash.com/photo-1588345921523-c2dcdb7f1dcd?w=800&dpr=2&q=80&w=1080&q=75"
    />
  </section>
  <footer class="flex items-center gap-2">
    <span class="badge-outline">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16" /><path d="M2 8h18a2 2 0 0 1 2 2v10" /><path d="M2 17h20" /><path d="M6 8v9" /></svg>
      1
    </span>
    <span class="badge-outline">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4 8 6" /><path d="M17 19v2" /><path d="M2 12h20" /><path d="M7 19v2" /><path d="M9 5 7.621 3.621A2.121 2.121 0 0 0 4 5v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5" /></svg>
      2
    </span>
    <span class="badge-outline">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 8 6-3-6-3v10" /><path d="m8 11.99-5.5 3.14a1 1 0 0 0 0 1.74l8.5 4.86a2 2 0 0 0 2 0l8.5-4.86a1 1 0 0 0 0-1.74L16 12" /><path d="m6.49 12.85 11.02 6.3" /><path d="M17.51 12.85 6.5 19.15" /></svg>
      350m²
    </span>
    <span class="ml-auto font-medium tabular-nums">
      $135,000
    </span>
  </footer>
</div>
                </div>
            </section>
        </div>
    </main>
</body>

</html>