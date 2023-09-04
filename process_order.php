<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'C:\Users\drtipu\OneDrive\Documents\order-formapp\PHPMailer\PHPMailer.php';
require 'C:\Users\drtipu\OneDrive\Documents\order-formapp\PHPMailer\SMTP.php';
require 'C:\Users\drtipu\OneDrive\Documents\order-formapp\PHPMailer\Exception.php';

// Function to send an email using Office 365 SMTP
function sendEmail($recipient, $subject, $message) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.office365.com'; // Office 365 SMTP server
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'pensandparable@gmail.com'; // Your Office 365 email address
        $mail->Password = 'Fatima@5958'; // Your Office 365 email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Sender
        $mail->setFrom('pensandparable@gmail.com', 'Fatima'); // Your email address and name

        // Recipient
        $mail->addAddress($recipient); // Recipient's email address

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return false; // Email sending failed
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $product = $_POST["product"];
    $title = $_POST["title"];
    $instructions = $_POST["instructions"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $deadline = $_POST["deadline"];
    $wordCount = $_POST["word-count"];
    $paymentMethod = $_POST["payment-method"];

    // Validate form data
    $errors = validateFormData($product, $title, $instructions, $name, $email, $deadline, $wordCount, $paymentMethod);

    if (empty($errors)) {
        // Process the order
        $orderId = processOrder($product, $title, $instructions, $name, $email, $deadline, $wordCount, $paymentMethod);

        if ($orderId) {
            // Order processed successfully
            sendConfirmationEmail($email, $product, $orderId);
            echo "Order successfully processed. You will receive a confirmation email shortly.";
        } else {
            // Error processing the order
            echo "Error: Order processing failed. Please try again later.";
        }
    } else {
        // Validation errors
        foreach ($errors as $error) {
            echo "Error: " . $error . "<br>";
        }
    }
} else {
    // Handle cases where the form was not submitted properly.
    // You can redirect the user back to the form or display an error message.
    // For now, let's just echo an error message.
    echo "Error: Form was not submitted.";
}

/**
 * Validate form data and return an array of validation errors.
 */
function validateFormData($product, $title, $instructions, $name, $email, $deadline, $wordCount, $paymentMethod) {
    $errors = [];

    // Check if required fields are filled
    if (empty($product) || empty($title) || empty($instructions) || empty($name) || empty($email) || empty($deadline) || empty($wordCount) || empty($paymentMethod)) {
        $errors[] = "All fields are required.";
    }

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    // Validate deadline date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
        $errors[] = "Invalid deadline date format. Please use YYYY-MM-DD.";
    }

    // Ensure word count is a positive number
    if (!is_numeric($wordCount) || $wordCount <= 0) {
        $errors[] = "Word count must be a positive number.";
    }

    return $errors;
}

/**
 * Process the order and return an order ID.
 */
function processOrder($product, $title, $instructions, $name, $email, $deadline, $wordCount, $paymentMethod) {
    // Your order processing logic goes here

    // Example: Generate a unique order ID (you can implement your own logic)
    $orderId = uniqid();

    // Calculate the price based on your logic
    $price = calculatePrice($product, $wordCount);

    // Store order data in a JSON file (as you've described in previous messages)
    storeOrderData($orderId, $product, $title, $instructions, $name, $email, $deadline, $wordCount, $paymentMethod, $price);

    return $orderId;
}

/**
 * Calculate the price based on the selected product and word count.
 */
function calculatePrice($product, $wordCount) {
    // Your pricing calculation logic goes here

    // Example: Calculate the price based on a fixed rate per word
    $pricePerWord = 0.05; // $0.05 per word
    $price = $pricePerWord * $wordCount;

    return $price;
}

/**
 * Store order data in a JSON file.
 */
function storeOrderData($orderId, $product, $title, $instructions, $name, $email, $deadline, $wordCount, $paymentMethod, $price) {
    // Define the file path
    $jsonFilePath = "orders.json";

    // Read existing JSON data from the file, or initialize an empty array if the file doesn't exist yet
    $orders = file_exists($jsonFilePath) ? json_decode(file_get_contents($jsonFilePath), true) : [];

    // Add a new order to the array
    $newOrder = [
        "order_id" => $orderId,
        "product" => $product,
        "title" => $title,
        "instructions" => $instructions,
        "name" => $name,
        "email" => $email,
        "deadline" => $deadline,
        "word_count" => $wordCount,
        "payment_method" => $paymentMethod,
        "price" => $price,
        "status" => "Pending",
        "timestamp" => date("Y-m-d H:i:s"), // Add a timestamp for reference
    ];

    $orders[] = $newOrder;

    // Encode the updated order data as JSON and write it back to the file
    $jsonData = json_encode($orders, JSON_PRETTY_PRINT);
    file_put_contents($jsonFilePath, $jsonData);
}

/**
 * Send a confirmation email to the customer.
 */
function sendConfirmationEmail($email, $product, $orderId) {
    // Your email sending logic goes here

    // Example: Send a simple confirmation email (configure your mail server settings)
    $to = $email;
    $subject = "Order Confirmation";
    $message = "Thank you for placing your order!\n";
    $message .= "Service Type: $product\n";
    $message .= "Order ID: $orderId\n";
    $message .= "We will process your order shortly.";

    // Use the mail() function to send the email (configure your mail server settings)
    $mailSent = mail($to, $subject, $message);

    if (!$mailSent) {
        // Handle email sending failure
        echo "Error: Order processing failed. Please try again later.";
    }
}

// Redirect to the confirmation page
header("Location: confirmation.html");
exit; // Make sure to exit the script after the redirect

// Log an error message (if needed)
$error_message = "Invalid email address: " . $email;
error_log($error_message, 3, "error_log.txt"); // 3 means append to the log file
?>
