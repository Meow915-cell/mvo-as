<?php

// Database connection
$servername = "127.0.0.1";
$username = "root"; // Adjust with your database username
$password = ""; // Adjust with your database password
$dbname = "mvo_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Query for total completed services
$total_services_query = "SELECT COUNT(*) as total FROM appointments WHERE status = 'completed'";
$total_services_stmt = $conn->prepare($total_services_query);
$total_services_stmt->execute();
$total_services = $total_services_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Query for completed services by date and service name
$query = "SELECT a.appointment_date, s.name as service_name, COUNT(*) as count 
          FROM appointments a 
          JOIN services s ON a.service_id = s.id 
          WHERE a.status = 'completed' 
          GROUP BY a.appointment_date, s.id 
          ORDER BY a.appointment_date, s.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$dates = [];
$services_data = [];
$service_names = [];

// Get unique dates and service names
foreach ($results as $row) {
    if (!in_array($row['appointment_date'], $dates)) {
        $dates[] = $row['appointment_date'];
    }
    if (!in_array($row['service_name'], $service_names)) {
        $service_names[] = $row['service_name'];
    }
}

// Initialize data arrays with zeros
foreach ($service_names as $service) {
    $services_data[$service] = array_fill_keys($dates, 0);
}

// Fill data arrays
foreach ($results as $row) {
    $services_data[$row['service_name']][$row['appointment_date']] = $row['count'];
}

// Prepare datasets for Chart.js
$datasets = [];
$colors = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6']; // Distinct colors
$color_index = 0;
foreach ($service_names as $service) {
    $datasets[] = [
        'label' => $service,
        'data' => array_values($services_data[$service]),
        'borderColor' => $colors[$color_index % count($colors)],
        'backgroundColor' => $colors[$color_index % count($colors)],
        'fill' => false,
        'tension' => 0.4
    ];
    $color_index++;
}

// Convert to JSON
$labels = json_encode($dates);
$datasets_json = json_encode($datasets);

$conn = null; // Close connection
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style type="text/tailwindcss">
        @theme {
        --color-clifford: #da373d;
      }
    </style>
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    include '../../components/sidebar.php';
    ?>

    <main class="p-4 md:p-6">
        <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
            <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                <a class="text-lg font-medium hover:text-foreground transition-colors">Dashboard</a>
            </li>
        </ol>
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            <div class="card !bg-sky-700/86 h-44">
                <section class="flex justify-between items-center ">
                    <div>
                        <p class="mb-2  text-md font-medium text-white">Pending Appointments</p>
                        <p class="text-2xl font-bold text-white">0</p>
                    </div>
                    <div class="flex items-center justify-center size-24 rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                </section>
            </div>

            <div class="card !bg-sky-700/86">
                <section class="flex justify-between items-center">
                    <div>
                        <p class="mb-2 text-md font-medium text-white">Total Customers</p>
                        <p class="text-2xl font-bold text-white">1</p>
                    </div>
                    <div class="flex items-center justify-center size-24 rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                </section>
            </div>

            <div class="card !bg-sky-700/86">
                <section class="flex justify-between items-center">
                    <div>
                        <p class="mb-2 text-md font-medium text-white">Total Services</p>
                        <p class="text-2xl font-bold text-white"><?php echo $total_services; ?></p>
                    </div>
                    <div class="flex items-center justify-center size-24 rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                            <path d="M12 11h4" />
                            <path d="M12 16h4" />
                            <path d="M8 11h.01" />
                            <path d="M8 16h.01" />
                        </svg>
                    </div>
                </section>
            </div>

            <div class="card !bg-sky-700/86">
                <section class="flex justify-between items-center">
                    <div>
                        <p class="mb-2 text-md font-medium text-white">Total Veterinarians</p>
                        <p class="text-2xl font-bold text-white">0</p>
                    </div>
                    <div class="flex items-center justify-center size-24 rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-user-star-icon lucide-user-star">
                            <path
                                d="M16.051 12.616a1 1 0 0 1 1.909.024l.737 1.452a1 1 0 0 0 .737.535l1.634.256a1 1 0 0 1 .588 1.806l-1.172 1.168a1 1 0 0 0-.282.866l.259 1.613a1 1 0 0 1-1.541 1.134l-1.465-.75a1 1 0 0 0-.912 0l-1.465.75a1 1 0 0 1-1.539-1.133l.258-1.613a1 1 0 0 0-.282-.866l-1.156-1.153a1 1 0 0 1 .572-1.822l1.633-.256a1 1 0 0 0 .737-.535z" />
                            <path d="M8 15H7a4 4 0 0 0-4 4v2" />
                            <circle cx="10" cy="7" r="4" />
                        </svg>
                    </div>
                </section>
            </div>
        </div>

        <div class="card">
            <section>
                <h2 class="text-lg font-medium mb-4">Total Completed Services by Date</h2>
                <canvas id="servicesChart" width="400" height="200"></canvas>
            </section>
        </div>
    </main>

    <script>
    const ctx = document.getElementById('servicesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $labels; ?>,
            datasets: <?php echo $datasets_json; ?>
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Total Completed Services by Date'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Services'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
    </script>
</body>

</html>