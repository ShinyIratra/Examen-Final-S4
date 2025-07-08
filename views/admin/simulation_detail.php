<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détails de la simulation</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page détails de simulation */
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
    
    .simulation-info {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 10px;
    }
    
    .simulation-info p {
      margin: 0;
      padding: 10px;
      border-bottom: 1px solid var(--primary-color-light);
    }
    
    .simulation-info strong {
      color: var(--text-color);
      font-weight: 600;
    }
    
    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      background-color: var(--sidebar-color);
      margin-bottom: 20px;
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
    td:nth-child(2) {
      font-weight: 600;
      color: #2e7d32;
    }
    
    /* Intérêt en surbrillance */
    td:nth-child(4) {
      font-weight: 600;
      color: #d81b60;
    }
    
    /* Boutons d'action */
    .actions {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      justify-content: flex-end;
    }
    
    button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      cursor: pointer;
      font-weight: 500;
      padding: 10px 15px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: all 0.3s ease;
    }
    
    button:hover {
      background-color: #5a4fe6;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    button.back-btn {
      background-color: #6c757d;
    }
    
    button.back-btn:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>
  <?php
    require '../layouts/sidebar.php';
  ?>

  <div class="main-content">
    <h1>Détails de la simulation <span id="simulation-id"></span></h1>
    
    <div class="simulation-info" id="simulation-summary">
      <!-- Les détails de la simulation seront insérés ici par JavaScript -->
    </div>

    <h2>Tableau d'amortissement</h2>
    <table id="table-remboursements">
      <thead>
        <tr>
          <th>Date</th>
          <th>Montant</th>
          <th>Capital (Avec Assurance)</th>
          <th>Intérêt</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    
    <div class="actions">
      <button class="back-btn" onclick="window.location.href='simulation.php'">
        <i class='bx bx-arrow-back'></i> Retour
      </button>
      <button id="convertToPretBtn" onclick="convertToPret()">
        <i class='bx bx-check-circle'></i> Convertir en prêt
      </button>
    </div>

    <script src="../env.js"></script>
    <script>
      // Get simulation ID from URL
      const urlParams = new URLSearchParams(window.location.search);
      const simulationId = urlParams.get('id');
      
      if (!simulationId) {
        alert("ID de simulation non spécifié");
        window.location.href = "simulation.php";
      }
      
      document.getElementById("simulation-id").textContent = "#" + simulationId;
      document.getElementById("convertToPretBtn").setAttribute("data-id", simulationId);
      
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
      
      // Charger les détails de la simulation
      function chargerSimulation() {
        ajax("GET", `/simulations/${simulationId}`, null, (data) => {
          // Afficher les informations de base de la simulation
          const summary = document.getElementById("simulation-summary");
          
          summary.innerHTML = `
            <p><strong>Montant:</strong> ${parseFloat(data.montant).toFixed(2)} €</p>
            <p><strong>Date de début:</strong> ${new Date(data.date_pret).toLocaleDateString()}</p>
            <p><strong>Date de fin:</strong> ${new Date(data.date_retour).toLocaleDateString()}</p>
            <p><strong>Assurance:</strong> ${parseFloat(data.assurance).toFixed(2)} €</p>
            <p><strong>Délai:</strong> ${data.delai} mois</p>
            <p><strong>Client:</strong> ${data.nom_client || data.id_client}</p>
            <p><strong>Taux:</strong> ${parseFloat(data.taux).toFixed(2)}%</p>
          `;
        });
      }
      
      // Charger les remboursements
      function chargerRemboursements() {
        ajax("GET", `/simulations/remboursements/${simulationId}`, null, (data) => {
          const tbody = document.querySelector("#table-remboursements tbody");
          tbody.innerHTML = "";
          
          let totalCapital = 0;
          let totalInteret = 0;
          
          data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${new Date(e.date_remboursement).toLocaleDateString()}</td>
              <td>${parseFloat(e.montant).toFixed(2)} €</td>
              <td>${parseFloat(e.capital).toFixed(2)} €</td>
              <td>${parseFloat(e.interet).toFixed(2)} €</td>
            `;
            tbody.appendChild(tr);
            
            totalCapital += parseFloat(e.capital);
            totalInteret += parseFloat(e.interet);
          });
          
          // Ajouter une ligne de total
          const trTotal = document.createElement("tr");
          trTotal.style.fontWeight = "bold";
          trTotal.innerHTML = `
            <td>TOTAL</td>
            <td>${(totalCapital + totalInteret).toFixed(2)} €</td>
            <td>${totalCapital.toFixed(2)} €</td>
            <td>${totalInteret.toFixed(2)} €</td>
          `;
          tbody.appendChild(trTotal);
        });
      }

      function convertToPret() {
        if (confirm("Êtes-vous sûr de vouloir convertir cette simulation en prêt réel ?")) {
          ajax("POST", `/simulations/convert-to-pret/${simulationId}`, null, (response) => {
            alert("La simulation a été convertie en prêt avec succès !");
            window.location.href = "pret.php"; // Rediriger vers la page des prêts
          });
        }
      }

      // Initialisation
      chargerSimulation();
      chargerRemboursements();
    </script>
  </div>
</body>
</html>