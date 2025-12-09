<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Email recipient
    $to = "info@mhkts.ae";
    
    // Email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Email content
    $email_content = "<html><body>";
    $email_content .= "<h2>New Contact Form Submission</h2>";
    $email_content .= "<p><strong>Name:</strong> $name</p>";
    $email_content .= "<p><strong>Email:</strong> $email</p>";
    $email_content .= "<p><strong>Phone:</strong> $phone</p>";
    $email_content .= "<p><strong>Subject:</strong> $subject</p>";
    $email_content .= "<p><strong>Message:</strong><br/>$message</p>";
    $email_content .= "</body></html>";
    
    // Send email
    $mail_sent = mail($to, "New Contact Form Submission - $subject", $email_content, $headers);
    
    // Send response back to ajax call
    $response = array();
    if ($mail_sent) {
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your message. We will contact you shortly!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Sorry, there was an error sending your message. Please try again later.';
    }
    
    echo json_encode($response);
    exit;
}
?> 