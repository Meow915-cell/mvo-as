<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user info
$stmt = $conn->prepare("SELECT name, email, phone, address, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get pets
$stmt = $conn->prepare("SELECT id, name, type, age, breed, favorite_activity, medical_history, image FROM pets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pets = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets - Manage</title>
    <link href="../../src/output.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
</head>
<body>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
include '../../components/sidebar-user.php';
?>

<main class="p-4 md:p-6">
    <div class="flex justify-between">
        <h2 class="text-xl font-semibold">My Pets</h2>
        <button class="btn-sm bg-sky-500" onclick="document.getElementById('add-pet').showModal()">Add Pet</button>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="table">
            <caption>List of Pets</caption>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Age</th>
                    <th>Breed</th>
                    <th>Favorite Activity</th>
                    <th>Medical History</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pets->num_rows > 0): ?>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?= $pet['image'] ? '../../Uploads/' . htmlspecialchars($pet['image']) : 'https://placehold.co/50x50?text=Pet'; ?>"
                                     alt="Pet" style="width:50px;height:50px;object-fit:cover;">
                            </td>
                            <td><?= htmlspecialchars($pet['name']); ?></td>
                            <td><?= htmlspecialchars($pet['type']); ?></td>
                            <td><?= $pet['age'] ? htmlspecialchars($pet['age']) . ' yrs' : 'N/A'; ?></td>
                            <td><?= htmlspecialchars($pet['breed'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($pet['favorite_activity'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($pet['medical_history'] ?? 'N/A'); ?></td>
                            <td class="text-right">
                                <div class="flex gap-2 justify-end">
                                    <button class="btn-sm-outline py-0 text-xs" onclick="openEditModal(<?= $pet['id']; ?>)">Edit</button>
                                    <button class="btn-sm py-0 text-xs bg-rose-500" onclick="openDeleteDialog(<?= $pet['id']; ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No pets found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add Pet Dialog -->
<dialog id="add-pet" class="dialog w-full sm:max-w-[425px]" onclick="if(event.target===this)this.close()">
    <article class="w-md  max-h-[85vh]">
        <header>
            <h2>Add Pet</h2>
            <p>Enter pet details below.</p>
        </header>
        <section class="overflow-y-auto">
        <form class="form grid gap-4 " action="../actions/manage_pet.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="grid gap-3">
                <label for="add_name">Name</label>
                <input type="text" id="add_name" name="name" required>
            </div>
            <div class="grid gap-3">
                <label for="add_type">Type</label>
                <select id="add_type" name="type" required class="w-full">
                    <option value="Dog">Dog</option>
                    <option value="Cat">Cat</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="grid gap-3">
                <label for="add_age">Age</label>
                <input type="number" id="add_age" name="age" min="0">
            </div>
            <div class="grid gap-3">
                <label for="add_breed">Breed</label>
                <input type="text" id="add_breed" name="breed">
            </div>
            <div class="grid gap-3">
                <label for="add_favorite_activity">Favorite Activity</label>
                <input type="text" id="add_favorite_activity" name="favorite_activity">
            </div>
            <div class="grid gap-3">
                <label for="add_medical_history">Medical History</label>
                <textarea id="add_medical_history" name="medical_history"></textarea>
            </div>
            <div class="grid gap-3">
                <label for="add_image">Photo</label>
                <input type="file" id="add_image" name="image" accept="image/jpeg,image/png">
            </div>
            <footer class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit" class="btn bg-sky-500">Save</button>
            </footer>
        </form>
                </section>
    </article>
</dialog>

<!-- Edit Pet Dialog -->
<dialog id="edit-pet" class="dialog w-full sm:max-w-[425px]" onclick="if(event.target===this)this.close()">
    <article class="w-md max-h-[85vh]">
        <header>
            <h2>Edit Pet</h2>
            <p>Update pet details below.</p>
        </header>
         <section class="overflow-y-auto">
        <form class="form grid gap-4" id="editPetForm" action="../actions/manage_pet.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="pet_id" id="edit_pet_id">
            <div class="grid gap-3">
                <label for="edit_name">Name</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div class="grid gap-3">
                <label for="edit_type">Type</label>
                <select id="edit_type" name="type" required class="w-full">
                    <option value="Dog">Dog</option>
                    <option value="Cat">Cat</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="grid gap-3">
                <label for="edit_age">Age</label>
                <input type="number" id="edit_age" name="age" min="0">
            </div>
            <div class="grid gap-3">
                <label for="edit_breed">Breed</label>
                <input type="text" id="edit_breed" name="breed">
            </div>
            <div class="grid gap-3">
                <label for="edit_favorite_activity">Favorite Activity</label>
                <input type="text" id="edit_favorite_activity" name="favorite_activity">
            </div>
            <div class="grid gap-3">
                <label for="edit_medical_history">Medical History</label>
                <textarea id="edit_medical_history" name="medical_history"></textarea>
            </div>
            <div class="grid gap-3">
                <label for="edit_image">Photo</label>
                <input type="file" id="edit_image" name="image" accept="image/jpeg,image/png">
                <small class="text-muted-foreground">Leave empty to keep current photo</small>
            </div>
            <footer class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                <button type="submit" class="btn bg-sky-500">Update</button>
            </footer>
        </form>
                </section>
    </article>
</dialog>

<!-- Delete Confirmation -->
<dialog id="delete-dialog" class="dialog" aria-labelledby="delete-title" aria-describedby="delete-desc">
    <article class="w-md">
        <header>
            <h2 id="delete-title">Are you sure?</h2>
            <p id="delete-desc">This action cannot be undone. This will permanently delete the pet.</p>
        </header>
        <form id="deleteForm" action="../actions/manage_pet.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="pet_id" id="delete_pet_id">
            <footer class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn-outline" onclick="document.getElementById('delete-dialog').close()">Cancel</button>
                <button type="submit" class="btn bg-rose-500">Delete</button>
            </footer>
        </form>
    </article>
</dialog>

<script>
function openDeleteDialog(petId) {
    document.getElementById('delete_pet_id').value = petId;
    document.getElementById('delete-dialog').showModal();
}

function openEditModal(petId) {
    fetch(`../actions/get_pet.php?id=${petId}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_pet_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_type').value = data.type;
            document.getElementById('edit_age').value = data.age || '';
            document.getElementById('edit_breed').value = data.breed || '';
            document.getElementById('edit_favorite_activity').value = data.favorite_activity || '';
            document.getElementById('edit_medical_history').value = data.medical_history || '';
            document.getElementById('edit_image').value = '';
            document.getElementById('edit-pet').showModal();
        })
        .catch(err => alert('Failed to load pet data'));
}
</script>
</body>
</html>
