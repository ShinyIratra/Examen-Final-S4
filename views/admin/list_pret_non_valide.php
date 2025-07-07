<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Prêts Non Validés</title>
  <style>
    input, button, select { margin: 5px; padding: 5px; }
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
    <h1>Liste des Prêts Non Validés</h1>

    <table id="table-prets">
      <thead>
        <tr>
          <th>ID</th>
          <th>Montant</th>
          <th>Date Prêt</th>
          <th>Date Retour</th>
          <th>Assurance</th>
          <th>Délai</th>
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
          } else if (xhr.readyState === 4 && xhr.status !== 200) {
            console.error("Error in API call:", xhr.status, xhr.statusText);
          }
        };
        xhr.send(data);
      }

      // Charger la liste des prêts non validés
      function chargerPrets() {
        ajax("GET", "/prets/invalides/desc", null, (data) => {
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
                <button onclick='validerPret(${e.id_pret})'>Valider</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        });
      }

      function validerPret(id) {
        if (confirm("Valider ce prêt ?")) {
          ajax("POST", `/prets/valide/${id}`, null, (response) => {
            if (response.success) {
              alert("Prêt validé avec succès.");
              chargerPrets();
            } else {
              alert("Erreur lors de la validation du prêt: " + 
                (response.message || "Erreur inconnue"));
            }
          });
        }
      }

      // Initialisation - just load the loans, we don't need the other data
      chargerPrets();
    </script>
  </div>
</body>
</html>