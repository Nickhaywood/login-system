<?php



/********* validation functions******/

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




function validation_errors($error_message){

$error_message =<<<DELIMITER

<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <strong>Warning!</strong> $error_message
  </div>
DELIMITER;
return $error_message;

}

  
function email_exists($email){
  $sql = "SELECT id FROM users WHERE email = '$email'";
  $result = query($sql);
  if(row_count($result)==1){
    return true;
  }else{
    return false;
  }
}


function username_exists($username){
  $sql = "SELECT id FROM users WHERE username = '$username'";
  $result = query($sql);
  if(row_count($result)==1){
    return true;
  }else{
    return false;
  }
}








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

      if($password !== $confirm_password){

        $errors[] = "password dont match!";

      }



  if(!empty($errors)){
       foreach($errors as $error){
     
echo validation_errors($error);     
      }
    }
  }
}
?>