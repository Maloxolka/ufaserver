<?php

require_once '../../src/ConfigLoader.php';
require_once '../../src/Log/LogWriter.php';
require_once '../../src/errors.php';


class DBOperations {

    private PDO $conn;
    private LogWriter $log;
    private ConfigLoader $config;

    public function __construct() {
        $this->config = new ConfigLoader;
        $this->log = new LogWriter();
        $this->conn = new PDO(
            "mysql:dbname=".$this->config->get('db.name')."; host=".$this->config->get('db.host'),
            $this->config->get('db.user'),
            $this->config->get('db.pass')
        );
        $this->conn->exec("SET NAMES 'utf-8'");
        $this->conn->exec("SET CHARACTER SET 'utf8'");
    }

    private function error($error): void {
        $this->log->logEntry(__CLASS__, debug_backtrace()[1]['function'], $error);
    }

    public function getHash($password) {
        return password_hash($password."621317", PASSWORD_BCRYPT, ['cost' => 14]);
    }

    public function verifyHash($password, $hash): bool {
        return password_verify($password, $hash);
    }

    public function parseDate($date) {
        return $date[8].$date[9].".".$date[5].$date[6];
    }

    public function login($data) {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':email' => $data->email
            )
        );

        $raw = $query->fetchObject();

        if($this->verifyHash($data->pass."621317", $raw->en_p)) {
            $result["id"] = $raw->id;
            $result["email"] = $raw->email;
            $result["type"] = $raw->type;
            $result["name"] = $raw->name;
            $result["sname"] = $raw->sname;
            return $result;
        }
        return false;
    }


    public function register($data) {
        $en_p= $this->getHash($data->pass);

        $sql = 'INSERT INTO `users` SET email = :email, en_p = :en_p, name = :name, sname = :sname';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':en_p' => $en_p,
                ':email' => $data->email,
                ':name' => $data->name,
                ':sname' => $data->sname
            )
        );

        if ($query) {
            $id_user = $this->conn->lastInsertId();

            if (!empty($id_user)) {
                return true;
            }

            $this->error(ERR_CANT_ADD_USER);
            return false;
        }

        $this->error(ERR_QUERY_USERS);
        return false;
    }

    public function addRoom($data) {
        $sql = 'INSERT INTO rooms SET number = :number, floor = :floor, type = :type, area = :area, room_limit = :room_limit';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':number' => $data->number,
                ':floor' => $data->floor,
                ':type' => $data->type,
                ':area' => $data->area,
                ':room_limit' => $data->room_limit,
            )
        );

        $sql = 'SELECT * FROM rooms WHERE number = :number AND floor = :floor AND type = :type AND area = :area AND room_limit = :room_limit';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':number' => $data->number,
                ':floor' => $data->floor,
                ':type' => $data->type,
                ':area' => $data->area,
                ':room_limit' => $data->room_limit,
            )
        );

        $raw = $query->fetchObject();
        $container["id_room"] = $raw->id;

        $tags = $data->tags;
        $count = count($tags);

        for ($i = 0; $i<$count; $i++) {
            $container["tag"] = $tags[$i];
            $this->giveTag((object) $container);
        }

        if ($query) {
            return true;
        }

        return false;
    }

    public function updateRoom($data) {
        $sql = 'UPDATE rooms SET number = :number, floor = :floor, type = :type, area = :area, room_limit = :room_limit WHERE id = :id';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':number' => $data->number,
                ':floor' => $data->floor,
                ':type' => $data->type,
                ':area' => $data->area,
                ':room_limit' => $data->room_limit,
                ':id' => $data->id_room
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function deleteRoom($data) {
        $sql = 'DELETE FROM rooms WHERE id = :id';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id' => $data->id_room
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function addTag($data) {
        $sql = 'INSERT INTO tags SET tag = :tag';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':tag' => $data->tag
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function deleteTag($data) {
        $sql = 'DELETE FROM tags WHERE tag = :tag';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':tag' => $data->tag
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function changeTag($data) {
        $sql = 'UPDATE tags SET tag = "'.$data->new_tag.'" WHERE tag = :tag';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':tag' => $data->tag
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function giveTag($data) {
        $sql = 'SELECT id FROM tags WHERE tag = :tag';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':tag' => $data->tag
            )
        );

        $raw = $query->fetchObject();
        $tag = $raw->id;

        $sql = 'INSERT INTO room_tags SET id_tag = :id_tag, id_room = :id_room';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_tag' => $tag,
                ':id_room' => $data->id_room
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function removeTag($data) {
        $sql = 'SELECT id FROM tags WHERE tag = :tag';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':tag' => $data->tag
            )
        );

        $raw = $query->fetchObject();
        $tag = $raw->id;

        $sql = 'DELETE FROM room_tags WHERE id_tag = :id_tag AND id_room = :id_room';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_tag' => $tag,
                ':id_room' => $data->id_room
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function addEvent($data) {

        $sql = 'SELECT * FROM events1 WHERE id_room = :id_room AND start > "'.date('Y-m-d H:i:s').'"';
        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_room' => $data->id_room
            )
        );

        $table = $query->fetchAll();

        $flag = 0;
        $count = count($table);

        for ($i = 0; $i<$count; $i++) {
            if (strtotime($data->start) >= strtotime($table[$i]["end"]) || strtotime($data->end) <= strtotime($table[$i]["start"])) {

            } else {
                $flag = 1;
            }
        }

        if ($flag != 1) {
            $start = date_format(date_create($data->start), 'Y-m-d H:i:s');
            $end = date_format(date_create($data->end), 'Y-m-d H:i:s');
            $sql = 'INSERT INTO events1 SET id_user = :id_user, id_room = :id_room, start = :start, end = :end, info = :info, description = :description';

            $query = $this->conn->prepare($sql);
            $query->execute(
                array(
                    ':id_user' => $data->id_user,
                    ':id_room' => $data->id_room,
                    ':start' => $start,
                    ':end' => $end,
                    ':info' => $data->info,
                    ':description' => $data->description
                )
            );

            if ($query) {
                return true;
            }

            return false;
        }
        return false;
    }

    public function updateEvent($data) {

        $sql = 'SELECT * FROM events1 WHERE id = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id' => $data->id_event
            )
        );

        $raw = $query->fetchObject();
        $id_room = $raw->id_room;

        $sql = 'SELECT * FROM events1 WHERE NOT (id = :id) AND id_room = :id_room AND start > "'.date('Y-m-d H:i:s').'"';
        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id' => $data->id_event,
                ':id_room' => $id_room
            )
        );

        $table = $query->fetchAll();

        $flag = 0;
        $count = count($table);

        for ($i = 0; $i<$count; $i++) {
            if (strtotime($data->start) >= strtotime($table[$i]["end"]) || strtotime($data->end) <= strtotime($table[$i]["start"])) {

            } else {
                $flag = 1;
            }
        }

        if ($flag != 1) {
            $start = date_format(date_create($data->start), 'Y-m-d H:i:s');
            $end = date_format(date_create($data->end), 'Y-m-d H:i:s');
            $sql = 'UPDATE events1 SET start = :start, end = :end, info = :info, description = :description  WHERE id = :id';

            $query = $this->conn->prepare($sql);
            $query->execute(
                array(
                    ':id' => $data->id_event,
                    ':start' => $start,
                    ':end' => $end,
                    ':info' => $data->info,
                    ':description' => $data->description
                )
            );

            if ($query) {
                return true;
            }

            return false;
        }
        return false;
    }

    public function deleteEvent($data) {
        $sql = 'DELETE FROM events1 WHERE id = :id';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id' => $data->id_event
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function addReview($data) {
        $sql = 'INSERT INTO reviews SET id_room = :id_room, description = :description';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_room' => $data->id_room,
                ':description' => $data->description
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function deleteReview($data) {
        $sql = 'DELETE FROM reviews WHERE id = :id';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id' => $data->id_review
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function addMember($data) {
        $sql = 'INSERT INTO members SET id_event = :id_event, name = :name, sname = :sname, email = :email';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_event' => $data->id_event,
                ':name' => $data->name,
                ':sname' => $data->sname,
                ':email' => $data->email,
            )
        );

        if ($query) {
            return true;
        }

        return false;
    }

    public function getRooms($data) {
        $tags = $data->tags;
        $type = $data->type;
        $floor = $data->floor;
        $left_lim = $data->left_lim;
        $right_lim = $data->right_lim;
        $filter = $data->filter;
        $page = $data->page;

        $count = count($tags);
        $where_sql = '';
        if ($count > 0) {
            $where_sql .= 'tag IN ( "'.$tags[0].'" ';

            for ($i = 1; $i < $count; $i++) {
                $where_sql .= ',"'.$tags[$i].'" ';
            }

            $where_sql .= ') AND ';
        }
        $where_sql .= 'type = "'. $type . '" AND ';
        $where_sql .= 'floor = '. $floor . ' AND ';
        $where_sql .= 'room_limit >= '. $left_lim . ' AND ';
        $where_sql .= 'room_limit <= '. $right_lim . ' ';

        $sql = 'SELECT id_room, floor, type, number, area FROM room_tags 
                INNER JOIN rooms ON room_tags.id_room = rooms.id
                INNER JOIN tags ON room_tags.id_tag = tags.id
                WHERE '.$where_sql.'
                GROUP BY id_room
                HAVING COUNT(*) >= "'.$count.'"
                ORDER BY '.$filter.'
                LIMIT 10 OFFSET '.(($page-1)*10).' ';

        $query = $this->conn->prepare($sql);
        $query->execute();

        $result = $query->fetchAll();

        if ($query) {
            return $result;
        }

        return false;
    }

    public function getAllTags() {
        $sql = 'SELECT tag FROM tags';

        $query = $this->conn->prepare($sql);
        $query->execute();

        if ($query) {
            return $query->fetchAll();
        }

        return false;
    }

    public function getMyEvents($data) {
        $sql = 'SELECT * FROM events1
                INNER JOIN users ON events1.id_user = users.id
                INNER JOIN rooms ON events1.id_room = rooms.id
                WHERE users.id = :id_user';


        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_user' => $data->id_user
            )
        );

        $result = $query->fetchAll();

        $count = count($result);
        for($i = 0; $i<$count; $i++) {

            $sql = 'SELECT * FROM members WHERE id_event = :id_event';

            $query = $this->conn->prepare($sql);
            $query->execute(
                array(
                    ':id_event' => $result[$i][0]
                )
            );

            $result[$i]["members"] = $query->fetchAll();
            $result[$i]["start"] = date_format(date_create($result[$i]["start"]), 'j F Y H:i');
            $result[$i]["end"] = date_format(date_create($result[$i]["end"]), 'j F Y H:i');
        }

        if ($query) {
            return $result;
        }

        return false;
    }

    public function getAsMemberEvents($data) {
        $sql = 'SELECT * FROM users WHERE id = :id';


        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id' => $data->id_user
            )
        );

        $raw = $query->fetchObject();

        $sql = 'SELECT id_event FROM members WHERE email = :email';

        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':email' => $raw->email
            )
        );

        $raw = $query->fetchAll();

        $result = $raw;
        $count = count($raw);
        for($i = 0; $i<$count; $i++) {

            $sql = 'SELECT * FROM events1
                INNER JOIN users ON events1.id_user = users.id
                INNER JOIN rooms ON events1.id_room = rooms.id
                WHERE events1.id = :id_event';

            $query = $this->conn->prepare($sql);
            $query->execute(
                array(
                    ':id_event' => $raw[$i]["id_event"]
                )
            );

            $buf = $query->fetchAll();
            $result[$i] = $buf[0];
            $result[$i]["start"] = date_format(date_create($result[$i]["start"]), 'j F Y H:i');
            $result[$i]["end"] = date_format(date_create($result[$i]["end"]), 'j F Y H:i');

            $sql = 'SELECT * FROM members WHERE id_event = :id_event';

            $query = $this->conn->prepare($sql);
            $query->execute(
                array(
                    ':id_event' => $result[$i][0]
                )
            );

            $result[$i]["members"] = $query->fetchAll();
        }

        if ($query) {
            return $result;
        }

        return false;
    }

    public function getMonthRoomEvents($data) {
        $sql = 'SELECT * FROM events1 
                INNER JOIN users ON events1.id_user = users.id
                INNER JOIN rooms ON events1.id_room = rooms.id
                WHERE id_room = :id_room AND start > "'.date('Y-m-d H:i:s').'" AND end < "'.date('Y-m-d H:i:s', strtotime("+4 week")).'"';
        $query = $this->conn->prepare($sql);
        $query->execute(
            array(
                ':id_room' => $data->id_room
            )
        );

        $table = $query->fetchAll();
        $count = count($table);
        for($i = 0; $i<$count; $i++) {
            $sql = 'SELECT * FROM members WHERE id_event = :id_event';

            $query = $this->conn->prepare($sql);
            $query->execute(
                array(
                    ':id_event' => $table[$i][0]
                )
            );

            $table[$i]["members"] = $query->fetchAll();

            $table[$i]["start"] = date_format(date_create($table[$i]["start"]), 'j F Y H:i');
            $table[$i]["end"] = date_format(date_create($table[$i]["end"]), 'j F Y H:i');
        }

        if ($query) {
            return $table;
        }

        return false;
    }
}
