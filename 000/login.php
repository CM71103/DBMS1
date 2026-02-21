<?php
session_start();
require_once 'config.php';

$message = "";

if(isset($_POST['submit'])){
    $Fname = trim($_POST['Fname']);
    $Email = trim($_POST['Email']);
    $Pass = $_POST['Password'];

    if($Fname && $Email && $Pass){
        // Check if email already exists
        $check = pg_query_params($connection, "SELECT * FROM \"Users\" WHERE \"Email\" = $1", array($Email));
        
        if (pg_num_rows($check) > 0) {
            $message = "Email already registered. Please login.";
        } else {
            $query = "INSERT INTO \"Users\" (\"FullName\", \"Email\", \"Password\") VALUES ($1, $2, $3)";
            $result = pg_query_params($connection, $query, array($Fname, $Email, $Pass));

            if($result) {
                $_SESSION['Email'] = $Email;
                $_SESSION['FullName'] = $Fname;
                header("Location: mainpage1.php");
                exit;
            } else {
                $message = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Canteen Signup</title>
     <link rel="stylesheet" href="login.css">
    <style>
    </style>
</head>
<body>

<div class="top-bar">
        <a href="login1.php">Login</a>
        <a href="login.php" class="active">Sign Up</a>
    </div>


    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if($message) echo "<div class='message'>{$message}</div>"; ?>
        <form id="signup-form" method="post" action="">
            <input type="text" id="signup-name" name="Fname" class="input-field" placeholder="Full Name" required>
            <div class="error" id="signup-name-error"></div>

            <input type="email" id="signup-email" name="Email" class="input-field" placeholder="Email" required>
            <div class="error" id="signup-email-error"></div>

            <input type="password" id="signup-password" name="Password" class="input-field" placeholder="Password" required>
            <div class="password-strength" id="strength-bar"></div>
            <span class="strength-text" id="strength-text">Password strength</span>
            <div class="error" id="signup-password-error"></div>

            <input type="password" id="signup-confirm" class="input-field" placeholder="Confirm Password" required>
            <div class="error" id="signup-confirm-error"></div>

            <label><input type="checkbox" id="terms"> I agree to the Terms & Conditions</label>

            <button type="submit" name="submit" class="submit-btn">Sign Up</button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById("signup-password");
        const strengthBar = document.getElementById("strength-bar");
        const strengthText = document.getElementById("strength-text");

        passwordInput.addEventListener("input", function() {
            const val = passwordInput.value;
            let strength = 0;
            if(val.length > 5) strength++;
            if(val.match(/[a-z]/) && val.match(/[A-Z]/)) strength++;
            if(val.match(/\d/)) strength++;
            if(val.match(/[^a-zA-Z0-9]/)) strength++;

            switch(strength){
                case 0: case 1:
                    strengthBar.style.width = "25%"; strengthBar.style.background = "#e74c3c"; strengthText.textContent = "Weak"; break;
                case 2: case 3:
                    strengthBar.style.width = "50%"; strengthBar.style.background = "#f39c12"; strengthText.textContent = "Medium"; break;
                case 4:
                    strengthBar.style.width = "100%"; strengthBar.style.background = "#2ecc71"; strengthText.textContent = "Strong"; break;
            }
        });

        document.getElementById("signup-form").addEventListener("submit", function(e){
            let isValid = true;
            const name = document.getElementById("signup-name");
            const email = document.getElementById("signup-email");
            const password = document.getElementById("signup-password");
            const confirm = document.getElementById("signup-confirm");
            const terms = document.getElementById("terms");

            document.getElementById("signup-name-error").textContent = "";
            document.getElementById("signup-email-error").textContent = "";
            document.getElementById("signup-password-error").textContent = "";
            document.getElementById("signup-confirm-error").textContent = "";

            if(!name.value.trim()){ document.getElementById("signup-name-error").textContent = "Name required"; isValid=false; }
            if(!email.value.trim()){ document.getElementById("signup-email-error").textContent = "Email required"; isValid=false; }
            else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)){ document.getElementById("signup-email-error").textContent = "Invalid email"; isValid=false; }
            if(!password.value.trim()){ document.getElementById("signup-password-error").textContent = "Password required"; isValid=false; }
            if(password.value !== confirm.value){ document.getElementById("signup-confirm-error").textContent = "Passwords do not match"; isValid=false; }
            if(!terms.checked){ alert("Agree to Terms"); isValid=false; }

            if(!isValid) e.preventDefault();
        });
    </script>
</body>
</html>
