<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
}

aside nav a,
aside nav summary {
    user-select: none;
}
</style>

<aside class="sidebar" data-side="left" aria-hidden="false">
    <nav aria-label="Sidebar navigation">
        <section class="scrollbar">
            <div class="rounded-sm m-2 flex gap-2 items-center" style="width: calc(100% - 1rem)">
                <img class="size-12 object-cover rounded-full" alt="logo" src="../../logo.png" />
                <h2 class="font-semibold text-sm">Municipality Vetrinary Office</h2>
                <p>

            </div>
            <div role="group" aria-labelledby="group-label-content-1" class="mt-0">
                <h3 id="group-label-content-1">Main</h3>
                <ul>
                    <li>
                        <a href="../dashboard">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <details id="submenu-content-1-3" href="appointments" <?php if(basename(dirname($_SERVER['PHP_SELF'])) == 'appointments'){ echo 'open'; } ?>>
                            <summary aria-controls="submenu-content-1-3-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-calendar1-icon lucide-calendar-1">
                                    <path d="M11 14h1v4" />
                                    <path d="M16 2v4" />
                                    <path d="M3 10h18" />
                                    <path d="M8 2v4" />
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                </svg>
                                Appointments

                            </summary>
                            <ul id="submenu-content-1-3-content">
                                <li>
                                    <a href="../appointments">
                                        <span>All</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="appointments">
                                        <span>Pending</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="appointments">
                                        <span>Accepted</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="appointments">
                                        <span>Rejected</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="appointments">
                                        <span>Complete</span>
                                    </a>
                                </li>
                            </ul>
                        </details>

                    </li>
                    <h3 id="group-label-content-1">Management</h3>
                    <li>
                        <a href="../services">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-briefcase-medical-icon lucide-briefcase-medical">
                                <path d="M12 11v4" />
                                <path d="M14 13h-4" />
                                <path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                <path d="M18 6v14" />
                                <path d="M6 6v14" />
                                <rect width="20" height="14" x="2" y="6" rx="2" />
                            </svg>
                            <span>Services</span>
                        </a>
                    </li>
                    <li>
                        <a href="../customers">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-user-icon lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li>
                        <a href="../veterinarians">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-user-star-icon lucide-user-star">
                                <path
                                    d="M16.051 12.616a1 1 0 0 1 1.909.024l.737 1.452a1 1 0 0 0 .737.535l1.634.256a1 1 0 0 1 .588 1.806l-1.172 1.168a1 1 0 0 0-.282.866l.259 1.613a1 1 0 0 1-1.541 1.134l-1.465-.75a1 1 0 0 0-.912 0l-1.465.75a1 1 0 0 1-1.539-1.133l.258-1.613a1 1 0 0 0-.282-.866l-1.156-1.153a1 1 0 0 1 .572-1.822l1.633-.256a1 1 0 0 0 .737-.535z" />
                                <path d="M8 15H7a4 4 0 0 0-4 4v2" />
                                <circle cx="10" cy="7" r="4" />
                            </svg>
                            <span>Veterinarians</span>
                        </a>
                    </li>
                    <li>
                        <a href="../schedules">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-calendar-days-icon lucide-calendar-days">
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
                            </svg>
                            <span>Schedules</span>
                        </a>
                    </li>
                    <h3 id="group-label-content-1">Others</h3>
                    <li>
                        <a href="../users">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-user-pen-icon lucide-user-pen">
                                <path d="M11.5 15H7a4 4 0 0 0-4 4v2" />
                                <path
                                    d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
                                <circle cx="10" cy="7" r="4" />
                            </svg>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="../settings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                                <path
                                    d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </section>
    </nav>
</aside>