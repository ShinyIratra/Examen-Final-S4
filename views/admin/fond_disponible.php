<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Fonds disponibles</title>
  <link rel="stylesheet" href="../layouts/style.css">
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
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
    
    .filters {
      margin-bottom: 15px;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    
    .filters input, .filters select, .filters button {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    .filters button {
      background-color: var(--primary-color);
      color: white;
      cursor: pointer;
    }
    
    .filters button:hover {
      background-color: var(--primary-color-dark);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    th, td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    
    th {
      background-color: var(--primary-color);
      color: white;
    }
    
    .montant-positif {
      color: #2e7d32;
      font-weight: bold;
    }
    
    .montant-negatif {
      color: #d32f2f;
      font-weight: bold;
    }
    
    .toggle-buttons {
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
    }
    
    .toggle-btn {
      padding: 8px 16px;
      border: 1px solid var(--primary-color);
      background-color: white;
      color: var(--primary-color);
      cursor: pointer;
      border-radius: 4px;
    }
    
    .toggle-btn.active {
      background-color: var(--primary-color);
      color: white;
    }
    
    .toggle-btn:hover {
      background-color: var(--primary-color-light);
    }
    
    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .summary-card {
      background-color: var(--sidebar-color);
      padding: 20px;
      border-radius: 8px;
      text-align: center;
    }
    
    .summary-card h3 {
      margin: 0 0 10px 0;
      color: var(--primary-color);
    }
    
    .summary-card .amount {
      font-size: 24px;
      font-weight: bold;
      color: #2e7d32;
    }
  </style>
</head>
<body>
  <?php require '../layouts/sidebar.php'; ?>

  <div class="main-content">
    <h1>Fonds disponibles par mois</h1>
    
    <div class="summary-cards">
      <div class="summary-card">
        <h3>Total Actuel</h3>
        <div class="amount" id="total-actuel">0.00 €</div>
      </div>
      <div class="summary-card">
        <h3>Dépôts du mois</h3>
        <div class="amount" id="depots-mois">0.00 €</div>
      </div>
      <div class="summary-card">
        <h3>Prêts du mois</h3>
        <div class="amount" id="prets-mois">0.00 €</div>
      </div>
      <div class="summary-card">
        <h3>Remboursements du mois</h3>
        <div class="amount" id="remboursements-mois">0.00 €</div>
      </div>
    </div>

    <div class="toggle-buttons">
      <button class="toggle-btn active" onclick="toggleView('mensuel')">Vue mensuelle</button>
      <button class="toggle-btn" onclick="toggleView('cumule')">Vue cumulée</button>
    </div>

    <div class="filters">
      <label for="annee">Année :</label>
      <select id="annee">
        <option value="">Toutes</option>
      </select>
      
      <label for="mois">Mois :</label>
      <select id="mois">
        <option value="">Tous</option>
        <option value="1">Janvier</option>
        <option value="2">Février</option>
        <option value="3">Mars</option>
        <option value="4">Avril</option>
        <option value="5">Mai</option>
        <option value="6">Juin</option>
        <option value="7">Juillet</option>
        <option value="8">Août</option>
        <option value="9">Septembre</option>
        <option value="10">Octobre</option>
        <option value="11">Novembre</option>
        <option value="12">Décembre</option>
      </select>
      
      <button onclick="filtrerFonds()">Filtrer</button>
      <button onclick="resetFiltre()">Réinitialiser</button>
    </div>

    <table id="table-fonds">
      <thead>
        <tr>
          <th>Année</th>
          <th>Mois</th>
          <th>Total Dépôts</th>
          <th>Total Prêts</th>
          <th>Total Remboursements</th>
          <th>Montant Disponible</th>
          <th id="header-cumule" style="display: none;">Montant Cumulé</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <canvas id="chart-fonds" width="800" height="400" style="margin-top: 30px;"></canvas>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../env.js"></script>
  <script>
    let currentView = 'mensuel';
    let chartFonds = null;
    
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

    function toggleView(view) {
      currentView = view;
      
      // Update toggle buttons
      document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.classList.add('active');
      
      // Show/hide cumulative column
      const cumulativeHeader = document.getElementById('header-cumule');
      const cumulativeCells = document.querySelectorAll('.cumule-cell');
      
      if (view === 'cumule') {
        cumulativeHeader.style.display = 'table-cell';
        cumulativeCells.forEach(cell => cell.style.display = 'table-cell');
      } else {
        cumulativeHeader.style.display = 'none';
        cumulativeCells.forEach(cell => cell.style.display = 'none');
      }
      
      chargerFonds();
    }

    function chargerFonds(annee = null, mois = null) {
      let url = currentView === 'cumule' ? "/fond-disponible/cumule" : "/fond-disponible";
      
      if (annee && mois) {
        url = `/fond-disponible/${annee}/${mois}`;
      }
      
      ajax("GET", url, null, (data) => {
        const tbody = document.querySelector("#table-fonds tbody");
        tbody.innerHTML = "";
        
        const labels = [];
        const disponibleData = [];
        const depotsData = [];
        const pretsData = [];
        const remboursementsData = [];
        
        let totalActuel = 0;
        let depotsMoisActuel = 0;
        let pretsMoisActuel = 0;
        let remboursementsMoisActuel = 0;
        
        data.forEach(e => {
          const tr = document.createElement("tr");
          const moisNom = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'][e.mois];
          
          const montantClass = e.montant_disponible >= 0 ? 'montant-positif' : 'montant-negatif';
          
          let cumulativeCell = '';
          if (currentView === 'cumule' && e.montant_cumule !== undefined) {
            const cumulativeClass = e.montant_cumule >= 0 ? 'montant-positif' : 'montant-negatif';
            cumulativeCell = `<td class="cumule-cell ${cumulativeClass}">${parseFloat(e.montant_cumule).toFixed(2)} €</td>`;
          } else {
            cumulativeCell = `<td class="cumule-cell" style="display: none;"></td>`;
          }
          
          tr.innerHTML = `
            <td>${e.annee}</td>
            <td>${moisNom}</td>
            <td>${parseFloat(e.total_depots).toFixed(2)} €</td>
            <td>${parseFloat(e.total_prets).toFixed(2)} €</td>
            <td>${parseFloat(e.total_remboursements).toFixed(2)} €</td>
            <td class="${montantClass}">${parseFloat(e.montant_disponible).toFixed(2)} €</td>
            ${cumulativeCell}
          `;
          tbody.appendChild(tr);
          
          // Préparer les données pour le graphique
          labels.push(`${e.annee}-${moisNom}`);
          disponibleData.push(parseFloat(e.montant_disponible));
          depotsData.push(parseFloat(e.total_depots));
          pretsData.push(parseFloat(e.total_prets));
          remboursementsData.push(parseFloat(e.total_remboursements));
          
          // Calculer les totaux pour les cartes de résumé
          totalActuel = currentView === 'cumule' && e.montant_cumule !== undefined ? e.montant_cumule : e.montant_disponible;
          depotsMoisActuel += parseFloat(e.total_depots);
          pretsMoisActuel += parseFloat(e.total_prets);
          remboursementsMoisActuel += parseFloat(e.total_remboursements);
        });
        
        // Mettre à jour les cartes de résumé
        document.getElementById('total-actuel').textContent = totalActuel.toFixed(2) + ' €';
        document.getElementById('depots-mois').textContent = depotsMoisActuel.toFixed(2) + ' €';
        document.getElementById('prets-mois').textContent = pretsMoisActuel.toFixed(2) + ' €';
        document.getElementById('remboursements-mois').textContent = remboursementsMoisActuel.toFixed(2) + ' €';
        
        // Remplir les années dans le select
        const anneesUniques = [...new Set(data.map(e => e.annee))];
        const selectAnnee = document.getElementById('annee');
        selectAnnee.innerHTML = '<option value="">Toutes</option>';
        anneesUniques.forEach(annee => {
          const option = document.createElement('option');
          option.value = annee;
          option.textContent = annee;
          selectAnnee.appendChild(option);
        });
        
        afficherGraphique(labels, disponibleData, depotsData, pretsData, remboursementsData);
      });
    }

    function filtrerFonds() {
      const annee = document.getElementById("annee").value;
      const mois = document.getElementById("mois").value;
      
      if (annee && mois) {
        chargerFonds(annee, mois);
      } else {
        chargerFonds();
      }
    }

    function resetFiltre() {
      document.getElementById("annee").value = "";
      document.getElementById("mois").value = "";
      chargerFonds();
    }

    function afficherGraphique(labels, disponibleData, depotsData, pretsData, remboursementsData) {
      const ctx = document.getElementById('chart-fonds').getContext('2d');
      
      if (chartFonds) {
        chartFonds.destroy();
      }
      
      chartFonds = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Montant Disponible',
              data: disponibleData,
              borderColor: 'rgb(75, 192, 192)',
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              tension: 0.1
            },
            {
              label: 'Dépôts',
              data: depotsData,
              borderColor: 'rgb(54, 162, 235)',
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              tension: 0.1
            },
            {
              label: 'Prêts',
              data: pretsData,
              borderColor: 'rgb(255, 99, 132)',
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              tension: 0.1
            },
            {
              label: 'Remboursements',
              data: remboursementsData,
              borderColor: 'rgb(255, 206, 86)',
              backgroundColor: 'rgba(255, 206, 86, 0.2)',
              tension: 0.1
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Évolution des fonds disponibles'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Montant (€)'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Période'
              }
            }
          }
        }
      });
    }

    // Initialisation
    chargerFonds();
  </script>
</body>
</html>