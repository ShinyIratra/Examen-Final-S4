<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des étudiants</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Dashboard */
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
    
    /* Formulaire */
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
    <h1>Gestion des étudiants</h1>

    <div>
      <input type="hidden" id="id">
      <input type="text" id="nom" placeholder="Nom">
      <input type="text" id="prenom" placeholder="Prénom">
      <input type="email" id="email" placeholder="Email">
      <input type="number" id="age" placeholder="Âge">
      <button onclick="ajouterOuModifier()">Ajouter / Modifier</button>
    </div>

    <table id="table-etudiants">
      <thead>
        <tr>
          <th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Âge</th><th>Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <a href="test.html"> Test </a>
  </div>
  
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

    function chargerEtudiants() {
      ajax("GET", "/etudiants", null, (data) => {
        const tbody = document.querySelector("#table-etudiants tbody");
        tbody.innerHTML = "";
        data.forEach(e => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${e.id}</td>
            <td>${e.nom}</td>
            <td>${e.prenom}</td>
            <td>${e.email}</td>
            <td>${e.age}</td>
            <td>
              <button onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
              <button onclick='supprimerEtudiant(${e.id})'>🗑️</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      });
    }

    function ajouterOuModifier() {
      const id = document.getElementById("id").value;
      const nom = document.getElementById("nom").value;
      const prenom = document.getElementById("prenom").value;
      const email = document.getElementById("email").value;
      const age = document.getElementById("age").value;

      const dataObj = {
        nom,
        prenom,
        email,
        age
      };

      if (id) {
        ajax("PUT", `/etudiants/${id}`, JSON.stringify(dataObj), () => {
          resetForm();
          chargerEtudiants();
        });
      } else {
        ajax("POST", "/etudiants", JSON.stringify(dataObj), () => {
          resetForm();
          chargerEtudiants();
        });
      }
    }

    function remplirFormulaire(e) {
      document.getElementById("id").value = e.id;
      document.getElementById("nom").value = e.nom;
      document.getElementById("prenom").value = e.prenom;
      document.getElementById("email").value = e.email;
      document.getElementById("age").value = e.age;
    }

    function supprimerEtudiant(id) {
      if (confirm("Supprimer cet étudiant ?")) {
        ajax("DELETE", `/etudiants/${id}`, null, () => {
          chargerEtudiants();
        });
      }
    }

    function resetForm() {
      document.getElementById("id").value = "";
      document.getElementById("nom").value = "";
      document.getElementById("prenom").value = "";
      document.getElementById("email").value = "";
      document.getElementById("age").value = "";
    }

    chargerEtudiants();
  </script>

</body>
</html>