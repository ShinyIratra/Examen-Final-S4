<nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="currentColor" class="bank-logo">
                        <path d="M11.5,1L2,6v2h19V6L11.5,1z M4,10h1v7H4V10z M7,10h1v7H7V10z M10,10h1v7h-1V10z M13,10h1v7h-1V10z M16,10h1v7h-1V10z M19,10h1v7h-1V10z M2,19v2h19v-2H2z"/>
                    </svg>
                </span>
                <div class="text logo-text">
                    <span class="name">FinanceHub</span>
                    <span class="profession">Gestion Bancaire</span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle'></i>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Search...">
                </li>
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="../admin/">
                            <i class='bx bx-home-alt icon' ></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../admin/ajout_fond.php">
                            <i class='bx bx-money icon' ></i>
                            <span class="text nav-text">Ajout Fond</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../admin/pret.php">
                            <i class='bx bx-dollar-circle icon'></i>
                            <span class="text nav-text">Ajouter Pret</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../admin/type_pret.php">
                            <i class='bx bx-category icon' ></i>
                            <span class="text nav-text">Type Pret</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../admin/list_pret_non_valide.php">
                            <i class='bx bx-x-circle icon' ></i>
                            <span class="text nav-text">Pret Non Valide</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../admin/pdf_client.php">
                            <i class='bx bx-wallet icon' ></i>
                            <span class="text nav-text">PDF Pret</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-content">
                <li class="">
                    <a href="#">
                        <i class='bx bx-log-out icon' ></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>
                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>
                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
                
            </div>
        </div>
    </nav>

<script>
        const body = document.querySelector('body'),
      sidebar = body.querySelector('nav'),
      toggle = body.querySelector(".toggle"),
      searchBtn = body.querySelector(".search-box"),
      modeSwitch = body.querySelector(".toggle-switch"),
      modeText = body.querySelector(".mode-text");
toggle.addEventListener("click" , () =>{
    sidebar.classList.toggle("close");
})
searchBtn.addEventListener("click" , () =>{
    sidebar.classList.remove("close");
})
modeSwitch.addEventListener("click" , () =>{
    body.classList.toggle("dark");
    
    if(body.classList.contains("dark")){
        modeText.innerText = "Light mode";
    }else{
        modeText.innerText = "Dark mode";
        
    }
});
</script>