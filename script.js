document.addEventListener("DOMContentLoaded", () => {
    const calendarGrid = document.getElementById("calendarGrid");
    const equipSelect = document.getElementById("equipSelect");
    const currentMonthYearEl = document.getElementById("currentMonthYear");
    const prevMonthBtn = document.getElementById("prevMonthBtn");
    const nextMonthBtn = document.getElementById("nextMonthBtn");

    if (!calendarGrid || !equipSelect) return;

    const pickupDateInput = document.getElementById("pickupDate");
    const returnDateInput = document.getElementById("returnDate");

    const sideCamTitle = document.getElementById("sideCamTitle");
    const sideCamImg = document.getElementById("sideCamImg");
    const tierCamName = document.getElementById("tierCamName");
    const tierBreakdown = document.getElementById("tierBreakdown");
    const sideKitInclusions = document.getElementById("sideKitInclusions");
    
    const sidePickup = document.getElementById("sidePickup");
    const sideReturn = document.getElementById("sideReturn");
    const sideDuration = document.getElementById("sideDuration");
    const sideRateApplied = document.getElementById("sideRateApplied");
    const sideSubtotal = document.getElementById("sideSubtotal");
    const sideBaseTotal = document.getElementById("sideBaseTotal");
    const sideDP = document.getElementById("sideDP");
    const sideBalance = document.getElementById("sideBalance");
    const sideAddons = document.getElementById("sideAddons");
    const addonCheckboxes = document.querySelectorAll(".addon-chk");

    let currentMonth = 10; 
    let currentYear = 2026;

    let startDate = null; 
    let endDate = null;   

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const unavailableDays = [];

    function renderCalendar() {
        if (!calendarGrid || !currentMonthYearEl) return;
        calendarGrid.innerHTML = "";
        currentMonthYearEl.textContent = `${monthNames[currentMonth]} ${currentYear}`;

        const totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();
        const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();

        for (let i = 0; i < firstDayIndex; i++) {
            const emptyEl = document.createElement("div");
            emptyEl.classList.add("cal-day", "other-month");
            calendarGrid.appendChild(emptyEl);
        }

        for (let day = 1; day <= totalDays; day++) {
            const dayEl = document.createElement("div");
            dayEl.classList.add("cal-day");
            dayEl.setAttribute("data-day", day);

            const isUnavail = unavailableDays.includes(day);
            const dotClass = isUnavail ? "unavail" : "avail";

            dayEl.innerHTML = `${day}<span class="dot ${dotClass}"></span>`;

            if (isUnavail) {
                dayEl.classList.add("disabled");
            } else {
                let isSelected = false;
                if (startDate && endDate) {
                    const dCurr = new Date(currentYear, currentMonth, day);
                    const dStart = new Date(startDate.year, startDate.month, startDate.day);
                    const dEnd = new Date(endDate.year, endDate.month, endDate.day);
                    if (dCurr >= dStart && dCurr <= dEnd) isSelected = true;
                } else if (startDate && !endDate) {
                    if (currentYear === startDate.year && currentMonth === startDate.month && day === startDate.day) {
                        isSelected = true;
                    }
                }

                if (isSelected) {
                    dayEl.classList.add("selected");
                }

                dayEl.addEventListener("click", () => {
                    const clicked = { day, month: currentMonth, year: currentYear };

                    
                    if (!startDate || (startDate && endDate)) {
                        startDate = clicked;
                        endDate = null; 
                    } else {
                        const dClicked = new Date(clicked.year, clicked.month, clicked.day);
                        const dStart = new Date(startDate.year, startDate.month, startDate.day);

                        if (dClicked >= dStart) {
                            endDate = clicked; 
                        } else {
                            startDate = clicked; 
                            endDate = null;
                        }
                    }
                    renderCalendar();
                    updateSummary();
                });
            }

            calendarGrid.appendChild(dayEl);
        }
    }

    if (prevMonthBtn) {
        prevMonthBtn.addEventListener("click", () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });
    }

    if (nextMonthBtn) {
        nextMonthBtn.addEventListener("click", () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });
    }

    function updateSummary() {
        const selectedOpt = equipSelect.options[equipSelect.selectedIndex];
        if (!selectedOpt) return;

        const camName = selectedOpt.value;
        const imgUrl = selectedOpt.getAttribute("data-img");
        const kitText = selectedOpt.getAttribute("data-kit");

        const t1 = parseInt(selectedOpt.getAttribute("data-t1")) || 549;
        const t2Attr = selectedOpt.getAttribute("data-t2");
        const t3Attr = selectedOpt.getAttribute("data-t3");

        const t2 = t2Attr ? parseInt(t2Attr) : t1;
        const t3 = t3Attr ? parseInt(t3Attr) : t2;

        if (sideCamTitle) sideCamTitle.textContent = camName;
        if (tierCamName) tierCamName.textContent = camName;
        if (sideCamImg && imgUrl) sideCamImg.src = imgUrl;
        if (sideKitInclusions) sideKitInclusions.textContent = kitText;

        if (tierBreakdown) {
            if (t3Attr && t2Attr) {
                tierBreakdown.textContent = `₱${t1}/day (1–3 days) · ₱${t2}/day (1–6 days) · ₱${t3}/day (1–7 days or more)`;
            } else if (t2Attr) {
                tierBreakdown.textContent = `₱${t1}/day (1–3 days) · ₱${t2}/day (4 days or more)`;
            } else {
                tierBreakdown.textContent = `₱${t1}/day flat rate`;
            }
        }

        let daysCount = 0;
        if (startDate && endDate) {
            if (pickupDateInput) pickupDateInput.value = `${monthNames[startDate.month].slice(0,3)} ${startDate.day}, ${startDate.year}`;
            if (returnDateInput) returnDateInput.value = `${monthNames[endDate.month].slice(0,3)} ${endDate.day}, ${endDate.year}`;
            
            const d1 = new Date(startDate.year, startDate.month, startDate.day);
            const d2 = new Date(endDate.year, endDate.month, endDate.day);
            daysCount = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
        } else if (startDate && !endDate) {
            if (pickupDateInput) pickupDateInput.value = `${monthNames[startDate.month].slice(0,3)} ${startDate.day}, ${startDate.year}`;
            if (returnDateInput) returnDateInput.value = ""; 
            daysCount = 0; 
        } else {
            if (pickupDateInput) pickupDateInput.value = "";
            if (returnDateInput) returnDateInput.value = "";
            daysCount = 0;
        }

        if (sidePickup) sidePickup.textContent = startDate ? (pickupDateInput ? pickupDateInput.value : "") : "Not selected";
        
        if (sideReturn) sideReturn.textContent = endDate ? (returnDateInput ? returnDateInput.value : "") : "Not selected";
        
        if (sideDuration) sideDuration.textContent = daysCount > 0 ? `${daysCount} billable day${daysCount > 1 ? 's' : ''}` : "0 days";

        let rate = t1;
        if (t3Attr && daysCount >= 7) {
            rate = t3;
        } else if (t2Attr && daysCount >= 4) {
            rate = t2;
        }

        const total = daysCount * rate;
        const half = total / 2;

        if (sideRateApplied) sideRateApplied.textContent = daysCount > 0 ? `₱${rate.toLocaleString()}/day × ${daysCount} days` : "₱0";
        if (sideSubtotal) sideSubtotal.textContent = `₱${total.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        if (sideBaseTotal) sideBaseTotal.textContent = `₱${total.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        if (sideDP) sideDP.textContent = `₱${half.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
        if (sideBalance) sideBalance.textContent = `₱${half.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

        const selectedAddons = [];
        addonCheckboxes.forEach(chk => {
            if (chk.checked) selectedAddons.push(chk.value);
        });

        if (sideAddons) {
            sideAddons.textContent = selectedAddons.length > 0 ? selectedAddons.join(", ") : "None selected";
        }
    }

    equipSelect.addEventListener("change", updateSummary);
    addonCheckboxes.forEach(chk => chk.addEventListener("change", updateSummary));

    renderCalendar();
    updateSummary();
});

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const tags = document.querySelectorAll(".tag");
    const cards = document.querySelectorAll(".card");

    searchInput.addEventListener("input", (e) => {
        const query = e.target.value.toLowerCase();
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(query) ? "flex" : "none";
        });
    });


    tags.forEach(tag => {
        tag.addEventListener("click", () => {
            tags.forEach(t => t.classList.remove("active"));
            tag.classList.add("active");

            const filter = tag.getAttribute("data-filter");
            cards.forEach(card => {
                if (filter === "all" || card.getAttribute("data-brand") === filter) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        });
    });
});