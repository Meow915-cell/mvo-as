<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../../src/output.css" rel="stylesheet" />
    <link
rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/basecoat.cdn.min.css"
    />
    <script
      src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/all.min.js"
      defer
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/basecoat.min.js"
      defer
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/basecoat-css@0.3.2/dist/js/sidebar.min.js"
      defer
    ></script>
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
      <!-- <header>
        <button
          type="button"
          onclick="document.dispatchEvent(new CustomEvent('basecoat:sidebar'))"
        >
          Toggle sidebar
        </button>
      </header> -->

      <ol class="mb-4 text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5">
                <li class="inline-flex items-center gap-1.5 hover:cursor-pointer">
                    <a class="text-lg font-medium hover:text-foreground transition-colors">Dashboard</a>
                </li>
            </ol>
      <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <div class="card">
          <section class="flex justify-between items-center">
            <div>
              <p class="mb-2 text-sm font-medium text-gray-600">
                Pending Appointments
              </p>
              <p class="text-2xl font-bold">0</p>
            </div>
            <div
              class="flex items-center justify-center size-12 rounded-full bg-orange-100 text-orange-500"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
          </section>
        </div>

        <div class="card">
          <section class="flex justify-between items-center">
            <div>
              <p class="mb-2 text-sm font-medium text-gray-600">
                Total Customers
              </p>
              <p class="text-2xl font-bold">1</p>
            </div>
            <div
              class="flex items-center justify-center size-12 rounded-full bg-green-100 text-green-500"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>
          </section>
        </div>

        <div class="card">
          <section class="flex justify-between items-center">
            <div>
              <p class="mb-2 text-sm font-medium text-gray-600">
                Total Services
              </p>
              <p class="text-2xl font-bold">0</p>
            </div>
            <div
              class="flex items-center justify-center size-12 rounded-full bg-blue-100 text-blue-500"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                <path
                  d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"
                />
                <path d="M12 11h4" />
                <path d="M12 16h4" />
                <path d="M8 11h.01" />
                <path d="M8 16h.01" />
              </svg>
            </div>
          </section>
        </div>

        <div class="card">
          <section class="flex justify-between items-center">
            <div>
              <p class="mb-2 text-sm font-medium text-gray-600">
                Total Veterinarians
              </p>
              <p class="text-2xl font-bold">0</p>
            </div>
            <div
              class="flex items-center justify-center size-12 rounded-full bg-indigo-100 text-indigo-500"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <line x1="19" x2="19" y1="8" y2="14" />
                <line x1="22" x2="16" y1="11" y2="11" />
              </svg>
            </div>
          </section>
        </div>
      </div>
    </main>
  </body>
</html>
