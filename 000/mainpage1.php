<?php

session_start();

if (!isset($_SESSION['Email'])) {
    header("Location: login1.php");
    exit;
}

$connection = pg_connect("host=localhost dbname=webapp1 user=postgres password=moulicm200607");

if (!isset($_SESSION['Cart'])) {
    $_SESSION['Cart'] = [];
}



if(isset($_POST['addToCart'])){
    $dishID = intval($_POST['dishId']);
   $email= $_SESSION['Email'];
    $qty = intval($_POST['qty']);

$category = $_POST['dishCategory'];

     $ItemCheck = pg_prepare($connection,"itmcheck",
    "Select * from  \"Inventory\" where \"InventoryName\" = $1 ");
  $ItemCheck1 =pg_execute($connection,"itmcheck",array($category));


$col = pg_fetch_assoc($ItemCheck1);
$qty1 = $col['Quantity'];


    
      if (is_null($qty1) || $qty1 == 0) { // also handle 0 quantity
            echo "
            <div class=\"empty-indicator\" id=\"emptyIndicator\">
                <svg viewBox=\"0 0 24 24\" fill=\"currentColor\">
                    <path d=\"M1 21h22L12 2 1 21zm13-3h-2v-2h2v2zm0-4h-2v-4h2v4z\"/>
                </svg>
                <span>Inventory '<strong>$category</strong>' is empty!</span>
            </div>
            ";
            
        }
       else{
        

    $ItemCategory = pg_query_params($connection,
    "Select * from  \"Item\" where \"ItemID\" =$1 ",array($dishID));

     

    $row3  = pg_fetch_assoc($ItemCategory);

    $Category = $row3['InventoryName'];
    $dishName =$row3['Name'];

    $InventUpdate = pg_prepare($connection,"queries",
    "Update \"Inventory\" set \"Quantity\" =GREATEST( \"Quantity\" - $1,0) 
    where \"InventoryName\"=$2;
    ");

        


    $InventUpdate1 = pg_execute($connection,"queries",array($qty,$Category));


     $items = ["ItemID"=>$dishID,"quant"=>$qty,"Name"=>$dishName];


     $_SESSION['Cart'][] =$items;
    
    
       }
    }



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amrita Food Delivery</title>
    <link rel="stylesheet" href="mainpage1.css">




</head>
<body>






    <div class="container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h1 id="main-title">Main Canteen<span>.</span></h1>
            </div>
            
            <div class="nav-menu">
                <div class="nav-item active" data-page="dashboard">
                    <i>📊</i>
                    <span>Dashboard</span>
                </div>
            </div>
        </div>

   


        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
 <!-- Place this at the top of your page -->

 

                <h2 id="welcome-message">Hello,<?php echo $_SESSION['FullName'] ?></h2>
                <div class="search-container">
                    <input type="text" class="search-bar" id="search-input" placeholder="What do you want eat today...">
                    <i class="search-icon">🔍</i>
                    <div class="search-results" id="search-results"></div>
                </div>
            </header>

            <a href="cart.php" id="cartIcon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 1.5A.5.5 0 0 1 .5 1h1a.5.5 0 0 1 
                         .485.379L2.89 5H14.5a.5.5 0 0 1 
                         .49.598l-1.5 7A.5.5 0 0 1 
                         13 13H4a.5.5 0 0 1-.491-.408L1.01 
                         2H.5a.5.5 0 0 1-.5-.5zm3.14 
                         4l1.25 5.5h7.22l1.25-5.5H3.14z"/>
                <circle cx="6" cy="15" r="1"/>
                <circle cx="12" cy="15" r="1"/>
            </svg>
        </a>
 




            <!-- Dashboard Page -->
            <div class="page active" id="dashboard">
                <h2 class="page-title">Dashboard</h2>
                
                <div class="mood-tracker">
                    <h3>How are you feeling today?</h3>
                    <div class="mood-options">
                        <div class="mood-option" data-mood="happy">😊</div>
                        <div class="mood-option" data-mood="sad">😢</div>
                        <div class="mood-option" data-mood="excited">😃</div>
                        <div class="mood-option" data-mood="tired">😴</div>
                        <div class="mood-option" data-mood="stressed">😰</div>
                    </div>
                    <p class="mood-recommendation" id="mood-recommendation">Select a mood to get personalized recommendations</p>
                    
                    <div class="mood-history">
                        <h4>Your Mood History</h4>
                        <div class="mood-chart">
                            <div class="mood-bar" style="height: 40%;">
                                <span class="mood-bar-label">Mon</span>
                            </div>
                            <div class="mood-bar" style="height: 70%;">
                                <span class="mood-bar-label">Tue</span>
                            </div>
                            <div class="mood-bar" style="height: 50%;">
                                <span class="mood-bar-label">Wed</span>
                            </div>
                            <div class="mood-bar" style="height: 80%;">
                                <span class="mood-bar-label">Thu</span>
                            </div>
                            <div class="mood-bar" style="height: 60%;">
                                <span class="mood-bar-label">Fri</span>
                            </div>
                            <div class="mood-bar" style="height: 90%;">
                                <span class="mood-bar-label">Sat</span>
                            </div>
                            <div class="mood-bar" style="height: 75%;">
                                <span class="mood-bar-label">Sun</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="dishes-grid" id="dashboard-dishes">
                    <!-- Dashboard dishes will be loaded here -->
                </div>
            </div>

            <!-- Food Order Page -->
            <div class="page" id="food-order">
                <h2 class="page-title">Food Order</h2>
                <div class="dishes-grid" id="food-order-dishes">
                    <!-- Food order dishes will be loaded here -->
                </div>
            </div>

            <!-- Birthday Cake Page -->
            <div class="page" id="birthday-cake">
                <h2 class="page-title">Birthday Cakes</h2>
                <div class="dishes-grid" id="cake-dishes">
                    <!-- Cake dishes will be loaded here -->
                </div>
            </div>

            <!-- Favorite Page -->
            <div class="page" id="favorite">
                <h2 class="page-title">Favorite Dishes</h2>
                <div id="favorite-items">
                    <!-- Favorite items will be loaded here -->
                </div>
            </div>

            <!-- Message Page -->
            <div class="page" id="message">
                <h2 class="page-title">Send a Message</h2>
                <div class="message-form">
                    <label for="message-content">Your Message</label>
                    <textarea id="message-content" placeholder="Type your message here..."></textarea>
                    <button class="add-to-cart"  id="send-message">Send Message</button>
                </div>
            </div>

            <!-- Order History Page -->
            <div class="page" id="order-history">
                <h2 class="page-title">Order History</h2>
                <div id="order-history-items">
                    <!-- Order history items will be loaded here -->
                </div>
            </div>

            <!-- Bills Page -->
            <div class="page" id="bills">
                <h2 class="page-title">Bills</h2>
                <div id="bills-items">
                    <!-- Bills items will be loaded here -->
                </div>
            </div>


            
            <!-- Profile Page -->
            <div class="page" id="profile">
                <h2 class="page-title">Profile</h2>
                <div class="profile-page">
                    <div class="profile-header-section">
                        <img src="https://media.istockphoto.com/id/1495088043/vector/user-profile-icon-avatar-or-person-icon-profile-picture-portrait-symbol-default-portrait.jpg?s=612x612&w=0&k=20&c=dhV2p1JwmloBTOaGAtaA3AW1KSnjsdMt7-U_3EZElZ0=" alt="User" class="profile-avatar-large">
                        <div class="profile-info">
                            <h3 class="profile-name" id="profile-display-name"><?php echo $_SESSION['FullName']  ?></h3>
                            <p class="profile-email" id="profile-display-email"><?php echo $_SESSION['Email']  ?></p>
                            <div class="profile-stats">
                                <div class="profile-stat">
                                    <div class="profile-stat-value">12</div>
                                    <div class="profile-stat-label">Orders</div>
                                </div>
                                <div class="profile-stat">
                                    <div class="profile-stat-value">5</div>
                                    <div class="profile-stat-label">Favorites</div>
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button class="profile-action-btn edit-profile-btn" id="edit-profile-btn">Edit Profile</button>
                                <button class="profile-action-btn share-profile-btn">Share Profile</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
    <script src="mainpage1.js"></script>
</body>
</html>