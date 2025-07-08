<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajout de Fond</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Ajout de Fond */
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
    
    /* Formulaire de dépôt */
    .main-content > div:first-of-type {
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
    
    /* Inputs et boutons */
    input, button {
      border-radius: 6px;
      padding: 10px 15px;
      border: 1px solid #ddd;
      transition: all 0.3s ease;
    }
    
    input:focus {
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