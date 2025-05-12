<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "PHPMailer/src/PHPMailer.php";
require_once "PHPMailer/src/SMTP.php";
require_once "PHPMailer/src/Exception.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try{

   
    // Get form data
    $data = [
        'fullname' => $_POST['fullname'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'service' => $_POST['service'] ?? '',
        'message' => $_POST['message'] ?? ''
    ];

    // Send to Google Sheets
    $googleSheetsResponse = sendToGoogleSheets($data);
    
    // Send email
    $emailSent = sendEmail($data);

    $response = [
            'success' => $googleSheetsResponse,
            'email_sent' => $emailSent,
            'message' => $googleSheetsResponse ? 'Data submitted successfully' : 'Google Sheets submission failed'
        ];
        
        if (!$googleSheetsResponse) {
            $response['error'] = 'Failed to send data to Google Sheets';
        }
        
        echo json_encode($response);
        exit;
     }
     catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
        exit;
    // Return response
    
}
}
function sendToGoogleSheets($data) {
    $url = 'https://script.google.com/macros/s/AKfycbzVVrUq0OWMdas99-TFKwn-WqEjskVPTH9-pDjtdhk3AKW9LO1EZ3OjsdTTAlZ3ODfq/exec';
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result !== false;
}

function sendEmail($data) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'senderphp6@gmail.com';
        $mail->Password = 'ypksaywbhhsiaabs';
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('senderphp6@gmail.com');
        $mail->addAddress('bablu3fitech@gmail.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Enquiry From website';

        $bodyofmsg = '<table style="width:100%; border:1px solid black">
                        <tr><td><b><center>Enquiry From Website</center></b></td></tr>
                        <tr><td>Name</td><td>'. htmlspecialchars($data['fullname']) .'</td></tr>
                        <tr><td>Email</td><td>'. htmlspecialchars($data['email']) .'</td></tr>
                        <tr><td>Phone</td><td>'. htmlspecialchars($data['phone']) .'</td></tr>
                        <tr><td>Service</td><td>'. htmlspecialchars($data['service']) .'</td></tr>
                        <tr><td>Message</td><td>'. htmlspecialchars($data['message']) .'</td></tr>
                      </table>';

        $mail->Body = $bodyofmsg;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>