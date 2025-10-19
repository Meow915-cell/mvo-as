<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login");
    exit();
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../../src/output.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
    <title>Users Management</title>
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar.php';
    ?>

    <main class="p-4 md:p-6">
        <div class="flex justify-between items-center">
            <ol class="mb-4 text-muted-foreground flex items-center gap-1.5 text-sm sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5">
                    <a class="text-lg font-medium">Settings</a>
                </li>
            </ol>
        </div>

        <section class="mb-8">
                <div class="rounded-lg shadow p-6 ">
                    <h2 class="text-xl font-bold mb-4">My Profile</h2>
                    <div class="flex w-max gap-40">
                        <div class="flex-1 gap-8 flex flex-col">
                            <div>
                                <p class="text-sm text-muted-foreground">Name</p>
                                <p class="font-medium"><?php echo htmlspecialchars($admin['name']); ?></p>
                            </div>

                            <div>
                                <p class="text-sm text-muted-foreground">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($admin['email']); ?></p>
                            </div>
                        </div>

                        <div class="flex-1 gap-8 flex flex-col">
                            <?php if (!empty($admin['phone'])): ?>
                            <div>
                                <p class="text-sm text-muted-foreground">Phone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($admin['phone']); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($admin['address'])): ?>
                            <div>
                                <p class="text-sm text-muted-foreground">Address</p>
                                <p class="font-medium"><?php echo htmlspecialchars($admin['address']); ?></p>
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
                <form class="form grid gap-4" action="../actions/update_admin_info.php" method="POST">
                    <div class="grid gap-3">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>"
                            required />
                    </div>
                    <div class="grid gap-3">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($admin['email']); ?>" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" />
                    </div>
                    <div class="grid gap-3">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address"
                            value="<?php echo htmlspecialchars($admin['address'] ?? ''); ?>" />
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

    <div id="toaster" class="toaster"></div>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const url = new URL(window.location);
        const params = url.searchParams;
        const toaster = document.getElementById("toaster");

        // Clear params BEFORE triggering toast display
        if (params.has("success") || params.has("error")) {
            const successMsg = params.get("success");
            const errorMsg = params.get("error");

            // Replace URL immediately 
            url.search = "";
            window.history.replaceState({}, document.title, url.toString());

            // Now show toast after clearing URL
            setTimeout(() => {
                if (successMsg) showToast("success", "Success", successMsg);
                if (errorMsg) showToast("error", "Error", errorMsg);
            }, 10);
        }

        function showToast(type, title, message) {
            const toast = document.createElement("div");
            toast.className = "toast";
            toast.setAttribute("data-category", type);
            toast.innerHTML = `
          <div class="toast-content">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                viewBox="0 0 24 24" fill="none" 
                stroke="${type === 'success' ? '#22c55e' : '#ef4444'}" 
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                ${type === "success"
                ? `<circle cx="12" cy="12" r="10" />
                    <path d="m9 12 2 2 4-4" />`
                : `<circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />`}
            </svg>
            <section>
                <h2>${title}</h2>
                <p>${message}</p>
            </section>
            </div>
        `;
            toaster.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    });
    </script>
</body>
</html>
