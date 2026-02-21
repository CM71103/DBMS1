
<?php

session_start();

$_SESSION['Cart']= [];

$connection = pg_connect("host=localhost dbname=webapp1 user=postgres password=moulicm200607");

if(isset($_POST['submit'])){

    $email = $_POST['Email'];
    $pass = $_POST['Password'];

    $query = pg_query_params($connection,"Select * from \"Users\" where 
        \"Email\" = $1",array($email));




if($row = pg_fetch_assoc($query)) {
    
  
       if($email==$row['Email'] ){
            if($pass==$row['Password']){
                
                $_SESSION['Email'] = $email;
                $_SESSION['Password'] = $pass;
                $_SESSION['FullName'] = $row['FullName'];

                header("Location:mainpage1.php");
                exit;
            }
        else{
            header("Location: login1.php?error=wrongpassword&email=" . urlencode($email) . "&pass=" . urlencode($pass));
        }
       }
       else{
             header("Location: login1.php?error=wrongpassword");
            exit;
       }
    
}



}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen - Login</title>
     <link rel="stylesheet" href="login1.css">
    <style>
        /* Reset & font */
      /* Reset & font */
* { margin:0; padding:0; box-sizing:border-box; }
body { 
    font-family:'Poppins', sans-serif; 
    background: url("Photos/Hotel.jpg") no-repeat center center fixed; 
    background-size: cover; 
}

    </style>
</head>
<body>

    <!-- Top bar -->
    <div class="top-bar">
        <a href="login1.php" class="active">Login</a>
        <a href="login.php">Sign Up</a>
    </div>

    <!-- Login form -->
    <div class="container">
        <div class="form-box">
            <h2>Login to Your Account</h2>
            <form id="login-form" method="post" action="login1.php">
                <input type="email" name="Email" class="input-field" placeholder="Email" required>
                <div class="error" id="login-email-error"></div>

                <input type="password" name="Password" class="input-field" placeholder="Password" required>
                <div class="error" id="login-password-error"></div>

                <button type="submit" name="submit" class="submit-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
        // Simple JS validation
        const form = document.getElementById("login-form");
        form.addEventListener("submit", function(e){
            let isValid = true;
            const email = this.Email;
            const password = this.Password;

            document.getElementById("login-email-error").textContent = "";
            document.getElementById("login-password-error").textContent = "";

            if(!email.value.trim()){ 
                document.getElementById("login-email-error").textContent="Email required"; isValid=false;
            } else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)){
                document.getElementById("login-email-error").textContent="Invalid email"; isValid=false;
            }

            if(!password.value.trim()){ 
                document.getElementById("login-password-error").textContent="Password required"; isValid=false;
            }

            if(!isValid) e.preventDefault();
        });
    </script>

</body>
</html>
