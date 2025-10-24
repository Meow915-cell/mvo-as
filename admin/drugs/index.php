<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all drugs with latest IN and OUT info
$query = "
SELECT 
    d.drug_id,
    d.drug_name,
    d.inventory,
    (SELECT MAX(date_in) FROM drug_in WHERE drug_id = d.drug_id) AS last_in,
    (SELECT MAX(date_out) FROM drug_out WHERE drug_id = d.drug_id) AS last_out
FROM drugs d
ORDER BY d.drug_id DESC
";
$drugs = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../../src/output.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
    <title>Drug Inventory</title>
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar.php';
    ?>

    <main class="p-4 md:p-6">
        <div class="flex justify-between">
            <h2 class="text-xl font-bold">Drugs Inventory</h2>
            <div class="flex gap-2">
                <button class="btn-sm bg-green-500" onclick="document.getElementById('add-drug').showModal()">Add Drug</button>
                <button class="btn-sm bg-sky-500" onclick="document.getElementById('in-drug').showModal()">Stock In</button>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="table">
                <caption>List of Drugs</caption>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Inventory</th>
                        <th>Last Date In</th>
                        <th>Last Date Out</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($drugs->num_rows > 0): ?>
                        <?php while ($row = $drugs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['drug_id']); ?></td>
                                <td><?= htmlspecialchars($row['drug_name']); ?></td>
                                <td><?= htmlspecialchars($row['inventory']); ?></td>
                                <td><?= htmlspecialchars($row['last_in'] ?? '—'); ?></td>
                                <td><?= htmlspecialchars($row['last_out'] ?? '—'); ?></td>
                                <td class="text-right flex gap-2 justify-end">
                                    <button type="button" class="btn-sm-outline py-0 text-xs bg-blue-100"
                                        onclick="openViewDialog(<?= $row['drug_id']; ?>)">View</button>
                                    <button type="button" class="btn-sm-destructive py-0 text-xs"
                                        onclick="openDeleteDialog(<?= $row['drug_id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No drugs found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Drug Dialog -->
    <dialog id="add-drug" class="dialog w-full sm:max-w-[425px]">
        <article>
            <header><h2>Add New Drug</h2></header>
            <form class="form grid gap-4" action="../actions/manage_drug.php" method="POST">
                <input type="hidden" name="action" value="add_drug">
                <label>Drug Name <input type="text" name="drug_name" required></label>
                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="btn bg-green-500">Save</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- View Dialog -->
    <dialog id="view-dialog" class="dialog w-full sm:max-w-[700px]">
        <article>
            <header><h2 class="font-bold">Drug Details</h2></header>

            <div id="drug-info" class="mb-4 text-sm grid grid-cols-2 gap-2"></div>

            <table class="table w-full border mt-2">
                <thead>
                    <tr>
                        <th>Date In</th>
                        <th>Expiry</th>
                        <th>Count</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="in-table-body">
                    <tr><td colspan="4" class="text-center text-gray-500">Loading...</td></tr>
                </tbody>
            </table>

            <footer class="flex justify-end mt-4">
                <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Close</button>
            </footer>
        </article>
    </dialog>

    <!-- Stock In Dialog -->
    <dialog id="in-drug" class="dialog w-full sm:max-w-[425px]">
        <article>
            <header><h2>Stock In</h2></header>
            <form class="form grid gap-4" action="../actions/manage_drug.php" method="POST">
                <input type="hidden" name="action" value="stock_in">
                <label>Select Drug
                    <select name="drug_id" required>
                        <option value="">Select...</option>
                        <?php
                        $drugList = $conn->query("SELECT drug_id, drug_name FROM drugs ORDER BY drug_name ASC");
                        while ($drug = $drugList->fetch_assoc()) {
                            echo "<option value='{$drug['drug_id']}'>{$drug['drug_name']}</option>";
                        }
                        ?>
                    </select>
                </label>
                <label>Quantity <input type="number" name="count_in" min="1" required></label>
                <label>Expiry (optional) <input type="date" name="expiry"></label>
                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="btn bg-sky-500">Save</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- Stock Out Dialog -->
    <dialog id="out-drug" class="dialog w-full sm:max-w-[425px]">
        <article>
            <header><h2>Stock Out</h2></header>
            <form class="form grid gap-4" action="../actions/manage_drug.php" method="POST">
                <input type="hidden" name="action" value="stock_out">
                <input type="hidden" name="drug_id" id="out_drug_id">
                <input type="hidden" name="current_stock" id="current_stock">
                <label>Quantity Out <input type="number" name="count_out" id="qty_out" min="1" required></label>
                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="btn bg-sky-500">Confirm</button>
                </footer>
            </form>
        </article>
    </dialog>

    <!-- History Dialog -->
