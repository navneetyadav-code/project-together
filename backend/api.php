<?php
// api.php - Handles real-time AJAX requests for Login and Signup
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- SIGNUP LOGIC ---
    if ($action === 'signup') {
        $fullName = $_POST['fullName'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $age = $_POST['age'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $bloodGroup = $_POST['bloodGroup'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = 'patient'; // Default role, you can add a field in frontend to change this

        // Basic validation
        if (empty($fullName) || empty($email) || empty($password) || empty($phone)) {
            echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
            exit;
        }

        // Check if email or phone already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone");
        $stmt->execute(['email' => $email, 'phone' => $phone]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Email or Phone number is already registered."]);
            exit;
        }

        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $insertQuery = "INSERT INTO users (role, full_name, dob, age, gender, blood_group, phone, email, password) 
                        VALUES (:role, :full_name, :dob, :age, :gender, :blood_group, :phone, :email, :password)";
        $stmt = $pdo->prepare($insertQuery);
        
        $success = $stmt->execute([
            'role' => $role,
            'full_name' => $fullName,
            'dob' => $dob,
            'age' => $age,
            'gender' => $gender,
            'blood_group' => $bloodGroup,
            'phone' => $phone,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        if ($success) {
            echo json_encode(["status" => "success", "message" => "Account created successfully! Redirecting..."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create account. Try again."]);
        }
        exit;
    }

    // --- LOGIN LOGIC ---
    if ($action === 'login') {
        $role = $_POST['user_role'] ?? 'patient';
        $contact = $_POST['contact'] ?? ''; // Email or Phone
        $password = $_POST['password'] ?? '';

        if (empty($contact) || empty($password)) {
            echo json_encode(["status" => "error", "message" => "Please enter your credentials."]);
            exit;
        }

        // Fetch user by email or phone AND matching role
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE (email = :contact OR phone = :contact) AND role = :role");
        $stmt->execute(['contact' => $contact, 'role' => $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            echo json_encode(["status" => "success", "message" => "Login successful! Redirecting..."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid credentials or role mismatch."]);
        }
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Invalid Action."]);
}
?>