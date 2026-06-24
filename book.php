<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $traveler_name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $traveler_email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($traveler_name) || empty($traveler_email)) {
        die("The magical energies are unstable. Please fill out all fields.");
    }

    try {
        // Automatically generate a unique Booking ID text string matching your BXXX format
        $generated_id = "B" . mt_rand(100, 999); 
        
        // Generates a random alphanumeric code for tracking reference strings
        $generated_ref = "EK" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $sql = "INSERT INTO booking_details (
                    booking_ID,
                    reference_number, 
                    traveler_name,
                    traveler_email,
                    special_request, 
                    booking_status, 
                    booking_date, 
                    visit_date,
                    ticket_type,
                    children_count,
                    senior_pwd_count,
                    optional_services
                ) VALUES (
                    :booking_id,
                    :ref, 
                    :name,
                    :email,
                    'None', 
                    'Pending', 
                    NOW(), 
                    CURDATE(),
                    'General Admission',
                    '0',
                    '0',
                    'None'
                )";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':booking_id' => $generated_id,
            ':ref'        => $generated_ref,
            ':name'       => $traveler_name,
            ':email'      => $traveler_email
        ]);

        echo "<script>
                alert('✨ Your inquiry spell has been recorded in the database parchment! Reference: " . $generated_ref . "');
                window.location.href = 'index.php';
              </script>";
              
    } catch (\PDOException $e) {
        die("Database spell failed: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>