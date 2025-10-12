<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
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

// Fetch appointments with pet image
$stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, 
                        u.name AS user_name, p.name AS pet_name, p.image AS pet_image, s.name AS service_name 
                        FROM appointments a 
                        JOIN users u ON a.user_id = u.id 
                        JOIN pets p ON a.pet_id = p.id 
                        JOIN services s ON a.service_id = s.id");
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
<html >

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../../src/output.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js" defer></script>
    <style>
    .pet-image {
        width: 100%;
        height: auto;
        max-height: 250px;
        border-radius: 5px;
        object-fit: cover;
    }


    .calendar-container {
        display: block;
    }

    .details-container {
        display: none;
    }

    .calendar-container.hidden {
        display: none;
    }

    .details-container.visible {
        display: block;
    }
    .fc .fc-list {
  font-size: 14px; /* change globally for list */
}

    </style>
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar.php';
    ?>

    <main class="p-4 md:p-6">


        <div class="details-container" id="appointmentDetails">

        </div>

        <div class="calendar-container" id="calendarContainer">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a onclick="showCalendarView()"
                        class="text-lg font-medium hover:text-foreground transition-colors">Appointments</a>
                </li>

            </ol>
            <div class="flex justify-between">
                <div>
                    <button onclick="calendar.prev();" class="btn-sm-icon-primary"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-chevron-left-icon lucide-chevron-left">
                            <path d="m15 18-6-6 6-6" />
                        </svg></button>
                    <button onclick="calendar.next();" class="btn-sm-icon"><svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-chevron-right-icon lucide-chevron-right">
                            <path d="m9 18 6-6-6-6" />
                        </svg> </button>

                    <button onclick="calendar.today();" class="btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-calendar-days-icon lucide-calendar-days">
                            <path d="M8 2v4" />
                            <path d="M16 2v4" />
                            <rect width="18" height="18" x="3" y="4" rx="2" />
                            <path d="M3 10h18" />
                            <path d="M8 14h.01" />
                            <path d="M12 14h.01" />
                            <path d="M16 14h.01" />
                            <path d="M8 18h.01" />
                            <path d="M12 18h.01" />
                            <path d="M16 18h.01" />
                        </svg> Today
                    </button>
                </div>
                <div class="text-lg font-semibold mb-2" id="dateRangeLabel"></div>
                <div>
                    <button onclick="changeCalendarView('listDay', this)" class="btn-sm-outline view-btn">Day</button>
                    <button onclick="changeCalendarView('listWeek', this)" class="btn-sm-outline view-btn">Week</button>
                    <button onclick="changeCalendarView('listMonth', this)"
                        class="btn-sm-outline view-btn">Month</button>
                </div>
            </div>

            <!-- <div >
  <input class="input" id="input-with-text" type="text" placeholder="Search">

