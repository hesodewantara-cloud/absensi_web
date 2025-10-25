// script.js
document.addEventListener("DOMContentLoaded", () => {
  const gridWrap = document.getElementById("gridWrap");
  const statusEl = document.getElementById("status");
  const dateInput = document.getElementById("date");
  const refreshBtn = document.getElementById("refreshBtn");
  const themeToggle = document.getElementById("themeToggle");
  const autoRefreshInterval = document.getElementById("autoRefreshInterval");

  const today = new Date().toISOString().split("T")[0];
  dateInput.value = today;

  async function loadData() {
    const date = dateInput.value;
    statusEl.textContent = "Memuat data...";
    gridWrap.innerHTML = "";

    try {
      const response = await fetch(`get_data.php?date=${date}`);
      
      // Periksa jika respons BUKAN 'ok' (misal: error 500)
      if (!response.ok) {
        // Coba baca respons error sebagai teks
        const errorText = await response.text();
        throw new Error(`Gagal ambil data. Status: ${response.status}. Pesan: ${errorText}`);
      }

      // Cek header untuk memastikan ini JSON sebelum parsing
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        // Jika ini HTML (karena redirect login), response.text() akan menampilkannya
        const htmlResponse = await response.text();
        console.error("Menerima HTML, seharusnya JSON:", htmlResponse);
        // Cek jika errornya adalah 'Akses ditolak' dari PHP kita
        if (htmlResponse.includes("Akses ditolak")) {
             throw new Error("Akses ditolak. Sesi Anda mungkin habis.");
        }
        throw new Error("Sesi login Anda mungkin habis. Silakan login kembali.");
      }

      const data = await response.json();

      // Cek jika data JSON adalah error yg kita kirim (dari get_data.php)
      if (data.error) {
        throw new Error(data.error);
      }

      if (!data.rooms || data.rooms.length === 0) {
        statusEl.textContent = "Tidak ada data absensi untuk tanggal ini.";
        return;
      }

      statusEl.textContent = `Tanggal: ${data.date} — Total Data: ${data.raw_count}`;

      // --- Perbaikan Nama Class CSS ---
      const table = document.createElement("table");
      table.className = "attendance-grid"; // SESUAIKAN DENGAN style.css

      const thead = document.createElement("thead");
      const headerRow = document.createElement("tr");
      const thRoom = document.createElement("th"); // Header kolom Ruang
      thRoom.textContent = "Ruang";
      thRoom.className = "room-col"; // SESUAIKAN DENGAN style.css
      headerRow.appendChild(thRoom);

      data.timeSlots.forEach((slot) => {
        const th = document.createElement("th");
        th.textContent = slot.slice(0, 5); // 07:00
        headerRow.appendChild(th);
      });
      thead.appendChild(headerRow);
      table.appendChild(thead);

      const tbody = document.createElement("tbody");

      // Urutkan ruangan berdasarkan nama
      data.rooms.sort();

      data.rooms.forEach((room) => {
        const row = document.createElement("tr");
        const roomCell = document.createElement("td");
        roomCell.textContent = room;
        roomCell.className = "room-col"; // SESUAIKAN DENGAN style.css
        row.appendChild(roomCell);

        data.timeSlots.forEach((slot) => {
          const td = document.createElement("td");
          const slotData = data.grid[room] ? (data.grid[room][slot] || []) : [];

          if (slotData.length > 0) {
            slotData.forEach((entry) => {
              const div = document.createElement("div");
              div.className = "attendee"; // SESUAIKAN DENGAN style.css
              div.innerHTML = `
                <span class="name">${entry.name || "Tanpa Nama"}</span>
                <span class="email">${entry.email || ""}</span>
              `;
              // Tambahkan timestamp jika perlu
              // <span class="time">${new Date(entry.timestamp).toLocaleTimeString()}</span>
              td.appendChild(div);
            });
          } else {
            td.classList.add("cell-empty"); // SESUAIKAN DENGAN style.css
            td.textContent = "–";
          }
          row.appendChild(td);
        });
        tbody.appendChild(row);
      });

      table.appendChild(tbody);
      gridWrap.appendChild(table);

    } catch (err) {
      console.error(err);
      statusEl.textContent = "Terjadi kesalahan: " + err.message;
      
      // --- PERBAIKAN: Hapus redirect otomatis ---
      // Baris di bawah ini menyebabkan infinite loop
      // if (err.message.includes("login") || err.message.includes("Akses ditolak")) {
      //   window.location.href = 'login.php';
      // }
      // --- Akhir Perbaikan ---
    }
  }

  refreshBtn.addEventListener("click", loadData);
  dateInput.addEventListener("change", loadData);

  themeToggle.addEventListener("click", () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    if (isDark) {
      document.documentElement.removeAttribute('data-theme');
    } else {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  });

  // Cek preferensi tema sistem
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-theme', 'dark');
  }

  let seconds = 60;
  setInterval(() => {
    seconds--;
    if (seconds <= 0) {
      loadData();
      seconds = 60;
    }
    autoRefreshInterval.textContent = seconds;
  }, 1000);

  loadData();
});