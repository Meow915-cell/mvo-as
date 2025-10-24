<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php';

// Redirect if user_id is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check access for a suitable module, or change the logic if needed
$module = 'services';
$access = restrictAccess($conn, $_SESSION['user_id'], $module);

// Fetch all walk-in records from the walkin table
$stmt = $conn->prepare("SELECT 
    id, 
    owner_name, 
    pet_name, 
    type, 
    birthdate, 
    breed, 
    body_temp, 
    weight 
    FROM walkin 
    ORDER BY id DESC");
$stmt->execute();
$walkins = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Walk-in Records</title>
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
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Walk-in Records</a>
                </li>
            </ol>
            <button class="btn-sm bg-sky-500" onclick="document.getElementById('add-walkin').showModal()">Add
                Walk-in</button>
        </div>



        <div class="overflow-x-auto mt-4">
            <table class="table">
                <caption>List of Walk-in Pet Records</caption>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Owner Name</th>
                        <th>Pet Name</th>
                        <th>Type</th>
                        <th>birthdate</th>
                        <th>Breed</th>
                        <th>Temp (C)</th>
                        <th>Weight (kg)</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Looping through the walkin records -->
                    <?php if ($walkins->num_rows > 0): ?>
                        <?php while ($row = $walkins->fetch_assoc()): ?>
                            <tr>
                                <td class="font-medium"><?= htmlspecialchars($row['id']); ?></td>
                                <td><?= htmlspecialchars($row['owner_name']); ?></td>
                                <td><?= htmlspecialchars($row['pet_name']); ?></td>
                                <td><?= htmlspecialchars($row['type']); ?></td>
                                <td><?= htmlspecialchars($row['birthdate']); ?></td>
                                <td><?= htmlspecialchars($row['breed']); ?></td>
                                <td><?= htmlspecialchars(number_format($row['body_temp'], 2)); ?></td>
                                <td><?= htmlspecialchars(number_format($row['weight'], 2)); ?></td>
                                <td>
                                    <div class="flex gap-2 w-full justify-end">
                                        <button class="btn-sm-outline py-0 text-xs"
                                            onclick="openEditWalkinModal(<?= htmlspecialchars($row['id']); ?>)">
                                            Edit
                                        </button>
                                        <button type="button" class="btn-sm bg-rose-500 py-0 text-xs"
                                            onclick="openDeleteWalkinDialog(<?= htmlspecialchars($row['id']); ?>)">
                                            Delete
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No walk-in records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Delete Dialog  -->

    <dialog id="alert-dialog" class="dialog" aria-labelledby="alert-dialog-title"
        aria-describedby="alert-dialog-description">
        <article class="w-md">
            <header>
                <h2 id="alert-dialog-title">Are you absolutely sure?</h2>
                <p id="alert-dialog-description">
                    This action cannot be undone. This will permanently delete this walk-in record.
                </p>
            </header>

            <form id="deleteForm" action="../actions/manage_walkin.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="walkin_id" id="deleteWalkinId">

                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline"
                        onclick="document.getElementById('alert-dialog').close()">Cancel</button>
                    <button type="submit" class="btn-primary">Continue</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- Add Walk-in Dialog -->

    <dialog id="add-walkin" class="dialog w-full sm:max-w-[425px] " onclick="if (event.target === this) this.close()">
        <article class="w-md  max-h-[85vh] ">
            <header>
                <h2>Add Walk-in Record</h2>
                <p>Enter the pet and owner details below. Click save when you're
                    done.</p>
            </header>

            <section class="overflow-y-auto">
                <form class="form grid gap-4" action="../actions/manage_walkin.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="grid gap-3">
                        <label for="owner_name">Owner Name</label>
                        <input type="text" id="owner_name" name="owner_name" autofocus required />
                    </div>
                    <div class="grid gap-3">
                        <label for="pet_name">Pet Name</label>
                        <input type="text" name="pet_name" id="pet_name" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="type">Pet Type</label>
                        <input type="text" name="type" id="type" />
                    </div>
                    <div class="grid gap-3">
                        <label for="birthdate">Birthdate</label>
                        <input type="date" name="birthdate" id="birthdate" />
                    </div>
                    <div class="grid gap-3">
                        <label for="breed">Breed</label>
                        <input type="text" name="breed" id="breed" />
                    </div>
                    <div class="grid gap-3">
                        <label for="body_temp">Body Temperature (C)</label>
                        <input type="number" step="0.01" name="body_temp" id="body_temp" />
                    </div>
                    <div class="grid gap-3">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" id="weight" />
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

    <!-- Edit Walk-in Dialog -->
    <dialog id="edit-walkin" class="dialog w-full sm:max-w-[425px] max-h-[85vh]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md max-h-[85vh]">
            <header>
                <h2>Edit Walk-in Record</h2>
                <p>Update the pet and owner details below. Click save when you're done.</p>
            </header>

            <section class="overflow-y-auto">
                <form class="form grid gap-4" action="../actions/manage_walkin.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="walkin_id" id="edit_walkin_id">

                    <div class="grid gap-3">
                        <label for="edit_owner_name">Owner Name</label>
                        <input type="text" name="owner_name" id="edit_owner_name" autofocus required />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_pet_name">Pet Name</label>
                        <input type="text" name="pet_name" id="edit_pet_name" required />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_type">Pet Type</label>
                        <input type="text" name="type" id="edit_type" />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_birthdate">Birthdate</label>
                        <input type="date" name="birthdate" id="edit_birthdate" />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_breed">Breed</label>
                        <input type="text" name="breed" id="edit_breed" />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_body_temp">Body Temperature (C)</label>
                        <input type="number" step="0.01" name="body_temp" id="edit_body_temp" />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_weight">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" id="edit_weight" />
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
        function openDeleteWalkinDialog(walkinId) {
            document.getElementById('deleteWalkinId').value = walkinId;
            document.getElementById('alert-dialog').showModal();
        }

        function openEditWalkinModal(walkinId) {
            fetch(`../actions/get_walkin.php?id=${walkinId}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    // Fill the fields
                    document.getElementById('edit_walkin_id').value = data.id;
                    document.getElementById('edit_owner_name').value = data.owner_name;
                    document.getElementById('edit_pet_name').value = data.pet_name;
                    document.getElementById('edit_type').value = data.type || '';
                    document.getElementById('edit_birthdate').value = data.birthdate;
                    document.getElementById('edit_breed').value = data.breed || '';
                    document.getElementById('edit_body_temp').value = data.body_temp;
                    document.getElementById('edit_weight').value = data.weight;


                    // Show the modal
                    document.getElementById('edit-walkin').showModal();
                })
                .catch(error => {
                    console.error('Fetch error:', error.message);
                    alert('Failed to load walk-in data. Please try again.');
                });
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
          <div class="toast-content border-sky-400">
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