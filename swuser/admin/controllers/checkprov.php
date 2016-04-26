<?php
if(!isset($_COOKIE['muser'])){
    echo 'NO COOKIE';
   // echo "<script>window.location.href='../login.html'</script>";
    exit;
}