<?php
if($_SERVER['REQUEST_METHOD'] !='POST' || !isset($_POST['user']) || !isset($_POST['password']) )
{
    echo '请输入用户名与密码';
    echo "<script>window.history.go(-1);</script>";
    exit;
}
$user     = trim( $_POST['user'] );
$password = trim( $_POST['password'] );

if(empty($user) || empty($password) )
{
    echo '请输入用户名与密码';
    echo "<script>window.history.go(-1);</script>";
    exit;
}
setcookie('muser',  $user);
setcookie('mpasswd',$password);
$_COOKIE['muser'] = $user;
$_COOKIE['mpasswd'] = $password;

echo "<script>window.location.href='../index.php'</script>";
exit;










