<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>PDF Prêt Client</title>
  <!----======== CSS ======== -->
  <link rel="stylesheet" href="../layouts/style.css">
  <!----===== Boxicons CSS ===== -->
  <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
  
  <style>
    /* Styles pour la page PDF Client */
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
    
    /* Container */
    .container {
      background-color: var(--sidebar-color);
      border-radius: 8px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    /* Groupes de formulaire */
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--text-color);
    }
    
    /* Inputs et select */
    input, select, datalist {
      width: 100%;
      max-width: 400px;
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
    
    /* Bouton de téléchargement */
    #download-btn {
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 6px;
      padding: 12px 20px;
      cursor: pointer;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }
    
    #download-btn:before {
      content: '📄';
    }
    
    #download-btn:hover {
      background-color: #5a4fe6;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    #download-btn:disabled {
      background-color: #9e9e9e;
      cursor: not-allowed;
    }
  </style>
</head>
<body>
  <?php
    require '../layouts/sidebar.php';
  ?>

  <div class="main-content">
    <h1>PDF Prêt Client</h1>

    <div class="container">
      <div class="form-group">
        <label for="id_client">Sélectionnez un client :</label>
        <input list="clients" id="id_client" placeholder="Choisir un client" onchange="chargerPretsClient()">
        <datalist id="clients"></datalist>
      </div>
      
      <div class="form-group">
        <label for="id_pret">Sélectionnez un prêt :</label>
        <select id="id_pret">
          <option value="">Tous les prêts</option>
        </select>
      </div>
      
      <div class="form-group">
        <button id="download-btn" onclick="telechargerPDF()">Télécharger PDF</button>
      </div>
    </div>

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

      // Charger les prêts d'un client spécifique
      function chargerPretsClient() {
        const clientId = document.getElementById("id_client").value;
        if (!clientId) return;
        
        ajax("GET", "/prets/user/" + clientId, null, (data) => {
          const select = document.getElementById("id_pret");
          select.innerHTML = '<option value="">Tous les prêts</option>';
          
          if (data && data.length > 0) {
            data.forEach(pret => {
              const option = document.createElement("option");
              option.value = pret.id_pret;
              const montant = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(pret.montant);
              const datePret = pret.date_pret ? new Date(pret.date_pret).toLocaleDateString() : 'N/A';
              option.textContent = `Prêt #${pret.id_pret} - ${montant} (${datePret})`;
              select.appendChild(option);
            });
          }
        });
      }

      // Télécharger le PDF des prêts du client
      function telechargerPDF() {
        const clientId = document.getElementById("id_client").value;
        const pretId = document.getElementById("id_pret").value;
        
        if (!clientId) {
          alert("Veuillez sélectionner un client");
          return;
        }

        // Si un prêt spécifique est sélectionné, utilisez-le
        const id = pretId || clientId;
        
        // Afficher un indicateur de chargement si nécessaire
        document.getElementById("download-btn").disabled = true;
        document.getElementById("download-btn").textContent = "Téléchargement en cours...";
        
        // Utiliser fetch au lieu de rediriger
        fetch(apiBase + "/prets/pdf/" + id)
          .then(response => {
            if (!response.ok) {
              throw new Error("Erreur lors du téléchargement: " + response.status);
            }
            return response.json();
          })
          .then(data => {
            if (data.success && data.pdf) {
              // Convertir le base64 en Blob
              const binaryString = atob(data.pdf);
              const bytes = new Uint8Array(binaryString.length);
              for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
              }
              const blob = new Blob([bytes], { type: 'application/pdf' });
              
              // Créer un lien de téléchargement
              const link = document.createElement('a');
              link.href = window.URL.createObjectURL(blob);
              link.download = "contrat-pret-" + id + ".pdf";
              
              // Ajouter au document, cliquer et supprimer
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
              
              // Libérer l'URL
              setTimeout(() => window.URL.revokeObjectURL(link.href), 100);
            } else {
              alert("Échec lors de la génération du PDF: " + (data.message || "Erreur inconnue"));
            }
          })
          .catch(error => {
            console.error("Erreur:", error);
            alert("Une erreur est survenue lors du téléchargement du PDF");
          })
          .finally(() => {
            // Réinitialiser le bouton
            document.getElementById("download-btn").disabled = false;
            document.getElementById("download-btn").textContent = "Télécharger PDF";
          });
      }

      // Initialisation
      document.addEventListener('DOMContentLoaded', function() {
        chargerClients();
      });
    </script>
  </div>
</body>
</html>