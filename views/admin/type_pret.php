<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des types de prêts</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Type de prêts */
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
    
    /* Formulaire de type de prêt */
    .main-content > div:first-of-type {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-wrap: wrap;
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
    
    /* Taux en surbrillance */
    td:nth-child(3) {
      font-weight: 600;
      color: #d81b60;
    }
    
    /* Durée en surbrillance */
    td:nth-child(4) {
      font-weight: 600;
      color: #1e88e5;
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
    <h1>Gestion des types de prêts</h1>

    <div>
      <input type="hidden" id="id_type_pret">
      <input type="text" id="nom" placeholder="Nom">
      <input type="number" id="taux" placeholder="Taux" step="0.01">
      <input type="number" id="duree_mois" placeholder="Durée (mois)" min="1">
      <button onclick="ajouterOuModifier()">Ajouter / Modifier</button>
    </div>

    <table id="table-type-prets">
      <thead>
        <tr>
          <th>ID</th><th>Nom</th><th>Taux (Pourcentage)</th><th>Durée (mois)</th><th>Action</th>
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

      function chargerTypePret() {
        ajax("GET", "/type-prets", null, (data) => {
          const tbody = document.querySelector("#table-type-prets tbody");
          tbody.innerHTML = "";
          data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${e.id_type_pret}</td>
              <td>${e.nom}</td>
              <td>${e.taux}</td>
              <td>${e.duree_mois}</td>
              <td>
                <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
                <button onclick='supprimerEtudiant(${e.id_type_pret})'>🗑️</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        });
      }

      function ajouterOuModifier() {
        const id_type_pret = document.getElementById("id_type_pret").value;
        const nom = document.getElementById("nom").value;
        const taux = document.getElementById("taux").value;
        const duree_mois = document.getElementById("duree_mois").value;

        const dataObj = {
          nom,
          taux,
          duree_mois,
        };

        if (id_type_pret) {
          ajax("PUT", `/type-prets/${id_type_pret}`, JSON.stringify(dataObj), () => {
            resetForm();
            chargerTypePret();
          });
        } else {
          ajax("POST", "/type-prets", JSON.stringify(dataObj), () => {
            resetForm();
            chargerTypePret();
          });
        }
      }

      function remplirFormulaire(e) {
        document.getElementById("id_type_pret").value = e.id_type_pret;
        document.getElementById("nom").value = e.nom;
        document.getElementById("taux").value = e.taux;
        document.getElementById("duree_mois").value = e.duree_mois;
      }

      function supprimerEtudiant(id_type_pret) {
        if (confirm("Supprimer ce type de prêt ?")) {
          ajax("DELETE", `/type-prets/${id_type_pret}`, null, () => {
            chargerTypePret();
          });
        }
      }

      function resetForm() {
        document.getElementById("id_type_pret").value = "";
        document.getElementById("nom").value = "";
        document.getElementById("taux").value = "";
        document.getElementById("duree_mois").value = "";
      }

      chargerTypePret();
    </script>
  </div>
</body>
</html>