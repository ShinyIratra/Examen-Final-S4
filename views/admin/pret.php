<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des prêts</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Gestion des prêts */
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
    
    /* Formulaire de prêt */
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
  </style>
</head>
<body>
  <?php
    require '../layouts/sidebar.php';
  ?>

  <div class="main-content">
    <h1>Gestion des prêts</h1>

    <div>
      <input type="hidden" id="pret_id">
      <input list="clients" id="id_client" placeholder="Choisir un client">
      <datalist id="clients"></datalist>
      <select id="id_type_pret">
        <option value="">Veuillez choisir un type de prêt</option>
      </select>
      <input type="number" id="montant" placeholder="Montant" step="0.01">
      <input type="date" id="date_pret" placeholder="Date du prêt">
      <input type="date" id="date_retour" placeholder="Date de retour">
      <input type="number" id="assurance" placeholder="Assurance" step="0.01">
      <input type="number" id="delai" placeholder="Delai" step="1" min="0">
      <button onclick="ajouterOuModifier()">Ajouter / Modifier</button>
      <button onclick="resetForm()">Réinitialiser</button>
    </div>

    <table id="table-prets">
      <thead>
        <tr>
          <th>ID</th>
          <th>Montant</th>
          <th>Date Prêt</th>
          <th>Date Retour</th>
          <th> Assurance </th>
          <th> Délai </th>
          <th>ID Client</th>
          <th>ID Type Prêt</th>
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
            option.textContent = e.nom;
            select.appendChild(option);
          });
        });
      }

      // Charger la liste des prêts
      function chargerPrets() {
        ajax("GET", "/prets", null, (data) => {
          const tbody = document.querySelector("#table-prets tbody");
          tbody.innerHTML = "";
          data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${e.id_pret}</td>
              <td>${e.montant}</td>
              <td>${e.date_pret || ""}</td>
              <td>${e.date_retour || ""}</td>
              <td>${e.assurance}</td>
              <td>${e.delai}</td>
              <td>${e.id_client}</td>
              <td>${e.id_type_pret}</td>
              <td>
                <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
                <button onclick='supprimerPret(${e.id_pret})'>🗑️</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        });
      }

      function ajouterOuModifier() {
        const id = document.getElementById("pret_id").value;
        const id_client = document.getElementById("id_client").value;
        const id_type_pret = document.getElementById("id_type_pret").value;
        const montant = document.getElementById("montant").value;
        const assurance = document.getElementById("assurance").value;
        const delai = document.getElementById("delai").value;
        const date_pret = document.getElementById("date_pret").value;
        const date_retour = document.getElementById("date_retour").value;

        if (!id_client || !id_type_pret || !montant || !assurance || !delai) {
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
          ajax("PUT", `/prets/${id}`, JSON.stringify(dataObj), () => {
            resetForm();
            chargerPrets();
          });
        } else {
          ajax("POST", "/prets", JSON.stringify(dataObj), () => {
            resetForm();
            chargerPrets();
          });
        }
      }

      function remplirFormulaire(e) {
        document.getElementById("pret_id").value = e.id_pret;
        document.getElementById("id_client").value = e.id_client;
        document.getElementById("id_type_pret").value = e.id_type_pret;
        document.getElementById("montant").value = e.montant;
        document.getElementById("assurance").value = e.assurance;
        document.getElementById("delai").value = e.delai;
        document.getElementById("date_pret").value = e.date_pret || "";
        document.getElementById("date_retour").value = e.date_retour || "";
      }

      function supprimerPret(id) {
        if (confirm("Supprimer ce prêt ?")) {
          ajax("DELETE", `/prets/${id}`, null, () => {
            chargerPrets();
          });
        }
      }

      function resetForm() {
        document.getElementById("pret_id").value = "";
        document.getElementById("id_client").value = "";
        document.getElementById("id_type_pret").value = "";
        document.getElementById("montant").value = "";
        document.getElementById("assurance").value = "";
        document.getElementById("delai").value = "";
        document.getElementById("date_pret").value = "";
        document.getElementById("date_retour").value = "";
      }

      // Initialisation
      chargerClients();
      chargerTypePrets();
      chargerPrets();
    </script>
  </div>
</body>
</html>