<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des remboursements</title>
  <style>
    input, button, select { margin: 5px; padding: 5px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
  </style>
  <link rel="stylesheet" href="../layouts/style.css">
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <?php require '../layouts/sidebar.php'; ?>

  <div class="main-content">
    <h1>Gestion des remboursements</h1>

    <div>
      <label for="filtre_id_pret">Filtrer par ID Prêt :</label>
      <select id="filtre_id_pret">
        <option value="">Tous les prêts validés</option>
      </select>
      <button onclick="filtrerParPret()">Filtrer</button>
      <button onclick="chargerRemboursements()">Tous</button>
    </div>

    <div>
      <input type="hidden" id="id_remboursement">
      <input type="number" id="montant" placeholder="Montant">
      <input type="date" id="date_remboursement" placeholder="Date remboursement">
      <input type="number" id="interet" placeholder="Intérêt">
      <input type="number" id="capital" placeholder="Capital">
      <select id="isPaid">
        <option value="0">Non payé</option>
        <option value="1">Payé</option>
      </select>
      <input type="date" id="date_payement" placeholder="Date paiement">
      <select id="id_pret">
        <option value="">Choisir un prêt validé</option>
      </select>
      <button onclick="ajouterOuModifier()">Ajouter / Modifier</button>
      <button onclick="resetForm()">Réinitialiser</button>
    </div>

    <table id="table-remboursements">
      <thead>
        <tr>
          <th>ID</th>
          <th>Montant</th>
          <th>Date remboursement</th>
          <th>Intérêt</th>
          <th>Capital</th>
          <th>Payé ?</th>
          <th>Date paiement</th>
          <th>ID Prêt</th>
          <th>Actions</th>
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
            callback && callback(JSON.parse(xhr.responseText));
          }
        };
        xhr.send(data);
      }

      function chargerRemboursements() {
        ajax("GET", "/remboursements", null, (data) => {
          remplirTable(data);
        });
      }

      function filtrerParPret() {
        const idPret = document.getElementById("filtre_id_pret").value;
        if (!idPret) {
          alert("Veuillez entrer un ID de prêt.");
          return;
        }
        ajax("GET", "/clients/remboursements/" + idPret, null, (data) => {
          remplirTable(data);
        });
      }

      function remplirTable(data) {
        const tbody = document.querySelector("#table-remboursements tbody");
        tbody.innerHTML = "";
        data.forEach(e => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${e.id_remboursement}</td>
            <td>${e.montant}</td>
            <td>${e.date_remboursement || ""}</td>
            <td>${e.interet}</td>
            <td>${e.capital}</td>
            <td>${e.isPaid == 1 ? "Oui" : "Non"}</td>
            <td>${e.date_payement || ""}</td>
            <td>${e.id_pret}</td>
            <td>
              <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
              <button onclick='supprimerRemboursement(${e.id_remboursement})'>🗑️</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }

      function ajouterOuModifier() {
        const id = document.getElementById("id_remboursement").value;
        const dataObj = {
          montant: document.getElementById("montant").value,
          date_remboursement: document.getElementById("date_remboursement").value,
          interet: document.getElementById("interet").value,
          capital: document.getElementById("capital").value,
          isPaid: document.getElementById("isPaid").value,
          date_payement: document.getElementById("date_payement").value,
          id_pret: document.getElementById("id_pret").value
        };
        if (id) {
          ajax("PUT", `/remboursements/${id}`, JSON.stringify(dataObj), () => {
            resetForm();
            chargerRemboursements();
          });
        } else {
          alert("tsisy ajout eh.");
        }
      }

      function remplirFormulaire(e) {
        document.getElementById("id_remboursement").value = e.id_remboursement;
        document.getElementById("montant").value = e.montant;
        document.getElementById("date_remboursement").value = e.date_remboursement ? e.date_remboursement.substring(0,10) : "";
        document.getElementById("interet").value = e.interet;
        document.getElementById("capital").value = e.capital;
        document.getElementById("isPaid").value = e.isPaid;
        document.getElementById("date_payement").value = e.date_payement ? e.date_payement.substring(0,10) : "";
        document.getElementById("id_pret").value = e.id_pret;
      }

      function supprimerRemboursement(id) {
        if (confirm("Supprimer ce remboursement ?")) {
          ajax("DELETE", `/remboursements/${id}`, null, () => {
            chargerRemboursements();
          });
        }
      }

      function resetForm() {
        document.getElementById("id_remboursement").value = "";
        document.getElementById("montant").value = "";
        document.getElementById("date_remboursement").value = "";
        document.getElementById("interet").value = "";
        document.getElementById("capital").value = "";
        document.getElementById("isPaid").value = "0";
        document.getElementById("date_payement").value = "";
        document.getElementById("id_pret").value = "";
      }

      // Charger uniquement les prêts validés
      function chargerPretsValides() {
        ajax("GET", "/prets/valides", null, (data) => {
          const select = document.getElementById("id_pret");
          select.innerHTML = '<option value="">Choisir un prêt validé</option>';
          data.forEach(e => {
            const option = document.createElement("option");
            option.value = e.id_pret;
            option.textContent = `Prêt #${e.id_pret} - ${e.montant}Ar (Client: ${e.id_client})`;
            select.appendChild(option);
          });
          
          // Remplir aussi le filtre
          const filterSelect = document.getElementById("filtre_id_pret");
          filterSelect.innerHTML = '<option value="">Tous les prêts validés</option>';
          data.forEach(e => {
            const option = document.createElement("option");
            option.value = e.id_pret;
            option.textContent = `Prêt ${e.id_pret}`;
            filterSelect.appendChild(option);
          });
        });
      }

      // Chargement initial
      chargerPretsValides();
      chargerRemboursements();
    </script>
  </div>
</body>
</html>