</div> -->

            <div id="calendar" class="rounded-md mt-3 pt-0 border overflow-hidden"></div>

        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
    let calendar = null;

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const hasWriteAccess = <?php echo json_encode($access['has_write_access']); ?>;


        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'listWeek',
            headerToolbar: {
                left: '',

                // right: 'dayGridMonth,timeGridWeek,timeGridDay,list'
                right: ''
            },
            height: 590,
            hiddenDays: [0],
            slotMinTime: "09:00:00",
            slotMaxTime: "16:15:00",
            slotDuration: "00:15:00",
            allDaySlot: false,
            events: <?php echo json_encode($appointments); ?>,
            datesSet: function(info) {
                updateDateRangeLabel(info.start, info.end);
            },
            eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;

                let actions = '';
                if (props.status === 'pending') {
                    actions +=
                        `<button onclick="updateStatus(${props.appointment_id}, 'approve')" ${hasWriteAccess ? '' : 'disabled'}>Accept</button>
                                    <button class="danger" onclick="updateStatus(${props.appointment_id}, 'cancel')" ${hasWriteAccess ? '' : 'disabled'}>Reject</button>`;
                } else if (props.status === 'confirmed') {
                    actions +=
                        `<button class="danger" onclick="updateStatus(${props.appointment_id}, 'cancel')" ${hasWriteAccess ? '' : 'disabled'}>Reject</button>`;
                }

                const detailsHTML = `
                       
                      <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                        <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                          <a onclick="showCalendarView()" class="flex items-center gap-2 text-lg font-medium hover:text-foreground transition-colors"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-undo2-icon lucide-undo-2"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5a5.5 5.5 0 0 1-5.5 5.5H11"/></svg> Appointments</a>
                        </li>
                        <li>
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5"><path d="m9 18 6-6-6-6" /></svg>
                        </li>
                        <li class="inline-flex items-center gap-1.5">
                          <a class="hover:text-foreground transition-colors">Details</a>
                        </li>
                      </ol>
                       
                        <div style="display:flex; flex-wrap: wrap; gap:20px;">
                            <div style="flex:1; min-width: 250px; border-right:1px solid #ddd; padding-right:20px;">
                                <img src="../../uploads/${props.pet_image}" alt="Pet Image" class="pet-image">
                                <div style="border-top:1px solid #ddd; padding-top:10px; margin-top:10px;"><strong>Appointment ID:</strong> ${props.appointment_id}</div>
                                <div style="border-top:1px solid #ddd; padding-top:10px; margin-top:10px;"><strong>User:</strong> ${props.user_name}</div>
                                <div style="border-top:1px solid #ddd; padding-top:10px; margin-top:10px;"><strong>Pet:</strong> ${props.pet_name}</div>
                                <div style="border-top:1px solid #ddd; padding-top:10px; margin-top:10px;"><strong>Service:</strong> ${props.service_name}</div>
                                <div style="border-top:1px solid #ddd; padding-top:10px; margin-top:10px;"><strong>Status:</strong> ${props.status}</div>
                            </div>
                            <div style="flex:2; min-width: 300px; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="margin-bottom:10px;">
                                        <strong>Date & Time:</strong><br>
                                        ${event.start.toLocaleString()}
                                    </div>
                                    <div style="border-top:1px solid #ddd; padding-top:10px;">
                                        <strong>Reason:</strong><br>
                                        <p style="margin-top:5px; overflow:auto; max-height:400px;">${props.reason}</p>
                                    </div>
                                </div>
                                <div style="margin-top:auto; padding-top:10px; border-top:1px solid #ddd; text-align:center; display:flex; gap:1rem; justify-content:center;">
                                    ${actions}
                                </div>
                            </div>
                        </div>
                    `;
                showDetailsView(detailsHTML);
            }
        });
        calendar.render();
        const defaultBtn = document.querySelector("[onclick*='listWeek']");
if (defaultBtn) {
    changeCalendarView('listWeek', defaultBtn);
}
    });

    function updateStatus(appointment_id, action) {
        if (<?php echo json_encode($access['has_write_access']); ?> === false) {
            alert('You do not have permission to modify appointments.');
            return;
        }
        fetch('../actions/manage_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `appointment_id=${appointment_id}&action=${action}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Request error: ' + err.message);
            });
    }

    function showDetailsView(content) {
        const calendarContainer = document.getElementById('calendarContainer');
        const detailsContainer = document.getElementById('appointmentDetails');

        // Hide calendar, show details
        calendarContainer.classList.add('hidden');
        detailsContainer.innerHTML = content;
        detailsContainer.classList.add('visible');
        detailsContainer.classList.remove('details-container'); // Remove the base class that has display: none
    }

    function changeCalendarView(viewName, btn) {
        calendar.changeView(viewName);

        // Reset all buttons to outline style
        document.querySelectorAll('.view-btn').forEach(b => {
            b.classList.remove('btn-sm');
            b.classList.add('btn-sm-outline');
        });

        // Set clicked button as active
        btn.classList.remove('btn-sm-outline');
        btn.classList.add('btn-sm');
    }



    function showCalendarView() {
        const calendarContainer = document.getElementById('calendarContainer');
        const detailsContainer = document.getElementById('appointmentDetails');

        // Hide details, show calendar
        detailsContainer.classList.remove('visible');
        detailsContainer.classList.add('details-container'); // Add back the base class
        detailsContainer.innerHTML = '';
        calendarContainer.classList.remove('hidden');

        // Update calendar size
        if (calendar) {

            calendar.updateSize();

        }
    }

    function updateDateRangeLabel(start, end) {
        const options = {
            month: 'short',
            day: 'numeric'
        };
        const optionsWithYear = {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        };

        // Adjust end (FullCalendar end is exclusive)
        const realEnd = new Date(end);
        realEnd.setDate(realEnd.getDate() - 1);

        const sameDay = start.toDateString() === realEnd.toDateString();

        let label = '';
        if (sameDay) {
            // Example: Oct 9, 2024
            label = start.toLocaleDateString('en-US', optionsWithYear);
        } else {
            // Example: Oct 9 - Oct 14, 2024
            const startStr = start.toLocaleDateString('en-US', options);
            const endStr = realEnd.toLocaleDateString('en-US', options);
            label = `${startStr} - ${endStr}, ${realEnd.getFullYear()}`;
        }

        document.getElementById('dateRangeLabel').textContent = label;
    }
    </script>
</body>

</html>