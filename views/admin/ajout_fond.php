<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajout de Fond</title>
  <style>
    input, button { margin: 5px; padding: 5px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
  </style>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <?php
    require '../layouts/sidebar.php';
  ?>

  <div class="main-content">
    <h1>Ajouter fond dans l'établissement financier</h1>

    <div>
      <input type="hidden" id="id_depot">
      <input type="number" id="montant" placeholder="Montant">
      <input type="date" id="date_depot" placeholder="Date">
      <input type="number" id="id_utilisateur" placeholder="ID Utilisateur">
      <button onclick="ajouterOuModifier()">Ajouter / Modifier</button>
    </div>

    <table id="table-fond">
      <thead>
        <tr>
          <th>ID</th><th>Montant</th><th>Date</th><th>ID Utilisateur</th><th>Actions</th>
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
        } else {
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }
        xhr.onreadystatechange = () => {
          if (xhr.readyState === 4 && xhr.status === 200) {
            callback(JSON.parse(xhr.responseText));
          }
        };
        xhr.send(data);
      }

      function chargerDepot() {
        ajax("GET", "/depot", null, (data) => {
          const tbody = document.querySelector("#table-fond tbody");
          tbody.innerHTML = "";
          data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${e.id_depot}</td>
              <td>${e.montant}</td>
              <td>${e.date_depot}</td>
              <td>${e.id_utilisateur}</td>
              <td>
                <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
                <button onclick='supprimerDepot(${e.id_depot})'>🗑️</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        });
      }

      function ajouterOuModifier() {
        const id = document.getElementById("id_depot").value;
        const montant = document.getElementById("montant").value;
        const date = document.getElementById("date_depot").value;
        const id_utilisateur = document.getElementById("id_utilisateur").value;

        const dataObj = {
          montant,
          date_depot: date,
          id_utilisateur
        };

        if (id) {
          ajax("PUT", `/depot/${id}`, JSON.stringify(dataObj), () => {
            resetForm();
            chargerDepot();
          });
        } else {
          ajax("POST", "/depot", JSON.stringify(dataObj), () => {
            resetForm();
            chargerDepot();
          });
        }
      }

      function remplirFormulaire(e) {
        document.getElementById("id_depot").value = e.id_depot;
        document.getElementById("montant").value = e.montant;
        document.getElementById("date_depot").value = e.date_depot;
        document.getElementById("id_utilisateur").value = e.id_utilisateur;
      }

      function supprimerDepot(id) {
        if (confirm("Supprimer ce dépôt ?")) {
          ajax("DELETE", `/depot/${id}`, null, () => {
            chargerDepot();
          });
        }
      }

      function resetForm() {
        document.getElementById("id_depot").value = "";
        document.getElementById("montant").value = "";
        document.getElementById("date_depot").value = "";
        document.getElementById("id_utilisateur").value = "";
      }

      chargerDepot();
    </script>
  </div>
</body>
</html>
<?php /**PATH C:\laragon\www\projet\resources\views\ajoutFond.blade.php ENDPATH**/ ?>