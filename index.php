<?php
require_once 'db/db_connect.php';

// Fetch services
$services = [];
$sql = "SELECT id, name, description FROM services";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Fetch veterinarians
$stmt = $conn->prepare("SELECT name, specialization, image FROM veterinarians ORDER BY name ASC");
$stmt->execute();
$vet_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/output.css">

    <title>MVO - Compassionate Care</title>

    <!-- ✅ Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>


    <!-- ✅ Tailwind Dark Mode Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#2563eb',
                        secondary: '#1e40af',
                        accent: '#60a5fa',
                    },
                },
            },
        };
    </script>

    <style>
        .text-shadow {
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-500">

    <!-- HEADER -->
    <header
        class="bg-gradient-to-r from-blue-700 to-blue-500 text-white dark:from-gray-900 dark:to-gray-800 shadow-lg sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <img src="img/logo2.jpg" alt="MVO Logo" class="w-12 h-12 rounded-full shadow-md">
                <span class="text-xl font-bold tracking-wide">MVO - </span>
                <span class="text-xl font-bold tracking-wide font-serif italic text-shadow">Compassionate Care</span>
            </div>

            <nav>
                <ul class="flex gap-6 text-sm font-medium items-center">
                    <!-- Floating Hover Links -->
                    <li>
                        <a href="#"
                            class="hover:text-yellow-300 dark:hover:text-yellow-400 transform hover:-translate-y-1 hover:scale-110 hover:shadow-[0_8px_20px_rgba(255,255,255,0.4)] dark:hover:shadow-[0_8px_20px_rgba(255,255,0,0.4)] transition-all duration-300 ease-in-out px-3 py-2 rounded-md">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="#services"
                            class="hover:text-yellow-300 dark:hover:text-yellow-400 transform hover:-translate-y-1 hover:scale-110 hover:shadow-[0_8px_20px_rgba(255,255,255,0.4)] dark:hover:shadow-[0_8px_20px_rgba(255,255,0,0.4)] transition-all duration-300 ease-in-out px-3 py-2 rounded-md">
                            Services
                        </a>
                    </li>
                    <li>
                        <a href="#veterinarians"
                            class="hover:text-yellow-300 dark:hover:text-yellow-400 transform hover:-translate-y-1 hover:scale-110 hover:shadow-[0_8px_20px_rgba(255,255,255,0.4)] dark:hover:shadow-[0_8px_20px_rgba(255,255,0,0.4)] transition-all duration-300 ease-in-out px-3 py-2 rounded-md">
                            Veterinarians
                        </a>
                    </li>
                    <li>
                        <a href="#contact"
                            class="hover:text-yellow-300 dark:hover:text-yellow-400 transform hover:-translate-y-1 hover:scale-110 hover:shadow-[0_8px_20px_rgba(255,255,255,0.4)] dark:hover:shadow-[0_8px_20px_rgba(255,255,0,0.4)] transition-all duration-300 ease-in-out px-3 py-2 rounded-md">
                            Contact
                        </a>
                    </li>

                    <!-- Login Button -->
                    <li>
                        <a href="login.php"
                            class="bg-yellow-300 text-black font-semibold px-4 py-1 rounded-lg hover:bg-sky-500 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-500 transition shadow transform hover:scale-105 hover:-translate-y-0.5 hover:shadow-[0_6px_12px_rgba(0,0,0,0.25)] duration-300 ease-in-out">
                            Login
                        </a>
                    </li>

                    <!-- Theme Toggle -->
                    <li>
                        <button id="theme-toggle"
                            class="flex items-center gap-2 px-4 py-2 bg-blue-600 dark:bg-black-300 text-white dark:text-black rounded-lg shadow hover:bg-blue-700 dark:hover:bg-yellow-400 transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5 hover:shadow-[0_6px_12px_rgba(0,0,0,0.25)]">
                            <span id="theme-label">🌙 Dark Mode</span>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- 🌗 THEME SCRIPT -->
    <script>
        const html = document.documentElement;
        const toggleBtn = document.getElementById("theme-toggle");
        const label = document.getElementById("theme-label");

        function updateLabel() {
            label.textContent = html.classList.contains("dark")
                ? "🌞 Light Mode"
                : "🌙 Dark Mode";
        }

        // Apply saved theme
        if (
            localStorage.theme === "dark" ||
            (!("theme" in localStorage) &&
                window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            html.classList.add("dark");
        } else {
            html.classList.remove("dark");
        }

        // Toggle theme
        toggleBtn.addEventListener("click", () => {
            const isDark = html.classList.toggle("dark");
            localStorage.theme = isDark ? "dark" : "light";
            updateLabel();
        });

        updateLabel();
    </script>

    <!-- HERO SECTION -->
    <section class="relative bg-cover bg-center text-white text-center py-32 px-6"
        style="background-image: url('img/img1.png'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 max-w-3xl mx-auto">
            <h1
                class="text-5xl md:text-6xl font-extrabold mb-6 drop-shadow-lg leading-tight text-sky-300 font-serif italic">
                Compassionate Care for Animals & Community
            </h1>
            <p class="text-lg md:text-xl mb-8 opacity-90 leading-relaxed">
                Dedicated to promoting animal health, welfare, and public service excellence.
            </p>
            <a href="#services"
                class="bg-accent text-black font-semibold px-8 py-3 rounded-full hover:bg-blue-500 transition shadow-md transform hover:scale-110 hover:-translate-y-1 duration-300 ease-in-out">
                Explore Services
            </a>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="services" class="py-20 bg-white dark:bg-gray-800 transition">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <strong class="block text-sm md:text-base mb-10 text-blue-500">
                📞 Call 602-955-5757 | ✉ Email municipalveterinaryoffice27@gmail.com
                <br>📍 CONSULTATION & CHECK-UP @ MVO CLINIC ONLY 9AM - 4PM MON-FRI
            </strong>

            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $service): ?>
                        <div
                            class="bg-gradient-to-b from-white to-blue-50 dark:from-gray-700 dark:to-gray-600 border border-blue-100 dark:border-gray-500 rounded-2xl shadow-md hover:shadow-2xl transform hover:-translate-y-2 hover:scale-105 transition-all duration-300 p-8">
                            <div class="text-primary text-5xl mb-4">🐾</div>
                            <h3 class="text-xl font-semibold text-blue-800 dark:text-blue-200 mb-3">
                                <?= htmlspecialchars($service['name']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                                <?= htmlspecialchars($service['description']) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 col-span-full">No services available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- VETERINARIANS -->
    <section id="veterinarians" class="py-20 bg-gray-50 dark:bg-gray-900 transition">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h2 class="text-4xl font-bold text-primary mb-12 font-serif italic">
                Meet Our Veterinary Team
            </h2>
            <div class="flex flex-wrap justify-center gap-10">
                <?php if ($vet_result && $vet_result->num_rows > 0): ?>
                    <?php while ($row = $vet_result->fetch_assoc()): ?>
                        <?php
                        $fileName = basename($row['image']);
                        $webImagePath = "uploads/$fileName";
                        $serverImagePath = __DIR__ . "/uploads/$fileName";
                        $imageSrc = (file_exists($serverImagePath) && is_readable($serverImagePath) && @getimagesize($serverImagePath))
                            ? $webImagePath
                            : 'img/placeholder.png';
                        ?>
                        <div
                            class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 hover:scale-105 transition-all duration-300 w-64 group">
                            <div class="overflow-hidden rounded-xl mb-4">
                                <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Veterinarian"
                                    class="w-full h-56 object-cover rounded-xl transform group-hover:scale-105 transition duration-300">
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                <?= htmlspecialchars($row['name']) ?>
                            </h3>
                            <p class="text-primary text-sm font-medium"><?= htmlspecialchars($row['specialization']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-500">No veterinary information available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact"
        class="py-20 bg-gradient-to-br from-blue-50 to-white dark:from-gray-800 dark:to-gray-900 transition">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-4xl font-bold text-primary text-center mb-12 font-serif italic">Contact Us</h2>
            <div class="grid md:grid-cols-2 gap-10">
                <form method="POST" class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg flex flex-col gap-4">
                    <input type="text" name="name" placeholder="Your Name" required
                        class="border border-gray-300 dark:border-gray-600 bg-transparent rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                    <input type="email" name="email" placeholder="Your Email" required
                        class="border border-gray-300 dark:border-gray-600 bg-transparent rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                    <textarea name="message" rows="5" placeholder="Your Message" required
                        class="border border-gray-300 dark:border-gray-600 bg-transparent rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    <button type="submit"
                        class="bg-primary hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition shadow-md transform hover:scale-105 hover:-translate-y-1 duration-300 ease-in-out">
                        Send Message
                    </button>
                </form>

                <iframe src="https://www.google.com/maps?q=Carranglan,+Nueva+Ecija,+Philippines&output=embed"
                    class="rounded-2xl shadow-lg w-full h-[400px] border-0" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gradient-to-r from-secondary to-primary text-white text-center py-6 text-sm">
        &copy; <?= date('Y') ?> MVO - Compassionate Care. All rights reserved.
    </footer>

</body>

</html>