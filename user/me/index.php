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

$stmt = $conn->prepare("SELECT id, name, type, age, breed, favorite_activity, medical_history, image, body_temp, weight FROM pets WHERE user_id = ?");
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

        <div class="main-content">
            <section class="mb-8">
                <div class="rounded-lg shadow p-6 ">
                    <h2 class="text-xl font-bold mb-4">My Profile</h2>
                    <div class="flex w-max gap-40">
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

                    <div class="mt-8 flex justify-end gap-4">
                        <button class="btn-sm-outline" onclick="openModal('editProfileModal')" id="editProfileBtn">Edit
                            Profile</button>
                        <button class="btn-sm-outline" onclick="openModal('changePasswordModal')"
                            id="changePasswordBtn">Change
                            Password</button>
                        <button type="button" class="btn-sm bg-rose-500" onclick="openModal('logoutDialog')"
                            id="logoutBtn">Logout</button>
                    </div>
                </div>
            </section>

            <!-- Pets Section -->
            <section>
                <div class="rounded-lg">
                    <div class="flex justify-between">
                        <ol
                            class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                            <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                                <a class="text-lg font-medium hover:text-foreground transition-colors">My Pets</a>
                            </li>
                        </ol>
                    </div>

                    <?php if ($pets->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php while ($pet = $pets->fetch_assoc()): ?>
                                <div class="card max-w-xs">
                                    <header>
                                        <h2><?php echo htmlspecialchars($pet['name']); ?></h2>
                                        <p><?php echo htmlspecialchars(($pet['type'] . ' - ' . $pet['breed'] ?? 'N/A') . ' • ' . $pet['age'] . ' years old'); ?>
                                        </p>
                                    </header>
                                    <section class="px-0">
                                        <img alt="<?php echo htmlspecialchars($pet['name']); ?>" loading="lazy" width="500"
                                            height="500" class="aspect-video object-cover"
                                            src="<?php echo $pet['image'] ? '../../uploads/' . htmlspecialchars($pet['image']) : 'https://placehold.co/300x200?text=' . htmlspecialchars($pet['type']); ?>" />
                                    </section>
                                    <footer class="flex items-center gap-2">
                                        <span class="badge-outline"><?php echo htmlspecialchars($pet['body_temp'] ?? 'N/A'); ?>
                                            °C</span>
                                        <span class="badge-outline"><?php echo htmlspecialchars($pet['weight'] ?? 'N/A'); ?>
                                            kg</span>
                                    </footer>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <p class="text-lg text-muted-foreground">You haven't added any pets yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <dialog id="editProfileModal" class="dialog w-full sm:max-w-[425px] max-h-[612px]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Edit Profile</h2>
                <p>Update your account information below. Click save when you're done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/update_profile.php" method="POST">
                    <div class="grid gap-3">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                            required />
                    </div>
                    <div class="grid gap-3">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" />
                    </div>
                    <div class="grid gap-3">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address"
                            value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" />
                    </div>

                    <footer class="flex justify-end gap-2 mt-4">
                        <button class="btn-outline" onclick="this.closest('dialog').close()"
                            type="button">Cancel</button>
                        <button type="submit" class="btn bg-sky-500" onclick="this.closest('dialog').close()">Save
                            changes</button>
                    </footer>
                </form>
            </section>

            <button type="button" aria-label="Close dialog" onclick="this.closest('dialog').close()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-x-icon lucide-x">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </article>
    </dialog>


    <!-- Change Password Modal -->
    <dialog id="changePasswordModal" class="dialog w-full sm:max-w-[425px] max-h-[612px]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Change Password</h2>
                <p>Enter your current password and set a new one.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/change_password.php" method="POST">
                    <div class="grid gap-3">
                        <label for="old_password">Old Password</label>
                        <input type="password" id="old_password" name="old_password" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required />
                    </div>

                    <footer class="flex justify-end gap-2 mt-4">
                        <button class="btn-outline" onclick="this.closest('dialog').close()"
                            type="button">Cancel</button>
                        <button type="submit" class="btn bg-sky-500" onclick="this.closest('dialog').close()">Save
                            changes</button>
                    </footer>
                </form>
            </section>

            <button type="button" aria-label="Close dialog" onclick="this.closest('dialog').close()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-x-icon lucide-x">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </article>
    </dialog>

    <!-- Logout Confirmation Dialog -->
    <dialog id="logoutDialog" class="dialog" aria-labelledby="logout-dialog-title"
        aria-describedby="logout-dialog-description">
        <article class="w-md">
            <header>
                <h2 id="logout-dialog-title">Are you sure you want to log out?</h2>
                <p id="logout-dialog-description">
                    You’ll need to sign in again to access your account.
                </p>
            </header>

            <form action="../../logout.php" method="POST">
                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline"
                        onclick="document.getElementById('logoutDialog').close()">Cancel</button>
                    <button type="submit" class="btn-destructive">Logout</button>
                </footer>
            </form>
        </article>
    </dialog>

    <script>
        function openModal(id) {
            document.getElementById(id).showModal();
        }

        function closeModal(id) {
            document.getElementById(id).close();
        }
    </script>
</body>

</html>