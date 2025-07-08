<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Comparaison de simulations</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page de comparaison */
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
    
    .comparison-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    
    .simulation-card {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .simulation-details {
      margin-bottom: 20px;
    }
    
    .simulation-details h2 {
      color: var(--primary-color);
      margin-bottom: 15px;
      font-size: 20px;
    }
    
    .detail-row {
      display: flex;
      border-bottom: 1px solid var(--primary-color-light);
      padding: 8px 0;
    }
    
    .detail-label {
      font-weight: 600;
      min-width: 150px;
    }
    
    .detail-value {
      flex-grow: 1;
    }
    
    .highlight {
      background-color: rgba(255, 255, 0, 0.2);
      padding: 2px 5px;
      border-radius: 3px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    
    th, td {
      padding: 8px 10px;
      border-bottom: 1px solid var(--primary-color-light);
      text-align: left;
    }
    
    th {
      background-color: var(--primary-color-light);
      font-weight: 600;
    }
    
    .total-row {
      font-weight: 600;
      background-color: rgba(105, 92, 254, 0.1);
    }
    
    .actions {
      margin-top: 20px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 10px 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    button:hover {
      background-color: #5a4fe6;
    }
    
    .back-btn {
      background-color: #6c757d;
    }
    
    .back-btn:hover {
      background-color: #5a6268;
    }
    
    .diff-highlight {
      font-weight: bold;
      color: #d81b60;
    }
  </style>
</head>
<body>
  <?php require '../layouts/sidebar.php'; ?>

  <div class="main-content">
    <h1>Comparaison de simulations</h1>
    
    <div class="actions" style="margin-bottom: 20px;">
      <button class="back-btn" onclick="window.location.href='simulation.php'">
        <i class='bx bx-arrow-back'></i> Retour
      </button>
    </div>
    
    <div class="comparison-container" id="comparison-container">
      <!-- Les détails des simulations seront insérés ici par JavaScript -->
    </div>

    <script src="../env.js"></script>
    <script>
      // Get simulation IDs from URL
      const urlParams = new URLSearchParams(window.location.search);
      const id1 = urlParams.get('id1');
      const id2 = urlParams.get('id2');
      
      if (!id1 || !id2) {
        alert("IDs de simulation non spécifiés");
        window.location.href = "simulation.php";
      }
      
      let simulations = [];
      
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
      
      function chargerSimulation(id, index) {
        return new Promise((resolve) => {
          ajax("GET", `/simulations/${id}`, null, (data) => {
            simulations[index] = data;
            resolve();
          });
        });
      }
      
      // Remplacer cette partie dans la fonction chargerRemboursements (lignes 153-166)
      function chargerRemboursements(id, index) {
        return new Promise((resolve) => {
          ajax("GET", `/simulations/remboursements/${id}`, null, (data) => {
            simulations[index].remboursements = data;
            
            // Calculer les totaux correctement
            let totalCapital = 0;
            let totalInteret = 0;
            let totalPaiement = 0;
            
            data.forEach(e => {
              totalCapital += parseFloat(e.capital);
              totalInteret += parseFloat(e.interet);
              totalPaiement += parseFloat(e.montant); // Utiliser le montant total qui inclut l'assurance
            });
            
            simulations[index].totalCapital = totalCapital;
            simulations[index].totalInteret = totalInteret;
            simulations[index].totalPaiement = totalPaiement; // Ne pas recalculer, utiliser la somme des montants
            
            resolve();
          });
        });
      }
      
      // Charger les données et afficher la comparaison
      async function initialiserComparaison() {
        await Promise.all([
          chargerSimulation(id1, 0),
          chargerSimulation(id2, 1)
        ]);
        
        await Promise.all([
          chargerRemboursements(id1, 0),
          chargerRemboursements(id2, 1)
        ]);
        
        afficherComparaison();
      }
      
      function afficherComparaison() {
        const container = document.getElementById('comparison-container');
        container.innerHTML = '';
        
        // Identifier les différences
        const differences = {
          montant: simulations[0].montant !== simulations[1].montant,
          assurance: simulations[0].assurance !== simulations[1].assurance,
          delai: simulations[0].delai !== simulations[1].delai,
          date_pret: simulations[0].date_pret !== simulations[1].date_pret,
          date_retour: simulations[0].date_retour !== simulations[1].date_retour,
          taux: simulations[0].taux !== simulations[1].taux,
          totalPaiement: Math.abs(simulations[0].totalPaiement - simulations[1].totalPaiement) > 0.01,
          totalInteret: Math.abs(simulations[0].totalInteret - simulations[1].totalInteret) > 0.01
        };
        
        // Créer les cartes de simulation
        simulations.forEach((sim, index) => {
          const card = document.createElement('div');
          card.className = 'simulation-card';
          
          // Détails de la simulation
          let detailsHTML = `
            <div class="simulation-details">
              <h2>Simulation #${sim.id_simulation}</h2>
              <div class="detail-row">
                <div class="detail-label">Montant:</div>
                <div class="detail-value ${differences.montant ? 'diff-highlight' : ''}">${parseFloat(sim.montant).toFixed(2)} </div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Taux:</div>
                <div class="detail-value ${differences.taux ? 'diff-highlight' : ''}">${parseFloat(sim.taux).toFixed(2)}%</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Date début:</div>
                <div class="detail-value ${differences.date_pret ? 'diff-highlight' : ''}">${new Date(sim.date_pret).toLocaleDateString()}</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Date fin:</div>
                <div class="detail-value ${differences.date_retour ? 'diff-highlight' : ''}">${new Date(sim.date_retour).toLocaleDateString()}</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Assurance:</div>
                <div class="detail-value ${differences.assurance ? 'diff-highlight' : ''}">${parseFloat(sim.assurance).toFixed(2)} % </div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Délai:</div>
                <div class="detail-value ${differences.delai ? 'diff-highlight' : ''}">${sim.delai} mois</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Client:</div>
                <div class="detail-value">${sim.nom_client || sim.id_client}</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Total paiements:</div>
                <div class="detail-value ${differences.totalPaiement ? 'diff-highlight' : ''}">${sim.totalPaiement.toFixed(2)} </div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Total intérêts:</div>
                <div class="detail-value ${differences.totalInteret ? 'diff-highlight' : ''}">${sim.totalInteret.toFixed(2)} </div>
              </div>
            </div>
          `;
          
          // Tableau d'amortissement
          let tableHTML = `
            <h3>Tableau d'amortissement</h3>
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Montant</th>
                  <th>Capital</th>
                  <th>Intérêt</th>
                </tr>
              </thead>
              <tbody>
          `;
          
          sim.remboursements.forEach(r => {
            tableHTML += `
              <tr>
                <td>${new Date(r.date_remboursement).toLocaleDateString()}</td>
                <td>${parseFloat(r.montant).toFixed(2)} </td>
                <td>${parseFloat(r.capital).toFixed(2)} </td>
                <td>${parseFloat(r.interet).toFixed(2)} </td>
              </tr>
            `;
          });
          
          // Ligne de total
          tableHTML += `
              <tr class="total-row">
                <td>TOTAL</td>
                <td>${sim.totalPaiement.toFixed(2)} </td>
                <td>${sim.totalCapital.toFixed(2)} </td>
                <td>${sim.totalInteret.toFixed(2)} </td>
              </tr>
            </tbody>
          </table>
          `;
          
          // Actions
          let actionsHTML = `
            <div class="actions">
              <button onclick="window.location.href='simulation_detail.php?id=${sim.id_simulation}'">
                <i class='bx bx-detail'></i> Voir détails
              </button>
            </div>
          `;
          
          card.innerHTML = detailsHTML + tableHTML + actionsHTML;
          container.appendChild(card);
        });
      }
      
      // Initialisation
      initialiserComparaison();
    </script>
  </div>
</body>
</html>