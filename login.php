<?php
// Include config file
require_once 'config.php';

session_start();
if(isset($_SESSION['username']) || !empty($_SESSION['username'])){
  header("location: index.html");
  exit;
}
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = 'Please enter username.';
    } else{
        if (strlen($username) > 512){
             $username = "Invalid username";
         } else {
            $username = trim($_POST["username"]);
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $username = htmlentities($username);
         }
    }
    
    // Check if password is empty
    if(empty(trim($_POST['password']))){
        $password_err = 'Please enter your password.';
    } else{
        $password = trim($_POST['password']);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT * FROM Users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $userid, $username, $hashed_password, $admin);
                    if(mysqli_stmt_fetch($stmt)){
                        if(sha1($password) == $hashed_password){
                            /* Password is correct, so start a new session and
                            save the username to the session */
                            session_start();
                            //set admin variable
                            //$sql = "SELECT admin FROM Users WHERE username = ".$username;
                            //$result = $link->query($sql);
                            //if ($result->num_rows > 0) {
                                //while($row = $result->fetch_assoc()) {
                                    $_SESSION['admin'] = $admin;
                                //}

                            $_SESSION['username'] = $username;      
                            header("location: admin.php");
                        } else{
                            // Display an error message if password is not valid
                            $password_err = 'The password you entered was not valid.';
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = 'No account found with that username.';
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>
<div><p><a href="index.html">Home</a>|<a href="admin.php">Admin Site (PHP)</a>|<a href="login.php">Log In</a>|<a href="logout.php">Log Out</a></p></div>
    <div class="wrapper">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username"class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <!--<p>Don't have an account? <a href="register.php">Sign up now</a>.</p>-->
        </form>
    </div>    
</body>
</html>