<?php
namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/mail.php';

class Mail {
    public static function send($to, $subject, $body, $isHtml = true) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port       = MAIL_PORT;

            // Recipients
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendBookingConfirmation($email, $userName, $bookingDetails) {
        $subject = "Reservation Confirmed: " . $bookingDetails['car_name'] . " (#" . $bookingDetails['id'] . ")";
        
        // Premium HTML Template (Table-based for maximum compatibility)
        $body = "
        <center>
        <div style=\"background-color: #0f172a; color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: auto; border-radius: 16px; overflow: hidden;\">
            <!-- Header -->
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-bottom: 1px solid #334155;\">
                <tr>
                    <td style=\"padding: 40px 20px; text-align: center;\">
                        <h1 style=\"margin: 0; font-size: 24px; letter-spacing: 4px; text-transform: uppercase; color: #fbbf24;\">RentRide</h1>
                        <p style=\"color: #94a3b8; margin-top: 10px; font-size: 14px; letter-spacing: 1px;\">EXECUTIVE FLEET</p>
                    </td>
                </tr>
            </table>

            <!-- Body -->
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                <tr>
                    <td style=\"padding: 40px 30px;\">
                        <h2 style=\"font-weight: 300; font-size: 28px; margin-top: 0; color: #ffffff;\">Reservation Confirmed</h2>
                        <p style=\"color: #94a3b8; line-height: 1.6;\">Dear " . htmlspecialchars($userName) . ", your request for the <strong>" . htmlspecialchars($bookingDetails['car_name']) . "</strong> has been finalized. We are preparing your vehicle for an exceptional driving experience.</p>
                        
                        <!-- Details Card -->
                        <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"20\" style=\"background-color: #1e293b; border-radius: 12px; margin: 30px 0; border: 1px solid #334155;\">
                            <tr>
                                <td>
                                    <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                                        <tr>
                                            <td style=\"color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;\">Booking ID</td>
                                            <td align=\"right\" style=\"font-weight: 600; color: #fbbf24;\">#" . htmlspecialchars($bookingDetails['id']) . "</td>
                                        </tr>
                                        <tr><td colspan=\"2\" style=\"height: 1px; background: #334155; margin: 15px 0;\"></td></tr>
                                        <tr><td colspan=\"2\" style=\"height: 15px;\"></td></tr>
                                        <tr>
                                            <td style=\"color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;\">Pick-up Date</td>
                                            <td align=\"right\" style=\"font-weight: 400; color: #f8fafc;\">" . htmlspecialchars($bookingDetails['start_date']) . "</td>
                                        </tr>
                                        <tr><td colspan=\"2\" style=\"height: 10px;\"></td></tr>
                                        <tr>
                                            <td style=\"color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;\">Return Date</td>
                                            <td align=\"right\" style=\"font-weight: 400; color: #f8fafc;\">" . htmlspecialchars($bookingDetails['end_date']) . "</td>
                                        </tr>
                                        <tr><td colspan=\"2\" style=\"height: 15px;\"></td></tr>
                                        <tr><td colspan=\"2\" style=\"height: 1px; background: #fbbf24; opacity: 0.3;\"></td></tr>
                                        <tr><td colspan=\"2\" style=\"height: 15px;\"></td></tr>
                                        <tr>
                                            <td style=\"color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;\">Total Investment</td>
                                            <td align=\"right\" style=\"font-weight: 600; font-size: 18px; color: #fbbf24;\">₹" . number_format($bookingDetails['total_price'], 2) . "</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                            <tr>
                                <td align=\"center\" style=\"padding-top: 20px;\">
                                    <a href=\"#\" style=\"background-color: #fbbf24; color: #0f172a; padding: 16px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; display: inline-block;\">MANAGE RESERVATION</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"background-color: #0f172a; border-top: 1px solid #334155;\">
                <tr>
                    <td style=\"padding: 30px; text-align: center;\">
                        <p style=\"color: #475569; font-size: 12px; margin: 0;\">&copy; 2026 RentRide Executive. All rights reserved.</p>
                        <p style=\"color: #475569; font-size: 12px; margin-top: 5px;\">Priority Support: cars.rentride@gmail.com</p>
                    </td>
                </tr>
            </table>
        </div>
        </center>
        ";

        return self::send($email, $subject, $body);
    }
}
?>
