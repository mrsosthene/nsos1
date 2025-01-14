const ctx1 = document.getElementById("chart-1").getContext("2d");
const myChart = new Chart(ctx1, {
type: "polarArea",
data: {
labels: ["Licence", "Master", "Ingenieur"],
datasets: [
{
label: "# of Votes",
data: [$total_licence , $total_master, $total_ingenieur],
backgroundColor: [
"rgba(54, 162, 235, 1)",
"rgba(255, 99, 132, 1)",
"rgba(255, 206, 86, 1)",
],
},
],
},
options: {
responsive: true,
},
});

const ctx2 = document.getElementById("chart-2").getContext("2d");
const myChart2 = new Chart(ctx2, {
type: "bar",
data: {
labels: ["Licence", "Master", "Ingenieur"],
datasets: [
{
label: "",
data: [$total_licence , $total_master, $total_ingenieur],
backgroundColor: [
"rgba(54, 162, 235, 1)",
"rgba(255, 99, 132, 1)",
"rgba(255, 206, 86, 1)",
],
},
],
},
options: {
responsive: true,
},
});