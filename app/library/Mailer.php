<?php
namespace Dcore\Library;

//require __DIR__ . "/../vendor/autoload.php";


use Exception;

class Mailer
{
    public static function send($to, $subject, $content, $optional = [])
    {
        global $config;
        $mailer_info = $config->mailer;
        try {
            $curl = curl_init();
            $dataPost = [
                "host" => $mailer_info->host,
                "port" => $mailer_info->port,
                "username" => $mailer_info->username,
                "password" => $mailer_info->password,
                "secure" => $mailer_info->secure,
                "from_title" => $mailer_info->from_title,
                "from_email" => $mailer_info->from_email,
                "to" => $to,
                "subject" => $subject,
                "text" => "",
                "html" => $content
            ];
            if ($optional['debug']) $dataPost['debug'] = $optional['debug'];
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://mailer.notresponse.com/mailer.php",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $dataPost,
                /*CURLOPT_HTTPHEADER => array(
                    "content-type: application/json",
                ),*/
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return false;
            } else {
                return $response;
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            die;
        }

        /*global $config;
        $mailer_info = $config->mailer;
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            $host = $mailer_info->host;
            $username = $mailer_info->username;
            $password = $mailer_info->password;

            $from = $mailer_info->from_email;
            //Server settings
            $mail->SMTPDebug = isset($optional['debug']) ? $optional['debug'] : 0;                                 // Enable verbose debug output
            //$mail->SMTPDebug = 1;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $host;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $username;                 // SMTP username
            $mail->Password = $password;                           // SMTP password
            $mail->SMTPSecure = $mailer_info->secure;                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $mailer_info->port;                                    // TCP port to connect to
            $mail->SMTPAutoTLS = false;
            $mail->smtpConnect(
                array(
                    "ssl" => array(
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true
                    )
                )
            );
            //Recipients
            $mail->setFrom($from, $mailer_info->from_title);
            $mail->addAddress($to);     // Add a recipient

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $content;
            $mail->send();
            return ['success' => 1, 'value' => $mail];
        } catch (Exception $e) {
            return ['success' => 0, 'value' => $mail->ErrorInfo];
        }*/
    }


    public static function sendTest($to, $subject, $content, $optional = [])
    {
        global $config;
        $mailer_info = $config->mailer;
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            $host = $mailer_info->host;
            $username = $mailer_info->username;
            $password = $mailer_info->password;

            $from = $mailer_info->from_email;
            //Server settings
            $mail->SMTPDebug = isset($optional['debug']) ? $optional['debug'] : 0;                                 // Enable verbose debug output
            //$mail->SMTPDebug = 1;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $host;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $username;                 // SMTP username
            $mail->Password = $password;                           // SMTP password
            $mail->SMTPSecure = $mailer_info->secure;                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $mailer_info->port;                                    // TCP port to connect to
            $mail->SMTPAutoTLS = false;
            $mail->smtpConnect(
                [
                    "ssl" => [
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true
                    ]
                ]
            );
            //Recipients
            $mail->setFrom($from, $mailer_info->from_title);
            $mail->addAddress($to);     // Add a recipient

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $content;
            $mail->send();
            return ['success' => 1, 'value' => $mail];
        } catch (Exception $e) {
            return ['success' => 0, 'value' => $mail->ErrorInfo];
        }
    }
}
