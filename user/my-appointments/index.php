<?php
session_start();
require_once '../../db/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_pets = [];
$stmt = $conn->prepare("SELECT id, name FROM pets WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_pets[] = $row;
}
$stmt->close();

$all_services = [];
$stmt = $conn->prepare("SELECT id, name, price FROM services");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $all_services[] = $row;
}
$stmt->close();

$stmt = $conn->prepare(
    "SELECT a.id,
            a.pet_id,
            a.service_id,
            a.appointment_date,
            a.appointment_time,
            a.reason,
            a.status,
            p.name AS pet_name,
            p.image AS pet_image,
            s.name AS service_name,
            s.price AS service_price
     FROM appointments a
     JOIN pets p ON a.pet_id = p.id
     JOIN services s ON a.service_id = s.id
     WHERE a.user_id = ? AND a.status IN ('pending', 'confirmed')
     ORDER BY a.appointment_date, a.appointment_time"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();

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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://unpkg.com/feather-icons"></script>

    <title>My Appointments</title>
    <style>
        .calendar-wrapper {
            position: relative;
        }

        .dialog {
            overflow: visible;
        }

        .dialog article {
            overflow: visible;
        }
    </style>
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar-user.php';
    ?>

    <main class="p-4 md:p-6" style="position: relative; z-index: 1;">
        <div class="flex justify-between">
            <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">My Appointments</a>
                </li>
            </ol>
            <button id="openAddDialogBtn" class="btn-sm bg-sky-500" type="button">Add Appointments</button>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="table">
                <caption>My Appointments</caption>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Pet Name</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appointments->num_rows > 0) : ?>
                        <?php $counter = 1; ?>
                        <?php while ($row = $appointments->fetch_assoc()) : ?>
                            <tr>
                                <td class="font-medium"><?= $counter++; ?></td>
                                <td><?= htmlspecialchars($row['pet_name']); ?></td>
                                <td><?= htmlspecialchars($row['service_name']); ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                                <td><?= htmlspecialchars(date('h:i A', strtotime($row['appointment_time']))); ?></td>
                                <td><?= htmlspecialchars($row['reason']); ?></td>
                                <td><?= htmlspecialchars(ucfirst($row['status'])); ?></td>
                                <td>
                                    <div class="flex gap-2 w-full justify-end">
                                        <button class="btn-sm-outline py-0 text-xs edit-btn" data-appointment-id="<?= (int)$row['id']; ?>">
                                            Edit
                                        </button>
                                        <button type="button" class="btn-sm bg-rose-500 py-0 text-xs cancel-btn" data-appointment-id="<?= (int)$row['id']; ?>">
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center">No appointments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <dialog id="addAppointmentDialog" class="dialog w-full sm:max-w-[425px] max-h-[612px]" onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Add appointments</h2>
                <p>Enter the appointment details below. Click save when you're done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/create_appointment.php" method="POST" id="createAppointmentForm">
                    <input type="hidden" name="action" value="add">

                    <div class="grid gap-3">
                        <label for="create_pet_id">Pets</label>
                        <select id="create_pet_id" name="pet_id" required class="w-[180px]">
                            <option value="">Select a pet</option>
                            <?php foreach ($user_pets as $pet) : ?>
                                <option value="<?= (int)$pet['id'] ?>"><?= htmlspecialchars($pet['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid gap-3">
                        <label for="create_service_id">Service</label>
                        <select id="create_service_id" name="service_id" required class="w-[180px]">
                            <option value="">Select a service</option>
                            <?php foreach ($all_services as $service) : ?>
                                <option value="<?= (int)$service['id'] ?>"><?= htmlspecialchars($service['name']) . ' - ₱' . htmlspecialchars($service['price']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="calendar-wrapper" id="create_calendar_wrapper">
                        <label for="create_appointment_date">Date:</label>
                        <input type="text" id="create_appointment_date" name="appointment_date" autocomplete="off" required>
                    </div>

                    <label for="create_appointment_time">Time:</label>
                    <select id="create_appointment_time" name="appointment_time" required>
                        <option value="">Select a time</option>
                    </select>

                    <div class="grid gap-3">
                        <label for="create_reason">Reason</label>
                        <input type="text" value="" id="create_reason" name="reason" placeholder="Enter reason" />
                    </div>

                    <footer class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn-outline" onclick="document.getElementById('addAppointmentDialog').close()">Cancel</button>
                        <button type="submit" class="btn bg-sky-500">Save changes</button>
                    </footer>
                </form>
            </section>
        </article>
    </dialog>

    <dialog id="rescheduleDialog" class="dialog w-full sm:max-w-[425px] max-h-[612px]" onclick="if (event.target === this) this.close()">
        <article class="w-md">
            <header>
                <h2>Reschedule appointment</h2>
                <p>Change appointment details below. Click save when done.</p>
            </header>

            <section>
                <form class="form grid gap-4" action="../actions/reschedule_appointment.php" method="POST" id="rescheduleForm">
                    <input type="hidden" name="appointment_id" id="reschedule_appointment_id" value="">

                    <div class="grid gap-3">
                        <label for="reschedule_pet_id">Pets</label>
                        <select id="reschedule_pet_id" name="pet_id" required class="w-[180px]">
                            <option value="">Select a pet</option>
                            <?php foreach ($user_pets as $pet) : ?>
                                <option value="<?= (int)$pet['id'] ?>"><?= htmlspecialchars($pet['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid gap-3">
                        <label for="reschedule_service_id">Service</label>
                        <select id="reschedule_service_id" name="service_id" required class="w-[180px]">
                            <option value="">Select a service</option>
                            <?php foreach ($all_services as $service) : ?>
                                <option value="<?= (int)$service['id'] ?>"><?= htmlspecialchars($service['name']) . ' - ₱' . htmlspecialchars($service['price']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="calendar-wrapper" id="reschedule_calendar_wrapper">
                        <label for="reschedule_date">Date:</label>
                        <input type="text" id="reschedule_date" name="appointment_date" autocomplete="off" required>
                    </div>

                    <label for="reschedule_time">Time:</label>
                    <select id="reschedule_time" name="appointment_time" required>
                        <option value="">Select a time</option>
                    </select>

                    <div class="grid gap-3">
                        <label for="reschedule_reason">Reason</label>
                        <input type="text" value="" id="reschedule_reason" name="reason" placeholder="Enter reason" />
                    </div>

                    <footer class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn-outline" onclick="document.getElementById('rescheduleDialog').close()">Cancel</button>
                        <button type="submit" class="btn bg-sky-500">Save changes</button>
                    </footer>
                </form>
            </section>
        </article>
    </dialog>

    <script>
        const restrictedDates = <?php echo json_encode($restricted_dates); ?>;
        const timeSlots = <?php echo json_encode($time_slots); ?>;

        const formatSlot = (slot) => {
            const d = new Date(`1970-01-01T${slot}`);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        };

        const normalizeDateInput = (value) => {
            if (!value) return '';
            if (typeof value === 'string') return value;
            if (value instanceof Date) return value.toISOString().split('T')[0];
            return '';
        };

        const isTimeWithinRestriction = (dateStr, time) => {
            const normalizedDate = normalizeDateInput(dateStr);
            if (!normalizedDate) return false;

            return restrictedDates.some((restriction) => (
                restriction.date === normalizedDate &&
                time >= restriction.start_time &&
                time <= restriction.end_time
            ));
        };

        const isFullyRestricted = (dateValue) => {
            const normalizedDate = normalizeDateInput(dateValue);
            if (!normalizedDate) return false;

            return restrictedDates.some((restriction) => (
                restriction.date === normalizedDate &&
                restriction.start_time === '00:00:00' &&
                restriction.end_time === '23:59:59'
            ));
        };

        const updateTimeSlotsFor = (selectId, dateValue) => {
            const select = document.getElementById(selectId);
            if (!select) return;

            select.innerHTML = '<option value="">Select a time</option>';

            if (!dateValue) return;

            const dateObj = new Date(dateValue);
            if (Number.isNaN(dateObj.getTime())) return;

            if (dateObj.getDay() === 0 || isFullyRestricted(dateValue)) return;

            timeSlots.forEach((slot) => {
                if (!isTimeWithinRestriction(dateValue, slot)) {
                    const opt = document.createElement('option');
                    opt.value = slot;
                    opt.textContent = formatSlot(slot);
                    select.appendChild(opt);
                }
            });
        };

        let createDatePicker = null;
        let rescheduleDatePicker = null;

        document.addEventListener('DOMContentLoaded', () => {
            const addDialog = document.getElementById('addAppointmentDialog');

            createDatePicker = flatpickr('#create_appointment_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                appendTo: document.getElementById('create_calendar_wrapper'),
                static: true,
                disable: [
                    (date) => date.getDay() === 0 || isFullyRestricted(date)
                ],
                onReady() {
                    this.calendarContainer.classList.add('flatpickr-calendar--dialog');
                },
                onOpen() {
                    updateTimeSlotsFor('create_appointment_time', this.input.value);
                },
                onChange(selectedDates, dateStr) {
                    updateTimeSlotsFor('create_appointment_time', dateStr);
                }
            });

            rescheduleDatePicker = flatpickr('#reschedule_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                appendTo: document.getElementById('reschedule_calendar_wrapper'),
                static: true,
                disable: [
                    (date) => date.getDay() === 0 || isFullyRestricted(date)
                ],
                onReady() {
                    this.calendarContainer.classList.add('flatpickr-calendar--dialog');
                },
                onOpen() {
                    updateTimeSlotsFor('reschedule_time', this.input.value);
                },
                onChange(selectedDates, dateStr) {
                    updateTimeSlotsFor('reschedule_time', dateStr);
                }
            });

            document.getElementById('openAddDialogBtn').addEventListener('click', () => {
                addDialog.showModal();
                createDatePicker.open();
            });

            document.querySelectorAll('.edit-btn').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const appointmentId = this.getAttribute('data-appointment-id');
                    openRescheduleModal(appointmentId);
                });
            });

            document.querySelectorAll('.cancel-btn').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const appointmentId = this.getAttribute('data-appointment-id');
                    cancelAppointment(appointmentId);
                });
            });
        });

        const openRescheduleModal = (appointmentId) => {
            fetch(`../actions/get_appointment.php?id=${appointmentId}`)
                .then((response) => {
                    if (!response.ok) throw new Error('Failed to fetch appointment data');
                    return response.json();
                })
                .then((data) => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    document.getElementById('reschedule_appointment_id').value = data.id;
                    document.getElementById('reschedule_pet_id').value = data.pet_id;
                    document.getElementById('reschedule_service_id').value = data.service_id;

                    rescheduleDatePicker.setDate(data.appointment_date, true);
                    updateTimeSlotsFor('reschedule_time', data.appointment_date);

                    const rescheduleTimeSelect = document.getElementById('reschedule_time');

                    if (
                        data.appointment_time &&
                        !Array.from(rescheduleTimeSelect.options).some((opt) => opt.value === data.appointment_time)
                    ) {
                        const extraOption = document.createElement('option');
                        extraOption.value = data.appointment_time;
                        extraOption.textContent = formatSlot(data.appointment_time);
                        rescheduleTimeSelect.appendChild(extraOption);
                    }

                    rescheduleTimeSelect.value = data.appointment_time;
                    document.getElementById('reschedule_reason').value = data.reason;

                    document.getElementById('rescheduleDialog').showModal();
                    rescheduleDatePicker.open();
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Failed to load appointment data');
                });
        };

        const cancelAppointment = (appointmentId) => {
            if (!confirm('Are you sure you want to cancel this appointment?')) return;

            fetch('../actions/cancel_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${appointmentId}`
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert('Appointment cancelled successfully!');
                        window.location.reload();
                    } else {
                        alert(data.error || 'Failed to cancel appointment');
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert('An error occurred. Please try again.');
                });
        };
    </script>
</body>

</html>