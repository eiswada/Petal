<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendFutureLetter($to_email, $sender_name, $pesan, $tanggal_kirim, $color) {
    $color_map = [
        'pink'   => '#f9a8c9',
        'purple' => '#c4b5fd',
        'mint'   => '#6ee7b7',
        'peach'  => '#fdba74',
        'blue'   => '#93c5fd',
        'yellow' => '#fde68a',
    ];
    $bg = $color_map[$color] ?? '#f9a8c9';

    $mail = new PHPMailer(true);

    try {
        // Mailtrap SANDBOX SMTP
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ed0ef8757004e0';
        $mail->Password   = '69902aa7d14a64';
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAutoTLS = true;
        $mail->Port       = 2525;

        // Sender — sandbox accepts any from address
        $mail->setFrom('noreply@petal.app', 'Petal');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = "A letter from your past, " . $sender_name;
        $mail->Body    = "
        <div style='font-family:Georgia,serif;max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);'>
            <div style='background:{$bg};padding:2.5rem 2rem 1.5rem;text-align:center;'>
                <h1 style='font-family:Georgia,serif;font-size:1.8rem;color:#1a1a2e;margin:.5rem 0 0;'>Petal</h1>
                <p style='color:rgba(26,26,46,.6);font-size:.85rem;margin:.25rem 0 0;'>A letter from your past</p>
            </div>
            <div style='padding:2.5rem 2rem;'>
                <p style='color:#6b7280;font-size:.85rem;text-transform:uppercase;letter-spacing:2px;margin:0 0 1rem;'>Dear {$sender_name},</p>
                <div style='background:#faf9f7;border-left:4px solid {$bg};border-radius:8px;padding:1.5rem;font-size:1rem;line-height:1.8;color:#1a1a2e;font-style:italic;'>
                    " . nl2br(htmlspecialchars($pesan)) . "
                </div>
                <p style='color:#6b7280;font-size:.8;margin:1.5rem 0 0;text-align:center;'>
                    Written on " . date('d M Y') . " · Delivered on " . date('d M Y', strtotime($tanggal_kirim)) . "
                </p>
            </div>
            <div style='background:#faf9f7;padding:1rem 2rem;text-align:center;border-top:1px solid #f0e6f6;'>
                <p style='color:#a78bca;font-size:.75rem;margin:0;'>Petal — Leave a light for the future</p>
            </div>
        </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
?>