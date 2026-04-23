<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - Be Pretty</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>مرحباً <?php echo $_SESSION['user_name']; ?></h1>
        <p>هذه هي لوحة تحكم المستخدم العادي</p>
        <a href="logout.php" class="btn btn-danger">تسجيل الخروج</a>
    </div>
</body>
</html>