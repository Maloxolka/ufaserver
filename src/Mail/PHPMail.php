<?php


use PHPMailer\PHPMailer\PHPMailer;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class PHPMail {

    private LogWriter $log;
    private ConfigLoader $config;

    public function __construct() {
        $this->config = new ConfigLoader;
        $this->log = new LogWriter();
    }

    public function sendEventToParticipants($params, $IsSendToOrg = false, $IsSendToParts = false): bool {
        $eventName = $params['eventName'];
        $eventDesc = $params['eventDesc'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $address = $params['address'];
        $organizer = $params['organizer'];
        $participants = $params['participants'];


        $event = Event::create()
            ->name($eventName)
            ->description($eventDesc)
            ->withTimezone()
            //                ->startsAt(new DateTime('21 March 2021 15:00'))
            //                ->endsAt(new DateTime('21 March 2021 17:40'))
            ->startsAt($startTime)
            ->endsAt($endTime)
            ->address($address)
            ->organizer($organizer['email'], $organizer['name']);

        foreach ($participants as $participant) {
            if (!empty($participant['email']) && !empty($participant['name'])) {
                $event->attendee($participant['email'], $participant['name']);
            }
        }

        $cal = Calendar::create()
            ->name('UfaApi')
            ->event($event)
            ->get();

        if (empty($cal)) {
            $this->log->logEntry(__CLASS__, __METHOD__, "iCal is empty");
            return false;
        }


        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';

        $mail->SMTPDebug = false;
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->Username = $this->config->get('google_mail.email');
        $mail->Password = $this->config->get('google_mail.password');

        try {
            $mail->smtpConnect(
                array(
                    "ssl" => array(
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true
                    )
                )
            );

            $mail->setFrom(
                $organizer['email'],
                $organizer['name']
            );

            if ($IsSendToParts) {
                foreach ($participants as $participant) {
                    if (!empty($participant['email'])) {
                        $mail->addAddress($participant['email']);
                    }
                }
            }
            if ($IsSendToOrg) {
                $mail->addAddress($organizer['email'], $organizer['name']);
            }

            $mail->Subject = 'Уведомление о новой встречи';
            $mail->isHTML(true);
            $mail->Body = "Здравствуйте, <b>${$organizer['name']}</b> пригласил(а) вас на новую встречу.";
            $mail->AltBody = "Здравствуйте, ${$organizer['name']} пригласил(а) вас на новую встречу.";

            $mail->addStringAttachment($cal, 'ical.ics', 'base64', 'text/calendar');

            if ($mail->send()) {
                return true;
            }

            $this->log->logEntry(__CLASS__, __METHOD__, "Mail's not sent. ".$mail->ErrorInfo);
        } catch(\PHPMailer\PHPMailer\Exception $e) {
            $this->log->logEntry(__CLASS__, __METHOD__, "Mail's not sent. ".$e->getMessage());
        }
        return false;
    }

}