<?php
$con = mysqli_connect('localhost','root','Yahweh1!','login_db');



function row_count($result){

  return mysqli_num_rows($results);
}



function escape($string){
  global $con;
  mysqli_real_esacpae($con,$string);
}



function query($query){
    global $con;
    return mysqli_query($con,$query);

}

function confirm($result){
    global $con;

    if(!$result){
      die("QUERY FAILED". mysqli_error($con));
    }
  }


    function fetch_array($result){
   global $con;

   return mysqli_fetch_array($result);

}



?>