<dialog id="history-dialog" class="dialog w-full sm:max-w-[600px]">
  <article>
    <header><h2 class="font-bold">Out History</h2></header>

    <table class="table w-full border mt-2">
      <thead>
        <tr>
          <th>Date Out</th>
          <th>Quantity</th>
        </tr>
      </thead>
      <tbody id="history-body">
        <tr><td colspan="2" class="text-center text-gray-500">Loading...</td></tr>
      </tbody>
    </table>

    <footer class="flex justify-end mt-4">
      <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Close</button>
    </footer>
  </article>
</dialog>


    <!-- Delete Dialog -->
    <dialog id="delete-dialog" class="dialog">
        <article>
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this drug?</p>
            <form action="../actions/manage_drug.php" method="POST">
                <input type="hidden" name="action" value="delete_drug">
                <input type="hidden" name="drug_id" id="delete_drug_id">
                <footer class="flex justify-end gap-2 mt-4">
                    <button type="button" class="btn-outline" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="btn-destructive">Delete</button>
                </footer>
            </form>
        </article>
    </dialog>

    <script>
        function openOutModal(id, stock) {
            document.getElementById('out_drug_id').value = id;
            document.getElementById('current_stock').value = stock;
            document.getElementById('out-drug').showModal();
        }

        function openDeleteDialog(id) {
            document.getElementById('delete_drug_id').value = id;
            document.getElementById('delete-dialog').showModal();
        }

        function openViewDialog(id) {
            fetch(`../actions/get_drug_info.php?drug_id=${id}`)
                .then(response => response.json())
                .then(data => {
                    // Fill drug info
                    document.getElementById('drug-info').innerHTML = `
                        <p><strong>ID:</strong> ${data.drug_id}</p>
                        <p><strong>Name:</strong> ${data.drug_name}</p>
                        <p><strong>Inventory:</strong> ${data.inventory}</p>
                        <p><strong>Last Date In:</strong> ${data.last_in ?? '—'}</p>
                        <p><strong>Last Date Out:</strong> ${data.last_out ?? '—'}</p>
                    `;

                    // Fill drug_in table
                    const tbody = document.getElementById('in-table-body');
                    if (data.in_records.length > 0) {
                        tbody.innerHTML = data.in_records.map(r => `
                            <tr>
                                <td>${r.date_in}</td>
                                <td>${r.expiry ?? '—'}</td>
                                <td>${r.count_in}</td>
                                <td>
                                    <button class="btn-sm-outline text-xs" onclick="openOutModal(${data.drug_id}, ${r.count_in})">Out</button>
                                    <button class="btn-sm-outline text-xs" onclick="openHistory(${r.in_id})">History</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-gray-500">No IN records found</td></tr>`;
                    }

                    document.getElementById('view-dialog').showModal();
                });
        }

        // Placeholder until history feature is added
        function openHistory(drug_id) {
  const tbody = document.getElementById('history-body');
  tbody.innerHTML = `<tr><td colspan="2" class="text-center text-gray-500">Loading...</td></tr>`;

  fetch(`../actions/get_history.php?drug_id=${drug_id}`)
    .then(res => res.json())
    .then(data => {
      if (data.length > 0) {
        tbody.innerHTML = data.map(row => `
          <tr>
            <td>${row.date_out}</td>
            <td>${row.count_out}</td>
          </tr>
        `).join('');
      } else {
        tbody.innerHTML = `<tr><td colspan="2" class="text-center text-gray-500">No OUT history found</td></tr>`;
      }
      document.getElementById('history-dialog').showModal();
    });
}

    </script>
</body>
</html>
