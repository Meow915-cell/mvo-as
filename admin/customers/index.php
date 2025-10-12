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

// Fetch all users and their pets' images and details, including age and medical_history
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.phone, u.address, 
           GROUP_CONCAT(p.id) AS pet_ids, 
           GROUP_CONCAT(p.name) AS pet_names, 
           GROUP_CONCAT(p.image) AS pet_images,
           GROUP_CONCAT(p.age) AS pet_ages,
           GROUP_CONCAT(p.medical_history) AS pet_medical_histories 
    FROM users u 
    LEFT JOIN pets p ON u.id = p.user_id 
    WHERE u.role = 'user' 
    GROUP BY u.id, u.name, u.email, u.phone, u.address 
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

        <div class="flex justify-between">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Services</a>
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
                        <th>Verified</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['address']); ?></td>
                        <td>
                            <?= $row['pet_ids'] ? count(explode(',', $row['pet_ids'])) : 0; ?>
                        </td>
                        <td>No</td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <nav role="navigation" aria-label="pagination" class="mx-auto flex w-full justify-end mt-3">
                <ul class="flex flex-row items-center gap-1">
                    <li>
                        <a href="#" id="prevPage" class="btn-ghost">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6" />
                            </svg>
                            Previous
                        </a>
                    </li>
                    <div id="pageNumbersContainer" class="flex gap-1"></div>
                    <li>
                        <a href="#" id="nextPage" class="btn-ghost">
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    </li>
                </ul>
            </nav>

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
                    <button type="submit" class="btn-primary">Continue</button>
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
                        <input type="text" value="" id="name" name="name" autofocus />
                    </div>
                    <div class="grid gap-3">
                        <label for="description">Description</label>
                        <input type="text" value="" name="description" id="description" />
                    </div>
                    <div class="grid gap-3">
                        <label for="price">Price</label>
                        <input type="number" value="" name="price" id="price" />
                    </div>
                    <footer class="flex justify-end gap-2 mt-4">
                        <button class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                        <button type="submit" class="btn" onclick="this.closest('dialog').close()">Save changes</button>
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
    </script>
   <script>
document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.querySelector("#usersTable tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const rowsPerPageSelect = document.getElementById("rowsPerPage");
    const searchInput = document.getElementById("searchInput");
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const pageNumbersContainer = document.getElementById("pageNumbersContainer");

    let currentPage = 1;
    let rowsPerPage = parseInt(rowsPerPageSelect.value);

    function filterRows() {
        const term = searchInput.value.toLowerCase();
        return rows.filter(row => row.textContent.toLowerCase().includes(term));
    }

    function renderTable() {
        const filteredRows = filterRows();
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        // Hide all rows first
        rows.forEach(r => r.style.display = "none");

        // Show only current page rows
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filteredRows.slice(start, end).forEach(r => r.style.display = "");

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        pageNumbersContainer.innerHTML = "";

        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (currentPage <= 3) {
            startPage = 1;
            endPage = Math.min(maxPagesToShow, totalPages);
        } else if (currentPage > totalPages - 2) {
            startPage = Math.max(1, totalPages - maxPagesToShow + 1);
            endPage = totalPages;
        }

        // First page + dots
        if (startPage > 1) {
            pageNumbersContainer.appendChild(createPageButton(1));
            if (startPage > 2) pageNumbersContainer.appendChild(createDots());
        }

        // Middle pages
        for (let i = startPage; i <= endPage; i++) {
            pageNumbersContainer.appendChild(createPageButton(i));
        }

        // Last page + dots
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) pageNumbersContainer.appendChild(createDots());
            pageNumbersContainer.appendChild(createPageButton(totalPages));
        }

        // Enable/disable Prev/Next
        prevBtn.classList.toggle("opacity-50 cursor-not-allowed", currentPage === 1);
        nextBtn.classList.toggle("opacity-50 cursor-not-allowed", currentPage === totalPages);
    }

    function createPageButton(pageNum) {
        const li = document.createElement("li");
        const a = document.createElement("a");
        a.href = "#";
        a.textContent = pageNum;
        a.className = pageNum === currentPage ? "btn-icon-outline" : "btn-icon-ghost";
        a.addEventListener("click", (e) => {
            e.preventDefault();
            currentPage = pageNum;
            renderTable();
        });
        li.appendChild(a);
        return li;
    }

    function createDots() {
        const li = document.createElement("li");
        li.innerHTML = `<div class="size-9 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0"><circle cx="12" cy="12" r="1" /><circle cx="19" cy="12" r="1" /><circle cx="5" cy="12" r="1" /></svg></div>`;
        return li;
    }

    // Prev/Next buttons
    prevBtn.addEventListener("click", e => {
        e.preventDefault();
        if (currentPage > 1) currentPage--;
        renderTable();
    });

    nextBtn.addEventListener("click", e => {
        e.preventDefault();
        const filteredRows = filterRows();
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) currentPage++;
        renderTable();
    });

    // Rows per page and search
    rowsPerPageSelect.addEventListener("change", () => {
        rowsPerPage = parseInt(rowsPerPageSelect.value);
        currentPage = 1;
        renderTable();
    });

    searchInput.addEventListener("input", () => {
        currentPage = 1;
        renderTable();
    });

    renderTable();
});
</script>




</body>

</html>