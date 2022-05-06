<!DOCTYPE html>
<head>
    <title>Ossama Website</title>

</head>
<body>
    <h1>This is a vulnerable website, open your Burp NOW.</h1>
    <h5>NOTE: You should use a web server so you could sign up.</h5>
    <h3>You have only 2 options</h3>
    <ul>
        <li><a href="sign_up.html">Sign UP</a></li><br>
        <li><a href="sign_in.html">Sign IN</a></li><br>
    </ul>

</body>

<?php
    // Process Sign IN
    if (isset($_POST['submin'])){
        $error_message = "<h2 align='center'>Wrong Credentials</h2>";
        $greeting = "<h2 align='center'>Great You Have LOGGED IN Now :)</h2>";
        
        $email = htmlspecialchars( $_POST['email']       );
        $pass  = htmlspecialchars( $_POST['pass']        );
        if (!empty($email) AND !empty($pass)){
            $pass = sha1($pass);
            require("config/db.php"); // have --> $conn
            $query = "SELECT * FROM oss_table WHERE email = '{$email}' AND pass_hashed = '{$pass}'";
            $result = mysqli_query($conn, $query);
            $entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $entries_count = sizeof($entries);
            // if exist --> Error
            if ($entries_count == 1){
                $cooky = base64_encode($email. ":". sha1($pass. "my_salt_that_no_one_know_:)"));
                setcookie('UserAuth', $cooky, time()+120);
                echo $greeting; 
            }
            else{
                echo $error_message;
            }
            mysqli_close($conn);
        }
        else{
            echo "<h1 align='center'>BAD INPUTS</h1>";
        }
    }
    // Process Sign UP
    if (isset($_POST['submup'])){
        $fname = htmlspecialchars( $_POST['fname']       );
        $lname = htmlspecialchars( $_POST['lname']       );
        $user  = htmlspecialchars( $_POST['username']    );
        $email = htmlspecialchars( $_POST['email']       );
        $pass  = htmlspecialchars( $_POST['pass']        );
        // Check DB to register or not
        require("config/db.php"); // have --> $conn
        // check if email or user exist
        $query = "SELECT * FROM oss_table WHERE email = '{$email}' OR username = '{$user}'";
        $result = mysqli_query($conn, $query);
        $entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $entries_count = sizeof($entries);
        // if exist --> Error
        if ($entries_count > 0){
            echo "<br><br><br><h2 align='center'>The username or email is used</h2>";
            mysqli_free_result($result);
        }
        // if not   --> Register
        elseif (!empty($fname) AND !empty($lname) AND !empty($user) AND !empty($email) AND !empty($pass)){
            $query = "INSERT INTO `oss_table` 
                   (`id`, `fname`, `lname`, `username`, `email`, `pass_hashed`, `timeOfCreation`) 
            VALUES (NULL, '{$fname}', '{$lname}', '{$user}', '{$email}', SHA1('{$pass}'), current_timestamp());
            ";
            $result = mysqli_query($conn, $query);
            if ($result){
                echo "<br><br><br><h2 align='center'>Registered Successfully</h2>";
                $cooky = base64_encode($email. ":". sha1(sha1($pass). "my_salt_that_no_one_know_:)"));
                setcookie('UserAuth', $cooky, time()+120);
            }
            else{
                echo "<br><br><br><h2 align='center'>Unuccessful Register, Try again Later.</h2>";
            }
        }
        else{
            echo "<h1>BAD INPUT</h1>";
        }
        
        mysqli_close($conn);
    }
    // Check if there are cookies (User Logged In Before)
    if (isset($_COOKIE['UserAuth']) AND (!isset($_POST['submin']) AND !isset($_POST['submup']))){
        $error_message = "<h2 align='center'>Wrong Credentials</h2>";

        $cooky = base64_decode($_COOKIE['UserAuth']);
        $email = substr($cooky, 0, strpos($cooky, ':'));
        $loggedIn = "<h2 align='center'>You Are Logged In BEFORE :)</h2>";
        require("config/db.php"); // have --> $conn
        $query = "SELECT * FROM oss_table WHERE email = '{$email}'";
        $result = mysqli_query($conn, $query);
        $entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $entries_count = sizeof($entries);
        // if exist --> Error
        $calc_cooky = $email.":". sha1($entries[0]['pass_hashed']. "my_salt_that_no_one_know_:)");
        if ($entries_count == 1 AND   $calc_cooky == $cooky   ){            
            echo $loggedIn; 
        }
        else{
            echo $error_message;
        }
        mysqli_free_result($result);
        mysqli_close($conn);
    }  
?>