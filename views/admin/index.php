<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Utilisateurs</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page Gestion des Utilisateurs */
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
    
    /* Formulaire d'utilisateur */
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
    input, select, button {
      border-radius: 6px;
      padding: 10px 15px;
      border: 1px solid #ddd;
      transition: all 0.3s ease;
      min-width: 150px;
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
    
    /* Badge de rôle */
    .role-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
      text-transform: uppercase;
    }
    
    .role-client {
      background-color: #e3f2fd;
      color: #1976d2;
    }
    
    .role-admin {
      background-color: #fff3e0;
      color: #f57c00;
    }
    
    .role-aucun {
      background-color: #f5f5f5;
      color: #757575;
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
    
    .password-field {
      position: relative;
    }
    
    .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      width: auto;
      height: auto;
      min-width: auto;
    }
  </style>
</head>
<body>
  <?php
    require '../layouts/sidebar.php';
  ?>

  <div class="main-content">
    <h1>Gestion des Utilisateurs</h1>

    <div>
      <input type="hidden" id="id_utilisateur">
      <input type="text" id="nom" placeholder="Nom complet" required>
      <input type="text" id="identifiant" placeholder="Identifiant" required>
      <div class="password-field">
        <input type="password" id="mdp" placeholder="Mot de passe" required>
        <button type="button" class="password-toggle" onclick="togglePassword()">
          <i class='bx bx-hide' id="password-icon"></i>
        </button>
      </div>
      
      <select id="role" required>
        <option value="">Choisir un rôle</option>
        <option value="client">Client</option>
        <option value="admin">Administrateur</option>
      </select>
      
      <button onclick="ajouterOuModifier()">Ajouter / Modifier</button>
      <button onclick="resetForm()">Réinitialiser</button>
    </div>

    <table id="table-utilisateurs">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Identifiant</th>
          <th>Rôle</th>
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
            callback(JSON.parse(xhr.responseText));
          } else if (xhr.readyState === 4 && xhr.status !== 200) {
            console.error("Error in API call:", xhr.status, xhr.statusText);
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              alert("Erreur: " + (errorResponse.error || "Erreur inconnue"));
            } catch (e) {
              alert("Erreur de communication avec le serveur");
            }
          }
        };
        xhr.send(data);
      }

      function chargerUtilisateurs() {
        ajax("GET", "/utilisateurs", null, (data) => {
          const tbody = document.querySelector("#table-utilisateurs tbody");
          tbody.innerHTML = "";
          data.forEach(e => {
            const roleBadgeClass = `role-${e.role}`;
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td>${e.id_utilisateur}</td>
              <td>${e.nom}</td>
              <td>${e.identifiant}</td>
              <td><span class="role-badge ${roleBadgeClass}">${e.role}</span></td>
              <td>
                <button onclick='remplirFormulaire(${JSON.stringify(e)})' title="Modifier">✏️</button>
                <button onclick='supprimerUtilisateur(${e.id_utilisateur})' title="Supprimer">🗑️</button>
              </td>
            `;
            tbody.appendChild(tr);
          });
        });
      }

      function ajouterOuModifier() {
        const id = document.getElementById("id_utilisateur").value;
        const nom = document.getElementById("nom").value;
        const identifiant = document.getElementById("identifiant").value;
        const mdp = document.getElementById("mdp").value;
        const role = document.getElementById("role").value;

        if (!nom || !identifiant || !role) {
          alert("Veuillez remplir tous les champs obligatoires.");
          return;
        }

        if (!id && !mdp) {
          alert("Le mot de passe est requis pour un nouvel utilisateur.");
          return;
        }

        const dataObj = {
          nom,
          identifiant,
          role
        };

        // Ajouter le mot de passe seulement s'il est fourni
        if (mdp) {
          dataObj.mdp = mdp;
        }

        if (id) {
          ajax("PUT", `/utilisateurs/${id}`, JSON.stringify(dataObj), () => {
            resetForm();
            chargerUtilisateurs();
            alert("Utilisateur modifié avec succès !");
          });
        } else {
          ajax("POST", "/utilisateurs", JSON.stringify(dataObj), () => {
            resetForm();
            chargerUtilisateurs();
            alert("Utilisateur créé avec succès !");
          });
        }
      }

      function remplirFormulaire(e) {
        document.getElementById("id_utilisateur").value = e.id_utilisateur;
        document.getElementById("nom").value = e.nom;
        document.getElementById("identifiant").value = e.identifiant;
        document.getElementById("role").value = e.role;
        document.getElementById("mdp").value = ""; // Ne pas pré-remplir le mot de passe
        document.getElementById("mdp").placeholder = "Laisser vide pour ne pas changer";
      }

      function supprimerUtilisateur(id) {
        if (confirm("Supprimer cet utilisateur ? Cette action est irréversible.")) {
          ajax("DELETE", `/utilisateurs/${id}`, null, () => {
            chargerUtilisateurs();
            alert("Utilisateur supprimé avec succès !");
          });
        }
      }

      function resetForm() {
        document.getElementById("id_utilisateur").value = "";
        document.getElementById("nom").value = "";
        document.getElementById("identifiant").value = "";
        document.getElementById("mdp").value = "";
        document.getElementById("role").value = "";
        document.getElementById("mdp").placeholder = "Mot de passe";
      }

      function togglePassword() {
        const passwordInput = document.getElementById("mdp");
        const passwordIcon = document.getElementById("password-icon");
        
        if (passwordInput.type === "password") {
          passwordInput.type = "text";
          passwordIcon.className = "bx bx-show";
        } else {
          passwordInput.type = "password";
          passwordIcon.className = "bx bx-hide";
        }
      }

      // Initialisation
      chargerUtilisateurs();
    </script>
  </div>
</body>
</html>