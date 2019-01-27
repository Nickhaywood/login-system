<?php

require './vendor/autoload.php';



/********* validation functions******/


//remove tags
function clean($string){
  return htmlentities($string);
}

function redirect($location){
  return header("location:{$location}");

}

function set_messages($message){
if(!empty($message)){
  $_SESSION['message'] = $message;
}else{
  $message ="";
 }
}

function display_message(){
  if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
  }
}

function token_generator(){
  $token = $_SESSION['token'] = md5(uniqid(mt_rand(),true));

  return $token;
}


function send_email($email, $subject, $msg, $headers){
  return mail($email,$subject,$msg,$headers);
 
/*************alternative email*********/

  //   $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
// try {
//     //Server settings
//     $mail->SMTPDebug = 2;                                 // Enable verbose debug output
//     $mail->isSMTP();                                      // Set mailer to use SMTP
//     $mail->Host = '	smtp.mailtrap.io;smtp2.example.com';  // Specify main and backup SMTP servers
//     $mail->SMTPAuth = true;                               // Enable SMTP authentication
//     $mail->Username = '	98b147ff2a2ae7';                 // SMTP username
//     $mail->Password = '47c676f8b68408';                           // SMTP password
//     $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
//     $mail->Port = 2525;                                    // TCP port to connect to

//     $mail->isHTML(true);                                  // Set email format to HTML
//     $mail->Subject = 'Here is the subject';
//     $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
//     $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

//     $mail->send();
//     echo 'Message has been sent';
// } catch (Exception $e) {
//     echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
// }

}

//validation error message

function validation_errors($error_message){

$error_message =<<<DELIMITER

<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <strong>Warning!</strong> $error_message
  </div>
DELIMITER;
return $error_message;

}

//check to see if email already exist 
function email_exists($email){
  $sql = "SELECT id FROM users WHERE email = '$email'";
  $result = query($sql);
  if(row_count($result)==1){
    return true;
  }else{
    return false;
  }
}

//check if username already exist
function username_exists($username){
  $sql = "SELECT id FROM users WHERE username = '$username'";
  $result = query($sql);
  if(row_count($result)==1){
    return true;
  }else{
    return false;
  }
}

//validate that the user entered the minimum and maximum length of character
function validate_user_registration(){

  $errors =[];
  $min = 3;
  $max = 20;
  
  
  
   if($_SERVER['REQUEST_METHOD'] =="POST"){

    //echo 'it works';
    
    $first_name    = clean($_POST['first_name']);
    $last_name    = clean($_POST['last_name']);
    $username    = clean($_POST['username']);
    $email    = clean($_POST['email']);
    $password    = clean($_POST['password']);
    $confirm_password    = clean($_POST['confirm_password']);

    if(strlen($first_name) < $min){
    $errors[] = "first name must be more than {$min} characters <br>";
    
    }
    if(strlen($last_name) < $min){
      $errors[] = "Last name must be more than {$min} characters <br>";
      
    }

    if(strlen($first_name) > $max){
      $errors[] = "first name cannot be more than {$max} characters <br>";
      
      }
      
      if(strlen($last_name) > $max){
        $errors[] = "Last name cannot be more than {$max} characters <br>";
        
      }

      if(strlen($username) < $min){
        $errors[] = "username must be more than {$min} characters <br>";
        
      }

      if(strlen($username) > $max){
        $errors[] = "username cannot be more than {$max} characters <br>";
        
      }

      if(email_exists($email)){
        $errors[] = "sorry that email is already registered";

      }

      if(username_exists($username)){
        $errors[] = "sorry that username is already registered";

      }

      if($password !== $confirm_password){

        $errors[] = "password dont match!";

      }


//display error found
  if(!empty($errors)){
       foreach($errors as $error){
     
       echo validation_errors($error);     
      }
    }
    
    else{
      if(register_user($first_name, $last_name, $username, $email, $password)) {
        
        set_messages("<p class='bg-success text-center'>please check your email for activation link</p>");
        
        redirect("index.php");
      }
      
    } 
  }

}


/********* register user functions******/

function register_user($first_name, $last_name, $username, $email, $password){
  
  $first_name = escape($first_name);
  $last_name = escape($last_name);
  $username = escape($username);
  $email = escape($email);
  $password = escape($password);
  
    if(email_exists($email)){
    return false;
  }else if (username_exists($username)){
    return false;

  }else{
    $password = md5($password);
    
    $validation_code = md5($username  + microtime());

    $sql = "INSERT INTO users(first_name, last_name, username, email, password, validation_code, active)";
    $sql.= " VALUES('$first_name','$last_name','$username','$email','$password','$validation_code', 0) ";
    $result = query($sql);
    confirm($result);

    $subject = "activate account";
    $msg = "click link below to activate account
    http://localhost/webdev/login_sys/activate.php?email=$email&code=$validation_code ";
    $header = "from:noreply@yourwebsite.com";

    send_email($email, $subject, $msg, $headers);
    
    //return true;
  }
  
}

/********* activate user functions******/
function activate_user(){
  
  if($_SERVER['REQUEST_METHOD']=="GET"){
    if(isset($_GET['email'])){
      $email = clean($_GET['email']);
      $validation_code = clean($_GET['code']);

      $sql = "SELECT id FROM users WHERE email = '".escape($_GET['email'])." 'AND validation_code ='".escape($_GET['code'])."'";
      $result = query($sql);
      confirm($result);
      
        if(row_count($result)==1){
      
      $sql2 = "UPDATE users SET active = 1,validation_code = 0 WHERE email = '".escape($_GET['email'])."' AND validation_code = '".escape($validation_code)."'";
      $result2 = query($sql2);
      confirm($result2); 
      set_message("<p> ACCOUNT ACTIVATED </p>");   
      redirect("login.php");
        }else{
          set_messages("acount could not be activated");
          redirect("login.php");
        }
      
      
         }
      }


  }
 
/***************valitate user login functions*******/

function validate_user_login(){
  $errors = [];
  $min = 3;
  $max = 20;
  if($_SERVER['REQUEST_METHOD'] == "POST"){
      
    $email    = clean($_POST['email']);
    $password    = clean($_POST['password']);

    if(empty($email)){
      $error[] = "email feild cannot be empty";
    }

    if(empty($password)){
      $error[] = "password feild cannot be empty";
    }
    
    
    
    
    if(!empty($errors)){
      foreach($errors as $error){
    
      echo validation_errors($error);     
     }
   }else{
        echo "NO ERRORS";
   }
  
  
  }

}

/***************valitate user login functions*******/


?>