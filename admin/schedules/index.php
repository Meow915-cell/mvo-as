<?php
session_start();
require_once '../../db/db_connect.php';
require_once '../restrict_access.php'; // Include the access control script

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Check access for schedules module
$module = 'schedules';
$access = restrictAccess($conn, $_SESSION['user_id'], $module); 

// Fetch all restricted dates
$stmt = $conn->prepare('SELECT id, restricted_date, reason, start_time, end_time FROM restricted_dates');
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();

$events = [];
$restricted_dates = []; // yyyy-mm-dd list

while ($row = $result->fetch_assoc()) {
    $startLabel = date('g:i A', strtotime($row['start_time']));
    $endLabel = date('g:i A', strtotime($row['end_time']));
    // Determine if it's an all-day event
    $isAllDay = ($row['start_time'] === '00:00:00' && (strtotime($row['end_time']) >= strtotime('23:59:00')));

    $title = $row['reason'] ? $row['reason'] : 'Restricted Time';
    
    $event = [
        'id' => $row['id'],
        'title' => $title,
        'color' => '#dc3545', // Red color for restricted
        'textColor' => '#ffffff',
        'allDay' => false
    ];

    if ($isAllDay) {
        $event['allDay'] = true;
        $event['start'] = $row['restricted_date'];
        $event['title'] = $row['reason'] ? $row['reason'] : 'Full Day Restriction';
        $event['end'] = date('Y-m-d', strtotime($row['restricted_date'] . ' +1 day'));
    } else {
        $title .= ": $startLabel - $endLabel";
        $event['title'] = $title;
        $event['start'] = $row['restricted_date'] . 'T' . $row['start_time'];
        $event['end'] = $row['restricted_date'] . 'T' . $row['end_time'];
    }

    $events[] = $event;
    if (!in_array($row['restricted_date'], $restricted_dates)) {
        $restricted_dates[] = $row['restricted_date'];
    }
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
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js" defer></script>

    <style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }

    /* Custom styles for your fullCalendar cell enhancements */
    .restricted-date {
        background-color: var(--color-danger-100) !important;
    }

    .time-label {
        font-size: 0.75rem;
        line-height: 1.1;
        padding: 2px 4px;
        text-align: left;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        border-top: 1px solid var(--color-border);
    }

    /* Styling for the disabled time inputs/sliders */
    .time-input-group.disabled {
        opacity: 0.6;
        pointer-events: none;
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
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Schedules</a>
                </li>
            </ol>
        </div>
        <div class="main-content">
            <!-- HEADER BUTTONS AND CUSTOM LABEL -->
            <div id="calendar-header-controls" class="flex items-center justify-between mb-4">
                <div>
                    <button onclick="calendar.prev();" class="btn-sm-icon bg-sky-500"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-chevron-left-icon lucide-chevron-left">
                            <path d="m15 18-6-6 6-6" />
                        </svg></button>
                    <button onclick="calendar.next();" class="btn-sm-icon bg-sky-500"><svg
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-chevron-right-icon lucide-chevron-right">
                            <path d="m9 18 6-6-6-6" />
                        </svg> </button>

                    <button onclick="calendar.today();" class="btn-sm bg-sky-500">
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
                <p id="calendar-title" class="text-xl font-semibold text-foreground"></p>
            </div>
            <!-- END HEADER BUTTONS AND CUSTOM LABEL -->
            <?php if ($access['has_read_access']): ?>
            <div id="calendar" style="margin:auto"></div>
            <?php else: ?>
            <p class="error">You do not have permission to view this page.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Restricted Date Modal -->
    <dialog id="restrictedDateModal" class="dialog w-full sm:max-w-lg"
        onclick="if (event.target === this) this.close()">
        <article>
            <header>
                <div class="flex justify-between">
                    <h2 id="modalTitle">Add Restricted Date</h2>
                    <button class="w-max" type="button" aria-label="Close dialog" onclick="closeModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-x-icon lucide-x">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

            </header>

            <section>
                <form class="form grid gap-4" action="../actions/manage_restricted_date.php" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="date_id" id="date_id">
                    <!-- The actual time values will be set by JS on hidden inputs -->
                    <input type="hidden" name="start_time" id="start_time_value">
                    <input type="hidden" name="end_time" id="end_time_value">

                    <div class="grid gap-3">
                        <label for="restricted_date">Date</label>
                        <input type="date" name="restricted_date" id="restricted_date" readonly required
                            class="text-foreground" />
                    </div>

                    <div class="grid gap-3">
                        <label for="reason">Reason (Optional)</label>
                        <input type="text" name="reason" id="reason" placeholder="e.g., Holiday, Maintenance" />
                    </div>

                    <div class="grid gap-3">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="wholeDayChk" name="whole_day" value="1" />
                            <label for="wholeDayChk">Restrict Whole Day</label>
                        </div>
                    </div>

                    <!-- TWO SLIDERS FOR START/END TIME -->
                    <div id="time-input-group" class="time-input-group grid gap-3">
                        <label>Restricted Time Range</label>
                        <div class="grid gap-2">
                            <label for="start_time_slider" class="text-sm">Start Time: <span id="start_readout"
                                    class="font-semibold text-primary">09:00 AM</span></label>
                            <input type="range" class="input w-full time-slider" id="start_time_slider" min="540"
                                max="960" step="30" value="540" />
                        </div>
                        <div class="grid gap-2">
                            <label for="end_time_slider" class="text-sm">End Time: <span id="end_readout"
                                    class="font-semibold text-primary">04:00 PM</span></label>
                            <input type="range" class="input w-full time-slider" id="end_time_slider" min="540"
                                max="960" step="30" value="960" />
                        </div>
                    </div>
                    <!-- END TWO SLIDERS -->

                    <footer class="flex justify-between gap-2 mt-4">
                        <button type="button" id="deleteBtn" class="btn btn-danger btn-outline"
                            style="display:none;">Delete</button>
                        <div class="flex gap-2 ml-auto">
                            <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                            <button type="submit" class="btn-primary">Save changes</button>
                        </div>
                    </footer>
                </form>
            </section>
        </article>
    </dialog>
    <!-- END Restricted Date Modal -->


    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <script>
    let calendar;

    const hasWriteAccess = <?php echo json_encode($access['has_write_access']); ?>;

    /* Time Helpers */
    const SLIDER_START_MIN = 540; // 09:00
    const SLIDER_END_MIN = 960; // 16:00

    function minToTime(m) {
        const h = Math.floor(m / 60);
        const mm = (m % 60).toString().padStart(2, '0');
        return `${h.toString().padStart(2,'0')}:${mm}:00`;
    }

    function minToLabel(m) {
        let h = Math.floor(m / 60);
        const mm = (m % 60).toString().padStart(2, '0');
        const ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 === 0 ? 12 : h % 12;
        return `${h}:${mm} ${ap}`;
    }

    const updateSlider = (el) => {
        const min = parseFloat(el.min || 0);
        const max = parseFloat(el.max || 100);
        const value = parseFloat(el.value);
        const percent = (max === min) ? 0 : ((value - min) / (max - min)) * 100;

        el.style.setProperty('--slider-value', `${percent}%`);
    };

    const updateTimeSliders = () => {
        const startSlider = document.getElementById('start_time_slider');
        const endSlider = document.getElementById('end_time_slider');
        const startReadout = document.getElementById('start_readout');
        const endReadout = document.getElementById('end_readout');
        const startValue = document.getElementById('start_time_value');
        const endValue = document.getElementById('end_time_value');

        if (!startSlider || !endSlider) return; // Safety check

        // Enforce start slider value is always <= end slider value
        if (parseInt(startSlider.value) > parseInt(endSlider.value)) {
            startSlider.value = endSlider.value;
        }

        updateSlider(startSlider);
        updateSlider(endSlider);

        startReadout.textContent = minToLabel(parseInt(startSlider.value));
        endReadout.textContent = minToLabel(parseInt(endSlider.value));
        startValue.value = minToTime(parseInt(startSlider.value));
        endValue.value = minToTime(parseInt(endSlider.value));
    };

    /* Modal Helpers */
    const restrictedDateModal = document.getElementById('restrictedDateModal');

    function openModal() {
        if (!hasWriteAccess) {
            alert('You do not have permission to add or edit restricted dates.');
            return;
        }
        if (restrictedDateModal) restrictedDateModal.showModal();
    }

    function closeModal() {
        if (restrictedDateModal) restrictedDateModal.close();
    }

    /* Whole-day behaviour */
    const wholeChk = document.getElementById('wholeDayChk');
    const timeInputGroup = document.getElementById('time-input-group');

    function setWholeDay(on) {
        if (!hasWriteAccess) return;
        if (on) {
            timeInputGroup.classList.add('disabled');
            document.getElementById('start_time_value').value = '00:00:00';
            document.getElementById('end_time_value').value = '23:59:00';
        } else {
            timeInputGroup.classList.remove('disabled');
            // Re-apply current slider values
            updateTimeSliders();
        }
    }

    function initializeSliders() {
        const sliders = document.querySelectorAll('input[type="range"].input');
        const startSlider = document.getElementById('start_time_slider');
        const endSlider = document.getElementById('end_time_slider');

        if (!startSlider || !endSlider) return; // Safety exit

        sliders.forEach(slider => {
            updateSlider(slider);
        });
        updateTimeSliders();

        startSlider.addEventListener('input', updateTimeSliders);
        endSlider.addEventListener('input', updateTimeSliders);
    }

    function updateCalendarLabel() {
        const titleElement = document.getElementById('calendar-title');
        if (calendar && titleElement) {
            const currentDate = calendar.getDate();

            const options = {
                year: 'numeric',
                month: 'long'
            };
            titleElement.textContent = currentDate.toLocaleDateString('en-US', options);
        }
    }

    function navigateCalendar(direction) {
        if (!calendar) return;
        if (direction === 'prev') {
            calendar.prev();
        } else if (direction === 'next') {
            calendar.next();
        } else if (direction === 'today') {
            calendar.today();
        }
        updateCalendarLabel();
    }


    /* Calendar and Main Logic */
    document.addEventListener('DOMContentLoaded', () => {
        initializeSliders();

        const events = <?php echo json_encode($events); ?>;
        const restrictedDates = <?php echo json_encode($restricted_dates); ?>;
        const calendarEl = document.getElementById('calendar');

        if (!calendarEl || !<?php echo $access['has_read_access'] ? 'true' : 'false'; ?>) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: hasWriteAccess,
            timeZone: 'Asia/Manila',
            hiddenDays: [0],
            displayEventTime: false,
            headerToolbar: false,
            events: events,

             datesSet: function(info) {
        updateCalendarLabel(info);  // update header title
        updateTodayButtonState();   // update Today button state
    },
       

            select(info) {
                if (!hasWriteAccess) {
                    alert('You do not have permission to add restricted dates.');
                    return;
                }
                const dateStr = info.startStr.split('T')[0];
                document.getElementById('restricted_date').value = dateStr;
                document.getElementById('reason').value = '';

                wholeChk.checked = false;
                setWholeDay(false);

                document.getElementById('start_time_slider').value = SLIDER_START_MIN;
                document.getElementById('end_time_slider').value = SLIDER_END_MIN;
                updateTimeSliders();

                const ev = events.find(e => e.start && e.start.startsWith(dateStr));
                if (ev) {
                    prepareModal('edit', ev);
                } else {
                    prepareModal('add');
                }
                openModal();
            },

            eventClick(info) {
                if (!hasWriteAccess) {
                    alert('You do not have permission to edit restricted dates.');
                    return;
                }
                prepareModal('edit', info.event);
                openModal();
            },

            dayCellDidMount: function(arg) {
                const dateStr = arg.date.toISOString().split('T')[0];
                if (!restrictedDates.includes(dateStr)) return;

                arg.el.classList.add('restricted-date');
                const dayEvents = events.filter(e => e.start && e.start.startsWith(dateStr) && !e
                    .allDay);
                const allDayEvents = events.filter(e => e.start === dateStr && e.allDay);

                const frame = arg.el.querySelector('.fc-daygrid-day-frame');
                if (!frame) return;

                if (allDayEvents.length) {
                    const label = document.createElement('div');
                    label.className = 'time-label';
                    label.innerHTML = '⛔ Full Day Restricted';
                    frame.appendChild(label);
                    return;
                }

                if (!dayEvents.length) return;

                const WORK_START = SLIDER_START_MIN,
                    WORK_END = SLIDER_END_MIN;
                const toMin = t => {
                    const [h, m] = t.split(':').map(Number);
                    return h * 60 + m;
                };

                const blocks = dayEvents.map(e => {
                    const s = toMin(e.start.split('T')[1].slice(0, 5));
                    const en = toMin(e.end.split('T')[1].slice(0, 5));
                    return {
                        start: s,
                        end: en
                    };
                }).sort((a, b) => a.start - b.start);

                const merged = [];
                blocks.forEach(b => {
                    if (!merged.length || b.start > merged[merged.length - 1].end) {
                        merged.push(b);
                    } else {
                        merged[merged.length - 1].end = Math.max(merged[merged.length - 1]
                            .end, b.end);
                    }
                });

                const available = [];
                let cur = WORK_START;
                merged.forEach(b => {
                    if (cur < b.start) available.push({
                        start: cur,
                        end: b.start
                    });
                    cur = Math.max(cur, b.end);
                });
                if (cur < WORK_END) available.push({
                    start: cur,
                    end: WORK_END
                });

                const label = document.createElement('div');
                label.className = 'time-label';
                label.innerHTML += '⛔ ' + merged.map(b =>
                    `${minToLabel(Math.max(b.start,WORK_START))}-${minToLabel(Math.min(b.end,WORK_END))}`
                    ).join(', ');
                label.innerHTML += '<br>';
                label.innerHTML += available.length ?
                    '✅ ' + available.map(b => `${minToLabel(b.start)}-${minToLabel(b.end)}`).join(
                        ', ') :
                    '❌ No available Time';
                frame.appendChild(label);
            }
        });
        calendar.render();
        updateCalendarLabel();

        

        /* event listeners to the physical buttons */
        document.getElementById('prev-button').addEventListener('click', () => navigateCalendar('prev'));
        document.getElementById('next-button').addEventListener('click', () => navigateCalendar('next'));
        document.getElementById('today-button').addEventListener('click', () => navigateCalendar('today'));

        /* Delete from modal */
        document.getElementById('deleteBtn').onclick = () => {
            if (!hasWriteAccess) {
                alert('You do not have permission to delete restricted dates.');
                return;
            }
            if (!confirm(
                    'Are you absolutely sure you want to delete this restricted date? This action cannot be undone.'
                    )) return;

            fetch('../actions/manage_restricted_date.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'delete',
                        date_id: document.getElementById('date_id').value
                    })
                })
                .then(response => response.text())
                .then(responseText => {
                    if (responseText.trim() === 'OK') {
                        location.reload();
                    } else {
                        alert('Failed to delete: ' + responseText);
                    }
                })
                .catch(error => {
                    alert('An unexpected network error occurred during deletion: ' + error.message);
                });
        };

        function updateTodayButtonState() {
            const todayBtn = document.querySelector("button[onclick='calendar.today();']");
            if (!calendar || !todayBtn) return;

            // Get today's date and the currently viewed range
            const today = new Date();
            const viewStart = calendar.view.currentStart;
            const viewEnd = calendar.view.currentEnd;

            // Check if today's date falls within the current view
            const isTodayVisible = today >= viewStart && today < viewEnd;

            if (isTodayVisible) {
                todayBtn.classList.remove('btn-sm-outline');
                todayBtn.classList.add('btn-sm', 'bg-sky-500');
            } else {
                todayBtn.classList.remove('btn-sm', 'bg-sky-500');
                todayBtn.classList.add('btn-sm-outline');
            }
        }

        /* Helper to configure modal */
        function prepareModal(mode, ev = null) {
            const formAction = document.getElementById('formAction');
            const modalTitle = document.getElementById('modalTitle');
            const deleteBtn = document.getElementById('deleteBtn');
            const startSlider = document.getElementById('start_time_slider');
            const endSlider = document.getElementById('end_time_slider');

            if (mode === 'add') {
                modalTitle.textContent = 'Add Restricted Date';
                formAction.value = 'add';
                deleteBtn.style.display = 'none';
                document.getElementById('date_id').value = '';
                wholeChk.checked = false;
                setWholeDay(false);
            } else {
                // EDIT MODE
                modalTitle.textContent = 'Edit Restricted Date';
                formAction.value = 'update';
                deleteBtn.style.display = 'inline-block';
                document.getElementById('date_id').value = ev.id;
                document.getElementById('restricted_date').value = ev.startStr.split('T')[0];

                const colonIndex = ev.title.indexOf(':');
                let reason = (colonIndex > -1 ? ev.title.substring(0, colonIndex).trim() : ev.title.trim());
                reason = reason.replace(/^(Restricted Time|Full Day Restriction)/, '').trim();
                document.getElementById('reason').value = reason;

                if (ev.allDay) {
                    wholeChk.checked = true;
                    setWholeDay(true);
                } else {
                    wholeChk.checked = false;
                    setWholeDay(false);
                    const st = ev.startStr.split('T')[1].slice(0, 5);
                    const et = ev.endStr.split('T')[1].slice(0, 5);
                    const toMin = t => parseInt(t.substring(0, 2)) * 60 + parseInt(t.substring(3, 2));

                    startSlider.value = toMin(st);
                    endSlider.value = toMin(et);
                    updateTimeSliders(); // Update UI
                }
            }
        }

        // Whole-day checkbox handler
        wholeChk.addEventListener('change', e => {
            if (!hasWriteAccess) {
                e.preventDefault();
                alert('You do not have permission to modify restricted dates.');
                return;
            }
            setWholeDay(e.target.checked);
        });
    });
    </script>
</body>

</html>