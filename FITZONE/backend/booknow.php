<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and sanitize input
$fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$treatment = isset($_POST['treatment']) ? trim($_POST['treatment']) : '';
$date = isset($_POST['date']) ? trim($_POST['date']) : '';
$time = isset($_POST['time']) ? trim($_POST['time']) : '';
$duration = isset($_POST['duration']) ? trim($_POST['duration']) : '';
$specialRequests = isset($_POST['specialRequests']) ? trim($_POST['specialRequests']) : '';

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($treatment) || empty($date) || empty($time) || empty($duration)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate date (should not be in the past)
$appointmentDate = strtotime($date);
$today = strtotime(date('Y-m-d'));
if ($appointmentDate < $today) {
    echo json_encode(['success' => false, 'message' => 'Appointment date cannot be in the past']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Get user_id if logged in, otherwise set to NULL
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Check if there's already a booking at the same date and time
    $checkStmt = $conn->prepare("SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ?");
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $checkStmt->bind_param("ss", $date, $time);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This time slot is already booked. Please choose another time.']);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();
    
    // Insert booking
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, full_name, email, phone, treatment, appointment_date, appointment_time, duration, special_requests, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $status = 'pending';
    $stmt->bind_param("issssssss", $userId, $fullName, $email, $phone, $treatment, $date, $time, $duration, $specialRequests);
    
    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Appointment booked successfully! We will contact you shortly to confirm your appointment.',
            'booking_id' => $bookingId
        ]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    error_log("Booking error: " . $e->getMessage());
}
?>

