<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
$statusFilter = '';
if (isset($_GET['status'])) {
    $statusFilter = $_GET['status'];
}

// Check access for appointments module
$module = 'appointments';
$access = restrictAccess($conn, $_SESSION['user_id'], $module);

// Update past confirmed appointments to completed
$current_datetime = date('Y-m-d H:i:s');
$stmt = $conn->prepare("UPDATE appointments SET status = 'completed' 
                        WHERE status = 'confirmed' 
                        AND CONCAT(appointment_date, ' ', appointment_time) < ?");
$stmt->bind_param("s", $current_datetime);
$stmt->execute();
$stmt->close();

// Fetch appointments
$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, 
        u.name AS user_name, p.name AS pet_name, p.image AS pet_image, s.name AS service_name 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN pets p ON a.pet_id = p.id 
        JOIN services s ON a.service_id = s.id";

if ($statusFilter) {
    $sql .= " WHERE a.status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $statusFilter);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $color = 'rgba(55, 136, 216, 0.7)';
    if ($row['status'] == 'confirmed') {
        $color = 'rgba(40, 167, 69, 0.7)';
    } else if ($row['status'] == 'cancelled') {
        $color = 'rgba(220, 53, 69, 0.7)';
    } else if ($row['status'] == 'completed') {
        $color = 'rgba(108, 117, 125, 0.7)';
    }
    $appointments[] = [
        'id' => $row['id'],
        'title' => "{$row['pet_name']} ({$row['service_name']}) - {$row['status']}",
        'start' => "{$row['appointment_date']}T{$row['appointment_time']}",
        'backgroundColor' => $color,
        'borderColor' => $color,
        'extendedProps' => [
            'appointment_id' => $row['id'],
            'user_name' => $row['user_name'],
            'pet_name' => $row['pet_name'],
            'pet_image' => $row['pet_image'],
            'service_name' => $row['service_name'],
            'reason' => $row['reason'],
            'status' => $row['status']
        ]
    ];
}
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
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <style>
        .pet-image { width: 100%; max-height: 250px; border-radius: 5px; object-fit: cover; }
        .calendar-container.hidden { display: none; }
        .details-container.visible { display: block; }
    </style>
</head>
<body>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
include '../../components/sidebar.php';
?>
<main class="p-4 md:p-6">
    <div class="details-container" id="appointmentDetails"></div>
    <div class="calendar-container" id="calendarContainer">
        <h2 class="text-lg font-semibold mb-2">Appointments</h2>
        <div id="calendar" class="rounded-md mt-3 pt-0 border overflow-hidden"></div>
    </div>
</main>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const hasWriteAccess = <?php echo json_encode($access['has_write_access']); ?>;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'listWeek',
        height: 600,
        hiddenDays: [0],
        events: <?php echo json_encode($appointments); ?>,
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            let actions = '';

            if (props.status === 'pending') {
                actions += `
                    <button onclick="updateStatus(${props.appointment_id}, 'approve')" ${hasWriteAccess ? '' : 'disabled'}>Accept</button>
                    <button class="danger" onclick="updateStatus(${props.appointment_id}, 'cancel')" ${hasWriteAccess ? '' : 'disabled'}>Reject</button>`;
            } 
            else if (props.status === 'confirmed') {
                actions += `
                    <button class="danger" onclick="updateStatus(${props.appointment_id}, 'cancel')" ${hasWriteAccess ? '' : 'disabled'}>Reject</button>
                    <button class="success" onclick="completeAppointment(${props.appointment_id})" ${hasWriteAccess ? '' : 'disabled'}>Completed</button>`;
            }

            const detailsHTML = `
                <div style="display:flex;gap:20px;">
                    <div style="flex:1;min-width:250px;">
                        <img src="../../uploads/${props.pet_image}" class="pet-image" alt="Pet Image">
                        <p><strong>Pet:</strong> ${props.pet_name}</p>
                        <p><strong>Owner:</strong> ${props.user_name}</p>
                        <p><strong>Service:</strong> ${props.service_name}</p>
                        <p><strong>Status:</strong> ${props.status}</p>
                    </div>
                    <div style="flex:2;">
                        <p><strong>Date & Time:</strong> ${event.start.toLocaleString()}</p>
                        <p><strong>Reason:</strong> ${props.reason}</p>
                        <div style="margin-top:10px;text-align:center;">${actions}</div>
                    </div>
                </div>`;
            showDetailsView(detailsHTML);
        }
    });

    calendar.render();
});

function updateStatus(id, action) {
    fetch('../actions/manage_appointment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `appointment_id=${id}&action=${action}`
    }).then(r => r.json()).then(data => {
        alert(data.message || data.error);
        if (data.success) location.reload();
    });
}

function completeAppointment(appointment_id) {
    const illness = prompt("Enter the illness:");
    if (!illness) { alert("Illness is required."); return; }

    const treatment = prompt("Enter the treatment:");
    if (!treatment) { alert("Treatment is required."); return; }

    fetch('../actions/manage_appointment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `appointment_id=${appointment_id}&action=complete&illness=${encodeURIComponent(illness)}&treatment=${encodeURIComponent(treatment)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function showDetailsView(content) {
    const calendarContainer = document.getElementById('calendarContainer');
    const detailsContainer = document.getElementById('appointmentDetails');
    calendarContainer.classList.add('hidden');
    detailsContainer.classList.add('visible');
    detailsContainer.innerHTML = content + `<div style="margin-top:20px;text-align:center;"><button onclick="showCalendarView()" class="btn-sm bg-sky-500">Back</button></div>`;
}

function showCalendarView() {
    document.getElementById('calendarContainer').classList.remove('hidden');
    document.getElementById('appointmentDetails').classList.remove('visible');
    document.getElementById('appointmentDetails').innerHTML = '';
}
</script>
</body>
</html>
