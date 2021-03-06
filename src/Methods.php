<?php

require_once '../src/Database/DBOperations.php';
require_once '../src/MBFunctions.php';
require_once '../src/RPC/JSON_RPC.php';
require_once '../src/errors.php';
require_once '../src/Check/regex.php';


class Methods {

    private DBOperations $db;
    private MBFunctions $mb;
    private JSON_RPC $rpc;

    private PHPMail $mail;

    public function __construct() {
        $this->db = new DBOperations();
        $this->mb = new MBFunctions();
        $this->rpc = new JSON_RPC();
        $this->mail = new PHPMail();
    }

    private function error($error) {
        return $this->rpc->makeErrorResponse(__CLASS__, $error, debug_backtrace()[1]['function']);
    }

    public function addRoom($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->addRoom($data));
    }

    public function login($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->login($data));
    }

    public function register($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->register($data));
    }

    public function updateRoom($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->updateRoom($data));
    }
    public function deleteRoom($data)
    {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->deleteRoom($data));
    }

    public function addTag($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->addTag($data));
    }

    public function deleteTag($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->deleteTag($data));
    }

    public function giveTag($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->giveTag($data));
    }

    public function removeTag($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->removeTag($data));
    }

    public function addEvent($data) {
        $db = $this->db;

        if ($db->addEvent($data)) {
            $params = [
                'eventName' => '',
                'eventDesc' => '',
                'startTime' => '',
                'endTime' => '',
                'address' => '',
                'organizer' => '',
                'participants' => '',
            ];
            $IsSendToOrg = false;
            $IsSendToParts = false;

            return $this->rpc->makeResultResponse(
                $this->mail->sendEventToParticipants($params, $IsSendToOrg, $IsSendToParts)
            );
        }

        return $this->error(ERR_INTERNAL);
    }

    public function updateEvent($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->updateEvent($data));
    }

    public function deleteEvent($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->deleteEvent($data));
    }

    public function addReview($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->addReview($data));
    }

    public function deleteReview($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->deleteReview($data));
    }

    public function addMember($data) {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->addMember($data));
    }

    public function getRooms() {
        $db = $this->db;

        return $this->rpc->makeResultResponse($db->getRooms());
    }

}
