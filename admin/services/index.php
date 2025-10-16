<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php';

// Redirect if user_id is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Check access for services module
$module = 'services';
$access = restrictAccess($conn, $_SESSION['user_id'], $module);

// Fetch all services
$stmt = $conn->prepare("SELECT id, name, description, price FROM services ORDER BY id DESC");
$stmt->execute();
$services = $stmt->get_result();
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
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Services</a>
                </li>
            </ol>
            <button class="btn-sm bg-sky-500" onclick="document.getElementById('add-service').showModal()">Add
                Service</button>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="table">
                <caption>List of Available Services</caption>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Description</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($services->num_rows > 0): ?>
                    <?php while ($row = $services->fetch_assoc()): ?>
                    <tr>
                        <td class="font-medium"><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td>
                            <div class="flex gap-2 w-full justify-end">
                                <button class="btn-sm-outline py-0 text-xs"
                                    onclick="openEditModal(<?= htmlspecialchars($row['id']); ?>)">
                                    Edit
                                </button>
                                <button type="button" class="btn-sm bg-rose-500 py-0 text-xs"
                                    onclick="openDeleteDialog(<?= htmlspecialchars($row['id']); ?>)">
                                    Delete
                                </button>

                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No services found</td>
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
                    This action cannot be undone. This will permanently delete this service.
                </p>
            </header>

            <form id="deleteForm" action="../actions/manage_service.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="service_id" id="deleteServiceId">

                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline"
                        onclick="document.getElementById('alert-dialog').close()">Cancel</button>
                    <button type="submit" class="btn-destructive">Continue</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- Add Service Dialog -->

    <dialog id="add-service" class="dialog w-full sm:max-w-[425px] max-h-[612px]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Add Service</h2>
                <p>Enter the service details below. Click save when you're
                    done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/manage_service.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="grid gap-3">
                        <label for="name">Service Name</label>
                        <input type="text" value="" id="name" name="name" autofocus placeholder="Enter Service Name"/>
                    </div>
                    <div class="grid gap-3">
                        <label for="description">Description</label>
                        <input type="text" value="" name="description" id="description" placeholder="Enter Description"/>
                    </div>
                    <footer class="flex justify-end gap-2 mt-4">
                        <button class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                        <button type="submit" class="btn bg-sky-500" onclick="this.closest('dialog').close()">Save changes</button>
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

    <!-- Edit Service Dialog -->
    <dialog id="edit-service" class="dialog w-full sm:max-w-[425px] max-h-[612px]"
        onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Edit Service</h2>
                <p>Update the service details below. Click save when you're done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/manage_service.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="service_id" id="edit_service_id">

                    <div class="grid gap-3">
                        <label for="edit_name">Service Name</label>
                        <input type="text" name="name" id="edit_name" autofocus />
                    </div>
                    <div class="grid gap-3">
                        <label for="edit_description">Description</label>
                        <input type="text" name="description" id="edit_description" />
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
    function openDeleteDialog(serviceId) {
        document.getElementById('deleteServiceId').value = serviceId;
        document.getElementById('alert-dialog').showModal();
    }

    function openEditModal(serviceId) {
        fetch(`../actions/get_service.php?id=${serviceId}`)
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
                document.getElementById('edit_service_id').value = data.id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_description').value = data.description || '';

                // Show the modal
                document.getElementById('edit-service').showModal();
            })
            .catch(error => {
                console.error('Fetch error:', error.message);
                alert('Failed to load service data. Please try again.');
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