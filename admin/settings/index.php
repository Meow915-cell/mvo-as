<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Fetch current admin info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name, email, phone, address, role FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all admin users (only if super admin)
$users = [];
if ($user_id === 1) {
    $stmt = $conn->prepare("SELECT id, name, email, phone, address, role, permissions FROM users WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
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
                    <a class="text-lg font-medium">Users</a>
                </li>
            </ol>
            <button class="btn-sm bg-sky-500" onclick="document.getElementById('add-user').showModal()">Add User</button>
        </div>

        <section class="mt-6">
            <div class="overflow-x-auto">
                <table class="table">
                    <caption>List of Users</caption>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']); ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? '—'); ?></td>
                                    <td><?= htmlspecialchars($row['address'] ?? '—'); ?></td>
                                    <td class="text-right flex gap-2 justify-end">
                                        <button class="btn-sm-outline text-xs"
                                            onclick="openPermissionDialog(<?= $row['id'] ?>)">Change Permission</button>
                                        <button class="btn-sm-outline text-xs text-red-500"
                                            onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted-foreground py-3">
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Delete Dialog -->
    <dialog id="alert-dialog" class="dialog">
        <article>
            <header>
                <h2>Are you sure?</h2>
                <p>This action cannot be undone. This will permanently delete this user.</p>
            </header>
            <form id="deleteForm" action="delete_user.php" method="GET">
                <input type="hidden" name="user_id" id="deleteUserId">
                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="document.getElementById('alert-dialog').close()">Cancel</button>
                    <button type="submit" class="btn-primary">Delete</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- Add User Dialog -->
    <dialog id="add-user" class="dialog w-full sm:max-w-[425px]">
        <article>
            <header>
                <h2>Add User</h2>
                <p>Fill out the form below to add a new user.</p>
            </header>

            <form class="form grid gap-4" action="add_user.php" method="POST">
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required />
                </div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required />
                </div>

                <div>
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" />
                </div>

                <div>
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" />
                </div>
                             <input type="hidden" name="role" value="admin"/>


                <div>
                    <label for="confirm_password">Default Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required />
                </div>

                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="btn">Save</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- Change Permission Dialog -->
    <dialog id="permission-dialog" class="dialog w-full sm:max-w-[425px]">
        <article>
            <header>
                <h2>Change Permissions</h2>
                <p>Select the permissions for this user.</p>
            </header>
            <form class="form grid gap-4" action="update_user_permission.php" method="POST">
                <input type="hidden" name="user_id" id="permissionUserId">

                <label><input type="checkbox" name="permissions[]" value="manage_users"> Manage Users</label>
                <label><input type="checkbox" name="permissions[]" value="manage_services"> Manage Services</label>
                <label><input type="checkbox" name="permissions[]" value="view_reports"> View Reports</label>

                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="btn">Save</button>
                </footer>
            </form>
        </article>
    </dialog>

    <script>
    function confirmDelete(id) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('alert-dialog').showModal();
    }

    function openPermissionDialog(id) {
        document.getElementById('permissionUserId').value = id;
        document.getElementById('permission-dialog').showModal();
    }
    </script>
</body>
</html>
