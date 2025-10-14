<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's pets
$stmt = $conn->prepare("SELECT id, name FROM pets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pets = $stmt->get_result();
$stmt->close();

// Fetch services
$stmt = $conn->prepare("SELECT id, name, price FROM services");
$stmt->execute();
$services = $stmt->get_result();
$stmt->close();

// Fetch upcoming appointments
$stmt = $conn->prepare("SELECT a.id, a.pet_id, a.service_id, a.appointment_date, a.appointment_time, a.reason, a.status, p.name AS pet_name, p.image AS pet_image, s.name AS service_name, s.price AS service_price 
                        FROM appointments a 
                        JOIN pets p ON a.pet_id = p.id 
                        JOIN services s ON a.service_id = s.id 
                        WHERE a.user_id = ? AND a.status IN ('pending', 'confirmed') 
                        ORDER BY a.appointment_date, a.appointment_time");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();

// Fetch restricted dates with times
$stmt = $conn->prepare("SELECT restricted_date, start_time, end_time FROM restricted_dates");
$stmt->execute();
$restricted_dates_result = $stmt->get_result();
$restricted_dates = [];
while ($row = $restricted_dates_result->fetch_assoc()) {
    $restricted_dates[] = [
        'date' => $row['restricted_date'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time']
    ];
}
$stmt->close();

$conn->close();

// Predefined time slots (9:00 AM to 5:00 PM)
$time_slots = [
    '09:00:00', '10:00:00', '11:00:00', '12:00:00',
    '13:00:00', '14:00:00', '15:00:00', '16:00:00'
];
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
        <div class="flex justify-between">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">My Appointments</a>
                </li>
            </ol>
        </div>
        <!-- <header>
        <button
          type="button"
          onclick="document.dispatchEvent(new CustomEvent('basecoat:sidebar'))"
        >
          Toggle sidebar
        </button>
      </header> -->


        <div class="main-content">
            
        </div>
    </main>
</body>

</html>