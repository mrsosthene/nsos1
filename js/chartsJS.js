const ctx1 = document.getElementById("chart-1").getContext("2d");
const myChart = new Chart(ctx1, {
  type: "polarArea",
  data: {
    labels: ["Inscrits", "Connectés"],
    datasets: [
      {
        label: "Nombre d'utilisateurs",
        data: [totalInscrits, totalConnectes],
        backgroundColor: [
          "rgba(75, 192, 192, 1)",
          "rgba(54, 162, 235, 1)"
        ],
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Répartition des utilisateurs'
      }
    }
  },
});

const ctx2 = document.getElementById("chart-2").getContext("2d");
const myChart2 = new Chart(ctx2, {
  type: "bar",
  data: {
    labels: ["Inscrits", "Connectés"],
    datasets: [
      {
        label: "Nombre d'utilisateurs",
        data: [totalInscrits, totalConnectes],
        backgroundColor: [
          "rgba(75, 192, 192, 1)",
          "rgba(54, 162, 235, 1)"
        ],
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: false
      },
      title: {
        display: true,
        text: 'Comparaison Inscrits/Connectés'
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  },
});
