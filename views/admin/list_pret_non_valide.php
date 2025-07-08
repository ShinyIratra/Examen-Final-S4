<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Prêts Non Validés</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Prêts Non Validés */
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
    
    tr {
      background-color: rgba(255, 205, 210, 0.1);
    }
    
    tr:hover {
      background-color: rgba(255, 205, 210, 0.3);
    }
    
    /* Montant en surbrillance */
    td:nth-child(2) {
      font-weight: 600;
      color: #2e7d32;
    }
    
    /* Bouton Valider */
    td button {
      background-color: #43a047;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 8px 15px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    td button:hover {
      background-color: #2e7d32;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
  </style>
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