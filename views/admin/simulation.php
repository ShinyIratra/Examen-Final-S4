<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des simulations</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Gestion des simulations */
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
    
    /* Formulaire de simulation */
    .main-content > div:first-of-type {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 10px;
    }
    
    /* Inputs et boutons */
    input, select, button {
      border-radius: 6px;
      padding: 10px 15px;
      border: 1px solid #ddd;
      transition: all 0.3s ease;
    }
    
    input:focus, select:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(105, 92, 254, 0.2);
    }
    
    button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      cursor: pointer;
      font-weight: 500;
    }
    
    button:hover {
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
    
    /* Boutons d'action */
    td button {
      width: 32px;
      height: 32px;
      padding: 0;
      margin-right: 5px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }

    .actions-bar {
      margin-bottom: 15px;
      display: flex;
      justify-content: flex-end;
    }
  </style>
</head>
<body>
  <?php
    require '../layouts/sidebar.php';
  ?>

  <div class="main-content">
    <h1>Gestion des simulations</h1>

    <div>
      <input type="hidden" id="simulation_id">
      <input list="clients" id="id_client" placeholder="Choisir un client">
      <datalist id="clients"></datalist>
      <select id="id_type_pret">
        <option value="">Veuillez choisir un type de prêt</option>
      </select>
      <input type="number" id="montant" placeholder="Montant" step="0.01">
      <input type="date" id="date_pret" placeholder="Date du prêt">
      <input type="date" id="date_retour" placeholder="Date de retour">
      <input type="number" id="assurance" placeholder="Assurance" step="0.01">
      <input type="number" id="delai" placeholder="Délai" step="1" min="0">
      <button onclick="ajouterOuModifier()">Simuler</button>
      <button onclick="resetForm()">Réinitialiser</button>
    </div>

    <div class="actions-bar">
      <button id="compareBtn" onclick="compareSelectedSimulations()" disabled>
        <i class='bx bx-git-compare'></i> Comparer les simulations sélectionnées
      </button>
    </div>

    <table id="table-simulations">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" onchange="toggleAllCheckboxes(this.checked)"></th>
          <th>ID</th>
          <th>Montant</th>
          <th>Date Prêt</th>
          <th>Date Retour</th>
          <th>Assurance</th>
          <th>Délai</th>
          <th>Client</th>
          <th>Type Prêt</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    
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

      // Remplir le datalist des clients
      function chargerClients() {
        ajax("GET", "/clients/details", null, (data) => {
          const datalist = document.getElementById("clients");
          datalist.innerHTML = '';
          data.forEach(e => {
            const option = document.createElement("option");
            option.value = e.id_client;
            option.label = e.nom + " (" + e.identifiant + ")";
            option.textContent = e.nom + " (" + e.identifiant + ")";
            datalist.appendChild(option);
          });
        });
      }

      // Remplir le select des types de prêts
      function chargerTypePrets() {
        ajax("GET", "/type-prets", null, (data) => {
          const select = document.getElementById("id_type_pret");
          select.innerHTML = '<option value="">Veuillez choisir un type de prêt</option>';
          data.forEach(e => {
            const option = document.createElement("option");
            option.value = e.id_type_pret;
            option.textContent = e.nom + " - " + e.taux + "% sur " + e.duree_mois + " mois";
            select.appendChild(option);
          });
        });
      }

      // Charger la liste des simulations
      function chargerSimulations() {
        ajax("GET", "/simulations", null, (data) => {
          const tbody = document.querySelector("#table-simulations tbody");
          tbody.innerHTML = "";
          data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td><input type="checkbox" class="sim-checkbox" data-id="${e.id_simulation}" onchange="updateCompareButton()"></td>
              <td>${e.id_simulation}</td>
              <td>${e.montant}</td>
              <td>${e.date_pret || ""}</td>
              <td>${e.date_retour || ""}</td>
              <td>${e.assurance}</td>
              <td>${e.delai}</td>
              <td>${e.id_client}</td>
              <td>${e.id_type_pret}</td>
              <td>
                <button onclick='window.location.href="simulation_detail.php?id=${e.id_simulation}"'>👁️</button>
                <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
                <button onclick='supprimerSimulation(${e.id_simulation})'>🗑️</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        });
      }

      function ajouterOuModifier() {
        const id = document.getElementById("simulation_id").value;
        const id_client = document.getElementById("id_client").value;
        const id_type_pret = document.getElementById("id_type_pret").value;
        const montant = document.getElementById("montant").value;
        const assurance = document.getElementById("assurance").value || 0;
        const delai = document.getElementById("delai").value || 0;
        const date_pret = document.getElementById("date_pret").value;
        const date_retour = document.getElementById("date_retour").value;

        if (!id_client || !id_type_pret || !montant || !date_pret || !date_retour) {
          alert("Veuillez remplir tous les champs obligatoires.");
          return;
        }

        const dataObj = {
          id_client,
          id_type_pret,
          montant,
          assurance,
          delai,
          date_pret,
          date_retour
        };

        if (id) {
          ajax("PUT", `/simulations/${id}`, JSON.stringify(dataObj), () => {
            resetForm();
            chargerSimulations();
          });
        } else {
          ajax("POST", "/simulations", JSON.stringify(dataObj), (reponse) => {
            resetForm();
            chargerSimulations();
            // Afficher automatiquement les remboursements
            if (reponse.id) {
              voirRemboursements(reponse.id);
            }
          });
        }
      }

      function remplirFormulaire(e) {
        document.getElementById("simulation_id").value = e.id_simulation;
        document.getElementById("id_client").value = e.id_client;
        document.getElementById("id_type_pret").value = e.id_type_pret;
        document.getElementById("montant").value = e.montant;
        document.getElementById("assurance").value = e.assurance;
        document.getElementById("delai").value = e.delai;
        document.getElementById("date_pret").value = e.date_pret || "";
        document.getElementById("date_retour").value = e.date_retour || "";
      }

      function supprimerSimulation(id) {
        if (confirm("Supprimer cette simulation ?")) {
          ajax("DELETE", `/simulations/${id}`, null, () => {
            chargerSimulations();
          });
        }
      }

      function resetForm() {
        document.getElementById("simulation_id").value = "";
        document.getElementById("id_client").value = "";
        document.getElementById("id_type_pret").value = "";
        document.getElementById("montant").value = "";
        document.getElementById("assurance").value = "0";
        document.getElementById("delai").value = "0";
        document.getElementById("date_pret").value = "";
        document.getElementById("date_retour").value = "";
      }

      // Fonction pour afficher les remboursements
      function voirRemboursements(id) {
        document.getElementById("convertToPretBtn").setAttribute("data-id", id);
        ajax("GET", `/simulations/remboursements/${id}`, null, (data) => {
          const tbody = document.querySelector("#table-remboursements tbody");
          tbody.innerHTML = "";
          
          let totalCapital = 0;
          let totalInteret = 0;
          
          data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${e.date_remboursement}</td>
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
          
          // Afficher la modal
          document.getElementById("remboursementsModal").style.display = "block";
        });
      }

      function fermerModal() {
        document.getElementById("remboursementsModal").style.display = "none";
      }

      function convertToPret() {
        const id = document.getElementById("convertToPretBtn").getAttribute("data-id");
        if (confirm("Êtes-vous sûr de vouloir convertir cette simulation en prêt réel ?")) {
          ajax("POST", `/simulations/convert-to-pret/${id}`, null, (response) => {
            alert("La simulation a été convertie en prêt avec succès !");
            fermerModal();
            chargerSimulations();
          });
        }
      }

      function genererPDF() {
        const id = document.getElementById("convertToPretBtn").getAttribute("data-id");
        ajax("GET", `/simulations/pdf/${id}`, null, (response) => {
          if(response.success && response.pdf) {
            alert("PDF généré : " + response.pdf);
            // En production, vous pourriez faire quelque chose comme:
            // window.open(apiBase + '/download-pdf/' + response.pdf, '_blank');
          } else {
            alert("Erreur lors de la génération du PDF");
          }
        });
      }

      // Fermer la modal si on clique en dehors
      window.onclick = function(event) {
        const modal = document.getElementById("remboursementsModal");
        if (event.target === modal) {
          fermerModal();
        }
      }

      // Fonctions pour la comparaison de simulations
      function updateCompareButton() {
        const checkboxes = document.querySelectorAll('.sim-checkbox:checked');
        const compareBtn = document.getElementById('compareBtn');
        compareBtn.disabled = checkboxes.length !== 2;
      }

      function toggleAllCheckboxes(checked) {
        const checkboxes = document.querySelectorAll('.sim-checkbox');
        checkboxes.forEach(checkbox => {
          checkbox.checked = checked;
        });
        updateCompareButton();
      }

      function compareSelectedSimulations() {
        const selectedIds = Array.from(document.querySelectorAll('.sim-checkbox:checked')).map(cb => cb.dataset.id);
        
        if (selectedIds.length !== 2) {
          alert('Veuillez sélectionner exactement 2 simulations à comparer.');
          return;
        }
        
        // Rediriger vers une page de comparaison avec les IDs sélectionnés
        window.location.href = `simulation_compare.php?id1=${selectedIds[0]}&id2=${selectedIds[1]}`;
      }

      // Initialisation
      chargerClients();
      chargerTypePrets();
      chargerSimulations();
    </script>
  </div>
</body>
</html>