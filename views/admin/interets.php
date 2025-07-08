<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des intérêts</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Intérêts */
    .main-content {
      padding: 25px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .main-content h1 {
      color: var(--primary-color);
      font-size: 28px;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--primary-color-light);
    }
    
    /* Filtres */
    .filters {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 10px;
    }
    
    .filters label {
      font-weight: 500;
      margin-right: 5px;
    }
    
    .filters input {
      border-radius: 6px;
      padding: 10px 15px;
      border: 1px solid #ddd;
      transition: all 0.3s ease;
    }
    
    .filters input:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(105, 92, 254, 0.2);
    }
    
    .filters button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 10px 15px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .filters button:hover {
      background-color: #5a4fe6;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      background-color: var(--sidebar-color);
      margin-bottom: 30px;
    }
    
    th {
      background-color: var(--primary-color-light);
      color: var(--text-color);
      font-weight: 600;
      text-align: left;
      padding: 12px 15px;
    }
    
    td {
      padding: 10px 15px;
      border-bottom: 1px solid var(--primary-color-light);
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover {
      background-color: rgba(105, 92, 254, 0.05);
    }
    
    /* Montant en surbrillance */
    td:nth-child(3) {
      font-weight: 600;
      color: #2e7d32;
    }
    
    /* Style du graphique */
    #chart-container {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 20px;
      margin-top: 30px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    canvas {
      width: 100% !important;
      height: 300px !important;
    }
  </style>
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

    <!-- Conteneur pour le graphique -->
    <div id="chart-container">
      <canvas id="chart-interets"></canvas>
    </div>
    
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
          } else if (xhr.readyState === 4 && xhr.status !== 200) {
            console.error("Error in API call:", xhr.status, xhr.statusText);
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
            
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${e.annee}</td>
              <td>${mois}</td>
              <td>${parseFloat(e.total_interets).toFixed(2)} Ar</td>
            `;
            tbody.appendChild(tr);
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
              backgroundColor: 'rgba(105, 92, 254, 0.5)',
              borderColor: 'rgba(105, 92, 254, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: { 
                title: { 
                  display: true, 
                  text: 'Mois',
                  color: '#707070'
                } 
              },
              y: { 
                title: { 
                  display: true, 
                  text: 'Intérêts (Ar)',
                  color: '#707070'
                }, 
                beginAtZero: true 
              }
            },
            plugins: {
              legend: {
                labels: {
                  color: '#707070'
                }
              }
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