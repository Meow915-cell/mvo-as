<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all appointments
$stmt = $conn->prepare("SELECT a.id, a.pet_id, a.service_id, a.appointment_date, a.appointment_time, a.reason, a.status, p.name AS pet_name, s.name AS service_name 
                        FROM appointments a 
                        JOIN pets p ON a.pet_id = p.id 
                        JOIN services s ON a.service_id = s.id 
                        WHERE a.user_id = ? AND (a.status = 'completed' OR a.status = 'cancelled')
                        ORDER BY a.appointment_date DESC, a.appointment_time DESC");

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->get_result();
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
    <style type="text/tailwindcss">
        @theme {
        --color-clifford: #da373d;
      }
    </style>
</head>
<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar-user.php';
    ?>
    <main class="p-4 md:p-6">
        <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Appointment History</a>
                </li>
            </ol>

        <div class="overflow-x-auto mt-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Pet Name</th>
                        <th>Service Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th class="text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appointments->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['pet_name']); ?></td>
                                <td><?= htmlspecialchars($row['service_name']); ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                                <td><?= htmlspecialchars($row['appointment_time']); ?></td>
                                <td><?= htmlspecialchars($row['reason']); ?></td>
                                <td class="text-right"><?= htmlspecialchars(ucfirst($row['status'])); ?></td>
                                
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No appointments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>
