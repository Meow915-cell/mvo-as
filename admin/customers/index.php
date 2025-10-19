<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Check access for customers module
$module = 'customers';
$access = restrictAccess($conn, $_SESSION['user_id'], $module);

$stmt = $conn->prepare("
    SELECT 
        u.id, u.name, u.email, u.phone, u.address,
        -- Pet Details
        GROUP_CONCAT(DISTINCT p.id SEPARATOR '|||') AS pet_ids,
        GROUP_CONCAT(DISTINCT p.name SEPARATOR '|||') AS pet_names,
        GROUP_CONCAT(DISTINCT p.type SEPARATOR '|||') AS pet_types,
        GROUP_CONCAT(DISTINCT p.age SEPARATOR '|||') AS pet_ages,
        GROUP_CONCAT(DISTINCT p.breed SEPARATOR '|||') AS pet_breeds,
        GROUP_CONCAT(DISTINCT p.favorite_activity SEPARATOR '|||') AS pet_favorite_activities,
        GROUP_CONCAT(DISTINCT p.medical_history SEPARATOR '|||') AS pet_medical_histories,
        GROUP_CONCAT(DISTINCT p.created_at SEPARATOR '|||') AS pet_created_ats,
        GROUP_CONCAT(DISTINCT p.image SEPARATOR '|||') AS pet_images,
        GROUP_CONCAT(DISTINCT p.body_temp SEPARATOR '|||') AS pet_body_temps,
        GROUP_CONCAT(DISTINCT p.weight SEPARATOR '|||') AS pet_weights,
        -- Appointment Details
        GROUP_CONCAT(a.appointment_date ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_dates,
        GROUP_CONCAT(s.name ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_services,
        GROUP_CONCAT(ap.name ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_pet_names,
        GROUP_CONCAT(a.status ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_statuses
    FROM users u
    LEFT JOIN pets p ON u.id = p.user_id
    LEFT JOIN appointments a ON u.id = a.user_id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN pets ap ON a.pet_id = ap.id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.name ASC
");
$stmt->execute();
$users = $stmt->get_result();

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
    <style>
        #usersTable table, #usersTable th, #usersTable td {
            user-select: none;
        }
    </style>
</head>
<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar.php';
    ?>
    <main class="p-4 md:p-6">
        <div id="tableContainer">
            <div class="flex justify-between">
                <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                    <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                        <a class="text-lg font-medium hover:text-foreground transition-colors">Customers</a>
                    </li>
                </ol>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2 mt-4 z-50">
                <div>
                    <input id="searchInput" class="input" type="text" placeholder="Search...">
                </div>
                <div class="flex gap-2 items-center mr-2">
                    <label for="rowsPerPage" class="mr-2 font-light">Rows:</label>
                    <select id="rowsPerPage" class="select w-[180px]">
                        <optgroup label="Rows">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="usersTable" class="table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Total Pets</th>
                            <!-- <th>Email Verified</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = $users->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100" 
                            data-user-name="<?= htmlspecialchars($row['name']); ?>"
                            data-user-id="<?= htmlspecialchars($row['id']); ?>"
                            data-user-email="<?= htmlspecialchars($row['email']); ?>"
                            data-user-phone="<?= htmlspecialchars($row['phone']); ?>"
                            data-user-address="<?= htmlspecialchars($row['address']); ?>"
                            data-pet-ids="<?= htmlspecialchars($row['pet_ids']); ?>"
                            data-pet-names="<?= htmlspecialchars($row['pet_names']); ?>"
                            data-pet-types="<?= htmlspecialchars($row['pet_types']); ?>"
                            data-pet-ages="<?= htmlspecialchars($row['pet_ages']); ?>"
                            data-pet-breeds="<?= htmlspecialchars($row['pet_breeds']); ?>"
                            data-pet-favorite-activities="<?= htmlspecialchars($row['pet_favorite_activities']); ?>"
                            data-pet-medical-histories="<?= htmlspecialchars($row['pet_medical_histories']); ?>"
                            data-pet-created-ats="<?= htmlspecialchars($row['pet_created_ats']); ?>"
                            data-pet-images="<?= htmlspecialchars($row['pet_images']); ?>"
                            data-pet-body-temps="<?= htmlspecialchars($row['pet_body_temps']); ?>"
                            data-pet-weights="<?= htmlspecialchars($row['pet_weights']); ?>"
                            data-appointment-dates="<?= htmlspecialchars($row['appointment_dates']); ?>"
                            data-appointment-services="<?= htmlspecialchars($row['appointment_services']); ?>"
                            data-appointment-pet-names="<?= htmlspecialchars($row['appointment_pet_names']); ?>"
                            data-appointment-statuses="<?= htmlspecialchars($row['appointment_statuses']); ?>">
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['phone']); ?></td>
                            <td><?= htmlspecialchars($row['address']); ?></td>
                            <td><?= $row['pet_ids'] ? count(explode('|||', $row['pet_ids'])) : 0; ?></td>
                            <!-- <td>No</td> -->
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <nav role="navigation" aria-label="pagination" class="mx-auto flex w-full justify-end mt-3">
                    <ul class="flex flex-row items-center gap-1">
                        <li>
                            <a href="#" id="prevPage" class="btn-ghost">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                                Prev
                            </a>
                        </li>
                        <div id="pageNumbersContainer" class="flex gap-1"></div>
                        <li>
                            <a href="#" id="nextPage" class="btn-ghost">
                                Next
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div id="userDetailsContainer" class="hidden">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a id="backButton" class="flex items-center gap-2 text-lg font-medium hover:text-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-undo2-icon lucide-undo-2">
                            <path d="M9 14 4 9l5-5" />
                            <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5a5.5 5.5 0 0 1-5.5 5.5H11" />
                        </svg> Customers
                    </a>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </li>
                <li class="inline-flex items-center gap-1.5">
                    <a id="breadcrumb-customer-name" class="hover:text-foreground transition-colors">Details</a>
                </li>
            </ol>
            <div id="userDetailsContent"></div>
        </div>
        <dialog id="alert-dialog" class="dialog" aria-labelledby="alert-dialog-title" aria-describedby="alert-dialog-description">
            <article class="w-md">
                <header>
                    <h2 id="alert-dialog-title">Are you absolutely sure?</h2>
                    <p id="alert-dialog-description">
                        This action cannot be undone. This will permanently delete this service.
                    </p>
                </header>
                <form id="deleteForm" action="../actions/manage_customer.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="deleteCustomerId">
                    <footer class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn-outline" onclick="document.getElementById('alert-dialog').close()">Cancel</button>
                        <button type="submit" class="btn-primary">Continue</button>
                    </footer>
                </form>
            </article>
        </dialog>
    </main>
    <script src="customers.js"></script>
    <script>
        const table = document.getElementById('usersTable');
        const tableContainer = document.getElementById('tableContainer');
        const detailsContainer = document.getElementById('userDetailsContainer');
        const detailsContent = document.getElementById('userDetailsContent');
        const backButton = document.getElementById('backButton');
        const separator = '|||';

        //when the row is selected this will show 

        table.querySelectorAll('tbody tr').forEach(row => {
            if (row.dataset.userName) {
                row.addEventListener('click', () => {
                    const {
                        userId,
                        userName,
                        userEmail,
                        userPhone,
                        userAddress,
                        petIds: petIdsStr,
                        petNames: petNamesStr,
                        petTypes: petTypesStr,
                        petAges: petAgesStr,
                        petBreeds: petBreedsStr,
                        petFavoriteActivities: petFavoriteActivitiesStr,
                        petMedicalHistories: petMedicalHistoriesStr,
                        petCreatedAts: petCreatedAtsStr,
                        petImages: petImagesStr,
                        petBodyTemps: petBodyTempsStr,
                        petWeights: petWeightsStr,
                        appointmentDates: appointmentDatesStr,
                        appointmentServices: appointmentServicesStr,
                        appointmentPetNames: appointmentPetNamesStr,
                        appointmentStatuses: appointmentStatusesStr
                    } = row.dataset;

                    document.getElementById('breadcrumb-customer-name').innerText = userName;

                    const userDetailsHtml = `
                        <div>
                            <h2 class="text-md font-semibold mb-4 mt-4">Customer Details</h2>
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <tbody>
                                        <tr>
                                            <td class="font-semibold text-gray-600 w-1/4">Name</td>
                                            <td>${userName}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-semibold text-gray-600 w-1/4">Email</td>
                                            <td>${userEmail}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-semibold text-gray-600">Phone</td>
                                            <td>${userPhone}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-semibold text-gray-600">Address</td>
                                            <td>${userAddress}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-end gap-2 mt-2">
                                <button type="button" class="btn-sm-outline py-0 text-xs" onclick="openDeleteDialog('${userId}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-clock-icon lucide-calendar-clock"><path d="M16 14v2.2l1.6 1"/><path d="M16 2v4"/><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M3 10h5"/><path d="M8 2v4"/><circle cx="16" cy="16" r="6"/></svg>
                                    Schedule Follow-Up
                                </button>
                                <button type="button" class="btn-sm-destructive py-0 text-xs" onclick="openDeleteDialog('${userId}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-icon lucide-trash">
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    `;

                    let petsHtml = '<h2 class="text-md font-semibold mt-6 mb-4">Pets</h2>';
                    if (petNamesStr) {
                        const petIds = petIdsStr.split(separator);
                        const petNames = petNamesStr.split(separator);
                        const petTypes = petTypesStr.split(separator);
                        const petAges = petAgesStr.split(separator);
                        const petBreeds = petBreedsStr.split(separator);
                        const petFavoriteActivities = petFavoriteActivitiesStr.split(separator);
                        const petMedicalHistories = petMedicalHistoriesStr.split(separator);
                        const petCreatedAts = petCreatedAtsStr.split(separator);
                        const petImages = petImagesStr.split(separator);
                        const petBodyTemps = petBodyTempsStr.split(separator);
                        const petWeights = petWeightsStr.split(separator);

                        petsHtml += `
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th class="w-20">Image</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Age</th>
                                            <th>Breed</th>
                                            <th>Favorite Activity</th>
                                            <th>Medical History</th>
                                            <th>Created At</th>
                                            <th>Body Temp</th>
                                            <th>Weight</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        petNames.forEach((name, index) => {
                            const image = petImages[index] ? `../../Uploads/${petImages[index]}` : 'https://via.placeholder.com/100';
                            const createdAt = new Date(petCreatedAts[index]).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            petsHtml += `
                                <tr>
                                    <td><img src="${image}" alt="${name}" class="w-16 h-16 object-cover rounded-md"></td>
                                    <td>${petIds[index] || 'N/A'}</td>
                                    <td>${name}</td>
                                    <td>${petTypes[index] || 'N/A'}</td>
                                    <td>${petAges[index] || 'N/A'}</td>
                                    <td>${petBreeds[index] || 'N/A'}</td>
                                    <td>${petFavoriteActivities[index] || 'N/A'}</td>
                                    <td>${petMedicalHistories[index] || 'None'}</td>
                                    <td>${createdAt}</td>
                                    <td>${petBodyTemps[index] || 'N/A'}</td>
                                    <td>${petWeights[index] || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        petsHtml += '</tbody></table></div>';
                    } else {
                        petsHtml += '<p>No pets found for this customer.</p>';
                    }

                    let appointmentsHtml = '<br/><h2 class="text-md font-semibold mt-8 mb-4">Appointment History</h2>';
                    if (appointmentDatesStr) {
                        const appointmentDates = appointmentDatesStr.split(separator);
                        const appointmentServices = appointmentServicesStr.split(separator);
                        const appointmentPetNames = appointmentPetNamesStr.split(separator);
                        const appointmentStatuses = appointmentStatusesStr.split(separator);

                        appointmentsHtml += `
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Pet</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        appointmentDates.forEach((date, index) => {
                            const formattedDate = new Date(date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            appointmentsHtml += `
                                <tr>
                                    <td>${formattedDate}</td>
                                    <td>${appointmentPetNames[index]}</td>
                                    <td>${appointmentServices[index]}</td>
                                    <td><span class="badge badge-outline-${appointmentStatuses[index].toLowerCase() === 'completed' ? 'success' : 'warning'}">${appointmentStatuses[index]}</span></td>
                                </tr>
                            `;
                        });
                        appointmentsHtml += '</tbody></table></div>';
                    } else {
                        appointmentsHtml += '<p>No appointment history found for this customer.</p>';
                    }

                    detailsContent.innerHTML = `
                        ${userDetailsHtml}
                        ${petsHtml}
                        ${appointmentsHtml}
                    `;

                    tableContainer.style.display = 'none';
                    detailsContainer.classList.remove('hidden');
                });
            }
        });

        backButton.addEventListener('click', (e) => {
            e.preventDefault();
            detailsContainer.classList.add('hidden');
            tableContainer.style.display = 'block';
        });

        function openDeleteDialog(customerId) {
            document.getElementById('deleteCustomerId').value = customerId;
            document.getElementById('alert-dialog').showModal();
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