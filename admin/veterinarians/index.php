<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php'; // Include the access control script

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Check access for veterinarians module
$module = 'veterinarians';
$access = restrictAccess($conn, $_SESSION['user_id'], $module);

// Verify database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all veterinarians
$stmt = $conn->prepare("SELECT id, name, email, phone, specialization, image FROM veterinarians ORDER BY name ASC");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$veterinarians = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Veterinarians</title>
    <link href="../../src/output.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js" defer></script>

</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar.php';
    ?>

    <main class="p-4 md:p-6">

        <div class="flex justify-between">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Veterinarians</a>
                </li>
            </ol>
            <button class="btn-sm bg-sky-500" onclick="document.getElementById('add-veterinarian').showModal()">Add
                Veterinarian</button>
        </div>



        <div class="overflow-x-auto mt-4">
            <table class="table">
                <caption>List of Veterinarians</caption>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($veterinarians->num_rows > 0): ?>
                        <?php while ($row = $veterinarians->fetch_assoc()): ?>
                            <tr>
                                <td><img src="<?php echo $row['image'] ? '../../Uploads/' . htmlspecialchars($row['image']) : 'https://placehold.co/50x50?text=Vet'; ?>"
                                        alt="Veterinarian" style="width: 50px; height: 50px; object-fit: cover;"></td>
                                <td class="font-medium"><?= htmlspecialchars($row['id']); ?></td>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($row['specialization'] ?? 'General'); ?></td>
                                <td class="text-right">
                                    <div class="flex gap-2 w-full justify-end">
                                        <button class="btn-sm-outline py-0 text-xs"
                                            onclick="openEditModal(<?= htmlspecialchars($row['id']); ?>)">
                                            Edit
                                        </button>
                                        <button type="button" class="btn-sm py-0 text-xs bg-rose-500" 
                                            onclick="openDeleteDialog(<?= htmlspecialchars($row['id']); ?>)">
                                            Delete
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No veterinarians found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Delete Dialog -->

    <dialog id="alert-dialog" class="dialog" aria-labelledby="alert-dialog-title"
        aria-describedby="alert-dialog-description">
        <article class="w-md">
            <header>
                <h2 id="alert-dialog-title">Are you absolutely sure?</h2>
                <p id="alert-dialog-description">
                    This action cannot be undone. This will permanently delete this veterinarian.
                </p>
            </header>

            <form id="deleteForm" action="../actions/manage_veterinarian.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="vet_id" id="deleteVetId"> <!-- Corrected name from service_id to vet_id -->

                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline"
                        onclick="document.getElementById('alert-dialog').close()">Cancel</button>
                    <button type="submit" class="btn-primary">Continue</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- Add Veterinarian Dialog -->

    <dialog id="add-veterinarian" class="dialog w-full sm:max-w-[425px] max-h-[612px]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Add Veterinarian</h2>
                <p>Enter the veterinarian details below. Click save when you're
                    done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/manage_veterinarian.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="grid gap-3">
                        <label for="add_name">Name</label>
                        <input type="text" value="" id="add_name" name="name" autofocus required />
                    </div>
                    <div class="grid gap-3">
                        <label for="add_email">Email</label>
                        <input type="email" value="" name="email" id="add_email" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="add_phone">Phone (Optional)</label>
                        <input type="text" value="" name="phone" id="add_phone" />
                    </div>
                    <div class="grid gap-3">
                        <label for="add_specialization">Specialization (Optional)</label>
                        <input type="text" value="" name="specialization" id="add_specialization" />
                    </div>
                    <div class="grid gap-3">
                        <label for="add_image">Photo (Optional)</label>
                        <input type="file" name="image" id="add_image" accept="image/jpeg,image/png" />
                    </div>

                    <footer class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                        <button type="submit" class="btn bg-sky-500">Save changes</button>
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

    <!-- Edit Veterinarian Dialog -->
    <dialog id="edit-veterinarian" class="dialog w-full sm:max-w-[425px] max-h-[612px]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Edit Veterinarian</h2>
                <p>Update the veterinarian details below. Click save when you're done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/manage_veterinarian.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="vet_id" id="edit_vet_id">

                    <div class="grid gap-3">
                        <label for="edit_name">Name</label>
                        <input type="text" name="name" id="edit_name" autofocus required />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_email">Email</label>
                        <input type="email" name="email" id="edit_email" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_phone">Phone (Optional)</label>
                        <input type="text" name="phone" id="edit_phone" />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_specialization">Specialization (Optional)</label>
                        <input type="text" name="specialization" id="edit_specialization" />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_image">Photo (Replace existing, Optional)</label>
                        <input type="file" name="image" id="edit_image" accept="image/jpeg,image/png" />
                        <small class="text-muted-foreground">Leave empty to keep the current photo.</small>
                    </div>

                    <footer class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn-outline"
                            onclick="this.closest('dialog').close()">Cancel</button>
                        <button type="submit" class="btn bg-sky-500">Save changes</button>
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


    <script>
        function openDeleteDialog(vetId) {
            document.getElementById('deleteVetId').value = vetId;
            document.getElementById('alert-dialog').showModal();
        }

        function openEditModal(vetId) {
            // Updated fetch URL to use get_veterinarian.php
            fetch(`../actions/get_veterinarian.php?id=${vetId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    // Fill the fields, using the properties from get_veterinarian.php
                    document.getElementById('edit_vet_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone || ''; // Handle null/empty phone
                    document.getElementById('edit_specialization').value = data.specialization || ''; // Handle null/empty specialization

                    // Show the modal
                    document.getElementById('edit-veterinarian').showModal();
                })
                .catch(error => {
                    console.error('Fetch error:', error.message);
                    alert('Failed to load veterinarian data. Please try again.');
                });
        }
    </script>

</body>

</html>