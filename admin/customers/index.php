<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php'; // Include the access control script

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Check access for customers module
$module = 'customers';
$access = restrictAccess($conn, $_SESSION['user_id'], $module);

// PHP and SQL query remain the same.
$stmt = $conn->prepare("
    SELECT 
        u.id, u.name, u.email, u.phone, u.address,
        -- Pet Details
        GROUP_CONCAT(DISTINCT p.id SEPARATOR '|||') AS pet_ids,
        GROUP_CONCAT(DISTINCT p.name SEPARATOR '|||') AS pet_names,
        GROUP_CONCAT(DISTINCT p.image SEPARATOR '|||') AS pet_images,
        GROUP_CONCAT(DISTINCT p.age SEPARATOR '|||') AS pet_ages,
        GROUP_CONCAT(DISTINCT p.medical_history SEPARATOR '|||') AS pet_medical_histories,
        -- Appointment Details
        GROUP_CONCAT(a.appointment_date ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_dates,
        GROUP_CONCAT(s.name ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_services,
        GROUP_CONCAT(ap.name ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_pet_names,
        GROUP_CONCAT(a.status ORDER BY a.appointment_date DESC SEPARATOR '|||') AS appointment_statuses
    FROM users u
    LEFT JOIN pets p ON u.id = p.user_id
    LEFT JOIN appointments a ON u.id = a.user_id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN pets ap ON a.pet_id = ap.id -- 'ap' for appointment pet
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
    table,
    th,
    td {
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
    <div  id="tableContainer">
        <div class="flex justify-between">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Customers</a>
                </li>
            </ol>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2 mt-4 z-50 ">
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
        <div class="overflow-x-auto ">
            <table id="usersTable" class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Total Pets</th>
                        <th>Email Verified</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $users->fetch_assoc()): ?>
                    
                    <tr class=" hover:bg-gray-100"
                        data-user-name="<?= htmlspecialchars($row['name']); ?>"
                        data-user-email="<?= htmlspecialchars($row['email']); ?>"
                        data-user-phone="<?= htmlspecialchars($row['phone']); ?>"
                        data-user-address="<?= htmlspecialchars($row['address']); ?>"
                        data-pet-names="<?= htmlspecialchars($row['pet_names']); ?>"
                        data-pet-ages="<?= htmlspecialchars($row['pet_ages']); ?>"
                        data-pet-images="<?= htmlspecialchars($row['pet_images']); ?>"
                        data-pet-medical-histories="<?= htmlspecialchars($row['pet_medical_histories']); ?>"
                        data-appointment-dates="<?= htmlspecialchars($row['appointment_dates']); ?>"
                        data-appointment-services="<?= htmlspecialchars($row['appointment_services']); ?>"
                        data-appointment-pet-names="<?= htmlspecialchars($row['appointment_pet_names']); ?>"
                        data-appointment-statuses="<?= htmlspecialchars($row['appointment_statuses']); ?>"
                    >
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['address']); ?></td>
                        <td>
                            <?= $row['pet_ids'] ? count(explode('|||', $row['pet_ids'])) : 0; ?>
                        </td>
                        <td>No</td>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6" />
                            </svg>
                            Prev
                        </a>
                    </li>
                    <div id="pageNumbersContainer" class="flex gap-1"></div>
                    <li>
                        <a href="#" id="nextPage" class="btn-ghost">
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
                    </div>
<div id="userDetailsContainer" class="hidden ">
   <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                    <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                      <a id="backButton" class="flex items-center gap-2 text-lg font-medium hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-undo2-icon lucide-undo-2"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5a5.5 5.5 0 0 1-5.5 5.5H11"/></svg> Customers</a>
                    </li>
                    <li>
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="m9 18 6-6-6-6" /></svg>
                    </li>
                    <li class="inline-flex items-center gap-1.5">
                        <a id="breadcrumb-customer-name" class="hover:text-foreground transition-colors">Details</a>
                    </li>
                  </ol>
    <div id="userDetailsContent"></div>
</div>

    </main>

    <script src="customers.js"></script>

    <script>
        const table = document.getElementById('usersTable');
        const tableContainer = document.getElementById('tableContainer');
        const detailsContainer = document.getElementById('userDetailsContainer');
        const detailsContent = document.getElementById('userDetailsContent');
        const backButton = document.getElementById('backButton');
        const separator = '|||';

        table.querySelectorAll('tbody tr').forEach(row => {
            if (row.dataset.userName) { 
                row.addEventListener('click', () => {
                    const {
                        userName, userEmail, userPhone, userAddress,
                        petNames: petNamesStr, petAges: petAgesStr, petImages: petImagesStr, petMedicalHistories: petMedicalHistoriesStr,
                        appointmentDates: appointmentDatesStr, appointmentServices: appointmentServicesStr,
                        appointmentPetNames: appointmentPetNamesStr, appointmentStatuses: appointmentStatusesStr
                    } = row.dataset;

                    // --- CHANGE 2: Update the breadcrumb text with the user's name ---
                    document.getElementById('breadcrumb-customer-name').innerText = userName;

                    // --- User Details HTML ---
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
                        </div>
                    `;

                    // --- Pets HTML ---
                    let petsHtml = '<h2 class="text-md font-semibold mt-6 mb-4 mt-4">Pets</h2>';
                    if (petNamesStr) {
                        const petNames = petNamesStr.split(separator);
                        const petAges = petAgesStr.split(separator);
                        const petImages = petImagesStr.split(separator);
                        const petMedicalHistories = petMedicalHistoriesStr.split(separator);

                        petsHtml += `
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th class="w-20">Image</th>
                                            <th>Name</th>
                                            <th>Age</th>
                                            <th>Medical History</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        petNames.forEach((name, index) => {
                            const image = petImages[index] ? `../../uploads/${petImages[index]}` : 'https://via.placeholder.com/100';
                            petsHtml += `
                                <tr>
                                    <td>
                                        <img src="${image}" alt="${name}" class="w-16 h-16 object-cover rounded-md">
                                    </td>
                                    <td>${name}</td>
                                    <td>${petAges[index] || 'N/A'}</td>
                                    <td>${petMedicalHistories[index] || 'None'}</td>
                                </tr>
                            `;
                        });
                        petsHtml += '</tbody></table></div>';
                    } else {
                        petsHtml += '<p>No pets found for this customer.</p>';
                    }

                    // --- Appointments HTML ---
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
                                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
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

                    // --- Combine all details into the final HTML ---
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
    </script>
</body>
</html>