/* ─────────────────────────────────────────
   1. SIDEBAR TOGGLE
───────────────────────────────────────── */
function toggleDesktop() {
  const sb = document.getElementById("sidebar");
  const main = document.getElementById("main");
  const icon = document.getElementById("toggleIcon");

  sb.classList.toggle("collapsed");
  main.classList.toggle("sb-collapsed");
  icon.className = sb.classList.contains("collapsed")
    ? "bi bi-chevron-right"
    : "bi bi-chevron-left";
}

function openMobile() {
  document.getElementById("sidebar").classList.add("mob-open");
  document.getElementById("overlay").classList.add("show");
}

function closeMobile() {
  document.getElementById("sidebar").classList.remove("mob-open");
  document.getElementById("overlay").classList.remove("show");
}

document.querySelectorAll(".sb-link").forEach((link) => {
  link.addEventListener("click", function () {
    document
      .querySelectorAll(".sb-link")
      .forEach((l) => l.classList.remove("active"));
    this.classList.add("active");
    if (window.innerWidth < 992) closeMobile();
  });
});

/* ─────────────────────────────────────────
   2. LIVE CLOCK & DATE
───────────────────────────────────────── */
function updateClock() {
  const now = new Date();
  const dateStr = now.toLocaleDateString("en-IN", {
    weekday: "short",
    day: "numeric",
    month: "short",
    year: "numeric",
  });

  document.getElementById("todayDate").textContent = dateStr;
  document.getElementById("timeDate").textContent = "Time Today · " + dateStr;

  let h = now.getHours();
  let m = now.getMinutes();
  let s = now.getSeconds();
  const ampm = h >= 12 ? "PM" : "AM";
  h = h % 12 || 12;

  document.getElementById("liveClock").textContent =
    h + ":" + (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
  document.getElementById("liveAmpm").textContent = " " + ampm;
}

updateClock();
setInterval(updateClock, 1000);

/* ─────────────────────────────────────────
   3. CHART DEFAULTS
───────────────────────────────────────── */
Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
Chart.defaults.color = "#6b7a9e";

const BLUE = "#3b5bdb";
const PINK = "#f06595";
const YELLOW = "#ffd43b";
const ORANGE = "#f97316";

/* ─────────────────────────────────────────
   4. ATTENDANCE BAR CHART
───────────────────────────────────────── */
const barCanvas = document.getElementById("attendanceBarChart");

// Read dynamic data from canvas data attributes, fallback to array of 0s if missing
let presentData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
let absentData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

if (barCanvas) {
  try {
    presentData = JSON.parse(barCanvas.dataset.present);
    absentData = JSON.parse(barCanvas.dataset.absent);
  } catch (e) {
    console.error("Error parsing bar chart data", e);
  }
}

new Chart(barCanvas.getContext("2d"), {
  type: "bar",
  data: {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    datasets: [
      {
        label: "Present",
        data: presentData,
        backgroundColor: "#6366f1", // Indigo for Present
        borderRadius: 5,
        barThickness: 18,
      },
      {
        label: "Absent",
        data: absentData,
        backgroundColor: "#f87171", // Red for Absent
        borderRadius: 5,
        barThickness: 18,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: "#1e293b",
        padding: 12,
        callbacks: {
          label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y} days`,
        },
      },
    },
    scales: {
      y: {
        stacked: true, // Stack the bars
        beginAtZero: true,
        grid: { color: "#f1f5f9", drawBorder: false },
        ticks: {
          color: "#94a3b8",
          font: { size: 11, weight: "600" },
        },
      },
      x: {
        stacked: true, // Stack the bars
        grid: { display: false },
        ticks: { color: "#94a3b8", font: { size: 11, weight: "600" } },
      },
    },
  },
});
/* ─────────────────────────────────────────
   5. LOGIN PIE CHART (Universal Fix)
───────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", function () {
  const pieCanvas = document.getElementById("pieChart");

  if (pieCanvas) {
    const BLUE = '#3b5bdb';
    const PINK = '#f06595';
    const YELLOW = '#ffd43b';

    const onTimeData = parseFloat(pieCanvas.dataset.ontime) || 0;
    const lateData = parseFloat(pieCanvas.dataset.late) || 0;
    const otData = parseFloat(pieCanvas.dataset.ot) || 0;

    // Check if there is data to avoid rendering an invisible chart
    const hasData = (onTimeData + lateData + otData) > 0;

    new Chart(pieCanvas, {
      type: "pie",
      data: {
        labels: ["On Time", "Late", "Over Time"],
        datasets: [{
          data: hasData ? [onTimeData, lateData, otData] : [1, 1, 1],
          backgroundColor: hasData ? [BLUE, PINK, YELLOW] : ['#e5e7eb', '#e5e7eb', '#e5e7eb'],
          borderWidth: 2,
          borderColor: "#fff",
          hoverOffset: 8,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // Important: allows the div height to control the chart
        plugins: {
          legend: { display: false }
        }
      },
    });
  }
});
/* ─────────────────────────────────────────
   6. LEAVE BALANCE DONUT CHART
───────────────────────────────────────── */
new Chart(document.getElementById("lbDonut"), {
  type: "doughnut",
  data: {
    labels: ["Casual", "Sick", "Earned", "Comp Off"],
    datasets: [
      {
        data: [8, 8, 6, 4],
        backgroundColor: ["#3b5bdb", "#ef4444", "#16a34a", "#9333ea"],
        borderWidth: 2,
        borderColor: "#fff",
        hoverOffset: 4,
      },
    ],
  },
  options: {
    responsive: false,
    maintainAspectRatio: false,
    cutout: "72%",
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => ` ${ctx.label}: ${ctx.parsed} days`,
        },
      },
    },
  },
});
