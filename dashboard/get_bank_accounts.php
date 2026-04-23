 <?php
require_once 'config.php';

if (isset($_GET['bank_id'])) {
    $bank_id = (int)$_GET['bank_id'];
    $result = $conn->query("SELECT * FROM bank_accounts WHERE bank_id = $bank_id");
    
    $accounts = [];
    while($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($accounts);
}

$conn->close();
?>