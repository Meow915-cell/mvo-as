document.addEventListener("DOMContentLoaded", function() {
    const tbody = document.querySelector("#usersTable tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const rowsPerPageSelect = document.getElementById("rowsPerPage");
    const searchInput = document.getElementById("searchInput");
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const pageNumbersContainer = document.getElementById("pageNumbersContainer");

    let currentPage = 1;
    let rowsPerPage = parseInt(rowsPerPageSelect.value);

    function filterRows() {
        const term = searchInput.value.toLowerCase();
        return rows.filter(row => row.textContent.toLowerCase().includes(term));
    }

    function renderTable() {
        const filteredRows = filterRows();
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        // Hide all rows first
        rows.forEach(r => r.style.display = "none");

        // Show only current page rows
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filteredRows.slice(start, end).forEach(r => r.style.display = "");

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        pageNumbersContainer.innerHTML = "";

        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (currentPage <= 3) {
            startPage = 1;
            endPage = Math.min(maxPagesToShow, totalPages);
        } else if (currentPage > totalPages - 2) {
            startPage = Math.max(1, totalPages - maxPagesToShow + 1);
            endPage = totalPages;
        }

        // First page + dots
        if (startPage > 1) {
            pageNumbersContainer.appendChild(createPageButton(1));
            if (startPage > 2) pageNumbersContainer.appendChild(createDots());
        }

        // Middle pages
        for (let i = startPage; i <= endPage; i++) {
            pageNumbersContainer.appendChild(createPageButton(i));
        }

        // Last page + dots
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) pageNumbersContainer.appendChild(createDots());
            pageNumbersContainer.appendChild(createPageButton(totalPages));
        }

        // Enable/disable Prev/Next
        prevBtn.classList.toggle("opacity-50 cursor-not-allowed", currentPage === 1);
        nextBtn.classList.toggle("opacity-50 cursor-not-allowed", currentPage === totalPages);
    }

    function createPageButton(pageNum) {
        const li = document.createElement("li");
        const a = document.createElement("a");
        a.href = "#";
        a.textContent = pageNum;
        a.className = pageNum === currentPage ? "btn-icon-outline" : "btn-icon-ghost";
        a.addEventListener("click", (e) => {
            e.preventDefault();
            currentPage = pageNum;
            renderTable();
        });
        li.appendChild(a);
        return li;
    }

    function createDots() {
        const li = document.createElement("li");
        li.innerHTML = `<div class="size-9 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0"><circle cx="12" cy="12" r="1" /><circle cx="19" cy="12" r="1" /><circle cx="5" cy="12" r="1" /></svg></div>`;
        return li;
    }

    // Prev/Next buttons
    prevBtn.addEventListener("click", e => {
        e.preventDefault();
        if (currentPage > 1) currentPage--;
        renderTable();
    });

    nextBtn.addEventListener("click", e => {
        e.preventDefault();
        const filteredRows = filterRows();
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) currentPage++;
        renderTable();
    });

    // Rows per page and search
    rowsPerPageSelect.addEventListener("change", () => {
        rowsPerPage = parseInt(rowsPerPageSelect.value);
        currentPage = 1;
        renderTable();
    });

    searchInput.addEventListener("input", () => {
        currentPage = 1;
        renderTable();
    });

    renderTable();
});