
<!DOCTYPE html>
<!-- Coding by CodingNepal | www.codingnepalweb.com -->
<html lang="en" dir="ltr">

<head>
  <title> Golden Asia </title>
  <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <style>

  </style>
</head>

<body>

  <div class="home">

    <div class="sidebar close">
      <div class="logo">
        <img src="logo.png" alt="Logo" style="width: 100px; position: absolute; top: 10px; left: 10px;">
      </div>
      <div class="logo-details">
        <span class="logo_name"> Asia Golden</span>
      </div>
      <ul class="nav-links">
      <li>
        <a href="newloan.php">
          <i class='bx bxs-bank'></i>
          <span class="link_name">New Loan</span>
        </a>
        
      </li>
      <li>
        <div class="iocn-link">
          <a href="cashier.php">
            <i class='bx bx-money'></i>
            <span class="link_name">Cashier</span>
          </a>
          
        </div>
        
      </li>
      <li>
        <div class="iocn-link">
          <a href="#">
            <i class='bx bxs-report' ></i>
            <span class="link_name">Reports</span>
          </a>
          
        </div>
      
      <li>
        <div class="iocn-link">
          <a href="#">
            <i class='bx bx-cog' ></i>
            <span class="link_name">Settings</span>
          </a>
          <i class='bx bxs-chevron-down arrow' ></i>
        </div>
        <ul class="sub-menu">
          <li><a class="link_name" href="#">Plugins</a></li>
          <li><a href="#">New Executive</a></li>
          <li><a href="#">New Branch</a></li>
          <li><a href="addgroup.php">New Group</a></li>
        </ul>
      </li>

    <li>
      <a href="login-user.php">
        <div class="profile-details">
          <div class="profile-content">
            <img src="image1.png" alt="profileImg">
          </div>
          <div class="name-job">
          <a href="login-user.php" class="logout-link">
   
    <span class="link_name">Logout</span>
    <i class='bx bx-log-out'></i>
  </a>
          </div>
        </div>
       
      </a>
    </li>
  </ul>
</div>



    <section class="home-section">
      <div class="home-content">
      <div class="top-bar">
        <i class='bx bx-menu'></i>
        </section>

  </div>
  <script>
    let arrow = document.querySelectorAll(".arrow");
    for (var i = 0; i < arrow.length; i++) {
      arrow[i].addEventListener("click", (e) => {
        let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
        arrowParent.classList.toggle("showMenu");
      });
    }
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    console.log(sidebarBtn);
    sidebarBtn.addEventListener("click", () => {
      sidebar.classList.toggle("close");
    });


  </script>
</body>

</html>