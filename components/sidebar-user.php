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

<aside class="sidebar " data-side="left" aria-hidden="false" >
    <nav aria-label="Sidebar navigation ">
        <section class="scrollbar ">
            <div class="rounded-sm m-2 flex gap-2 items-center" style="width: calc(100% - 1rem)">
                <img class="size-12 object-cover rounded-full" alt="logo" src="../../logo.png" />
                <h2 class="font-semibold text-sm">Municipality Vetrinary Office</h2>
                <p>

            </div>
            <div role="group" aria-labelledby="group-label-content-1" class="mt-0">
                <h3 id="group-label-content-1">Main</h3>
                <ul>
                    <li>
                        <a href="../me">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>My Account</span>
                        </a>
                    </li>

                    
                    <li>
                        <a href="../my-appointments">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-calendar1-icon lucide-calendar-1">
                                    <path d="M11 14h1v4" />
                                    <path d="M16 2v4" />
                                    <path d="M3 10h18" />
                                    <path d="M8 2v4" />
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                </svg>
                            <span>My Appointments</span>
                        </a>
                    </li>
                    <li>
                        <a href="../appointment-history">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history-icon lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                            <span>Appointment History</span>
                        </a>
                    </li>
                    <li>
                        <a href="../pets">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paw-print-icon lucide-paw-print"><circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/></svg>
                            <span>Pets</span>
                        </a>
                    </li>
                    
                </ul>
            </div>
        </section>
    </nav>
</aside>