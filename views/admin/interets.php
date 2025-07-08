<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des intérêts</title>
  <style>
    input, button { margin: 5px; padding: 5px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .filters { margin-bottom: 15px; }
  </style>
  <link rel="stylesheet" href="../layouts/style.css">
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <?php require '../layouts/sidebar.php'; ?>

  <div class="main-content">
    <h1>Intérêts perçus par mois</h1>

    <div class="filters">
      <label for="date_debut">Date début :</label>
      <input type="date" id="date_debut">
      <label for="date_fin">Date fin :</label>
      <input type="date" id="date_fin">
      <button onclick="filtrerInterets()">Filtrer</button>
      <button onclick="resetFiltre()">Réinitialiser</button>
    </div>

    <table id="table-interets">
      <thead>
        <tr>
          <th>Année</th>
          <th>Mois</th>
          <th>Total Intérêts</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <!-- Ajoute ce canvas pour le graphique -->
    <canvas id="chart-interets" width="600" height="250" style="margin-top:40px"></canvas>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../env.js"></script>
    <script>
      function ajax(method, url, data, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, apiBase + url, true);
        if (method === "PUT" || method === "POST") {
          xhr.setRequestHeader("Content-Type", "application/json");
        }
        xhr.onreadystatechange = () => {
          if (xhr.readyState === 4 && xhr.status === 200) {
            callback(JSON.parse(xhr.responseText));
          }
        };
        xhr.send(data);
      }

      function chargerInterets(date_debut = null, date_fin = null) {
        let url = "/interets";
        if (date_debut && date_fin) {
          url += "/" + date_debut + "/" + date_fin;
        }
        ajax("GET", url, null, (data) => {
          const tbody = document.querySelector("#table-interets tbody");
          tbody.innerHTML = "";
          // Préparer les données pour le graphique
          const labels = [];
          const values = [];
          data.forEach(e => {
            const mois = e.mois.toString().padStart(2, '0');
            labels.push(`${e.annee}-${mois}`);
            values.push(parseFloat(e.total_interets));
            tbody.innerHTML += `
              <tr>
                <td>${e.annee}</td>
                <td>${mois}</td>
                <td>${parseFloat(e.total_interets).toFixed(2)}</td>
              </tr>
            `;
          });
          afficherGraphique(labels, values);
        });
      }

      function filtrerInterets() {
        const date_debut = document.getElementById("date_debut").value;
        const date_fin = document.getElementById("date_fin").value;
        if (!date_debut || !date_fin) {
          alert("Veuillez sélectionner une date de début et une date de fin.");
          return;
        }
        chargerInterets(date_debut, date_fin);
      }

      function resetFiltre() {
        document.getElementById("date_debut").value = "";
        document.getElementById("date_fin").value = "";
        chargerInterets();
      }

      let chartInterets = null;
      function afficherGraphique(labels, values) {
        const ctx = document.getElementById('chart-interets').getContext('2d');
        if (chartInterets) chartInterets.destroy();
        chartInterets = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Total Intérêts',
              data: values,
              backgroundColor: 'rgba(54, 162, 235, 0.5)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              x: { title: { display: true, text: 'Mois' } },
              y: { title: { display: true, text: 'Intérêts (Ar)' }, beginAtZero: true }
            }
          }
        });
      }

      // Chargement initial (tous les intérêts)
      chargerInterets();
    </script>
  </div>
</body>
</html>