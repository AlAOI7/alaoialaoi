<?php
require_once 'config.php';

function sendVerificationEmail($to, $name, $verification_code) {
    $subject = "رمز التحقق - Be Pretty";
    
    $message = "
    <!DOCTYPE html>
    <html lang='ar' dir='rtl'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
            .code { background: #f8f9fa; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; color: #dc3545; border: 2px dashed #dc3545; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Be Pretty</h1>
                <p>تأكيد بريدك الإلكتروني</p>
            </div>
            <div style='padding: 20px;'>
                <h2>مرحباً $name,</h2>
                <p>شكراً لتسجيلك في Be Pretty. يرجى استخدام رمز التحقق أدناه لتأكيد بريدك الإلكتروني:</p>
                <div class='code'>$verification_code</div>
                <p>هذا الرمز صالح لمدة 30 دقيقة.</p>
                <p>إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.</p>
            </div>
            <div class='footer'>
                <p>© 2024 Be Pretty. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // إعدادات البريد
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Be Pretty <dotnetala@gmail.com>" . "\r\n";
    $headers .= "Reply-To: dotnetala@gmail.com" . "\r\n";
    
    // إرسال البريد
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        // إذا فشل mail()، جرب استخدام PHPMailer كبديل
        return sendWithPHPMailer($to, $name, $verification_code);
    }
}

// دالة بديلة باستخدام PHPMailer (إذا كان متوفراً)
function sendWithPHPMailer($to, $name, $verification_code) {
    try {
        // إذا كان PHPMailer مثبتاً، استخدمه
        if (file_exists('PHPMailer/PHPMailer.php')) {
            require_once 'PHPMailer/PHPMailer.php';
            require_once 'PHPMailer/SMTP.php';
            require_once 'PHPMailer/Exception.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->Port = SMTP_PORT;
            $mail->SMTPSecure = 'tls';
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
            $mail->addAddress($to, $name);
            
            $mail->isHTML(true);
            $mail->Subject = "رمز التحقق - Be Pretty";
            
            $message = "
            <!DOCTYPE html>
            <html lang='ar' dir='rtl'>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
                    .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { text-align: center; background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
                    .code { background: #f8f9fa; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; color: #dc3545; border: 2px dashed #dc3545; margin: 20px 0; border-radius: 5px; }
                    .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Be Pretty</h1>
                        <p>تأكيد بريدك الإلكتروني</p>
                    </div>
                    <div style='padding: 20px;'>
                        <h2>مرحباً $name,</h2>
                        <p>شكراً لتسجيلك في Be Pretty. يرجى استخدام رمز التحقق أدناه لتأكيد بريدك الإلكتروني:</p>
                        <div class='code'>$verification_code</div>
                        <p>هذا الرمز صالح لمدة 30 دقيقة.</p>
                        <p>إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.</p>
                    </div>
                    <div class='footer'>
                        <p>© 2024 Be Pretty. جميع الحقوق محفوظة.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->Body = $message;
            
            return $mail->send();
        }
    } catch (Exception $e) {
        error_log("فشل إرسال البريد: " . $e->getMessage());
    }
    
    return false;
}
?>