<?php

include "setup.php";

function get_daily_practices() {
    global $db;

    $query = "select concat(start_time, ' - ', end_time) as time, location_name, performance.name from practice, location, performance, timeslot
              where practice.location_id = location.location_id
              and practice.performance_id = performance.performance_id
              and practice.time_id = timeslot.time_id
              and date = CURDATE()
              order by start_time";

    try {
        $statement = $db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();
        return $result;
    }
    catch (PDOException $e) {
        echo $e;
        exit();
    }
}

function get_timeslots() {
    global $db;

    $query = "select concat(start_time, ' - ', end_time) as time, start_time, end_time, time_id 
              from timeslot order by start_time";

    try {
        $statement = $db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();
        return $result;
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function get_daily_practices_by_time_id($time_id) {
    global $db;

    $query = "select concat(start_time, ' - ', end_time) as time, location_name, performance.name from practice, location, performance, timeslot
              where practice.location_id = location.location_id
              and practice.performance_id = performance.performance_id
              and practice.time_id = timeslot.time_id
              and date = CURDATE()
              and practice.time_id = :time_id
              order by start_time";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":time_id", $time_id);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();
        return $result;
    }
    catch (PDOException $e) {
        echo $e;
        exit();
    }
}

function get_practices_by_date($day, $month, $year) {
    global $db;

    $query = "select concat(start_time, ' - ', end_time) as time, location_name, performance.name from practice, location, performance, timeslot
              where practice.location_id = location.location_id
              and practice.performance_id = performance.performance_id
              and practice.time_id = timeslot.time_id
              and day(date) = :day
              and month(date) = :month
              and year(date) = :year
              order by start_time";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":day", $day);
        $statement->bindValue(":month", $month);
        $statement->bindValue(":year", $year);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();
        return $result;
    }
    catch (PDOException $e) {
        echo $e;
        exit();
    }
}
function get_performances() {
    global $db;

    $query = "select * from performance";

    try {
        $statement = $db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();
        return $result;
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function add_user($email, $password, $first_name, $last_name, $grade) {
    global $db;
    global $auth;

    $user_id = $auth->register($email, $password, $first_name . " " . $last_name);

    $query = "insert into users (email, password, username) values (:email, \"password\", :username); insert into user_info (grade, is_paid) values (:grade, 0)";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":email", $email);
        $statement->bindValue(":username", $first_name . $last_name);
        $statement->bindValue(":grade", $grade);

        $statement->execute();
        $statement->closeCursor();

    }

    catch (PDOException $e) {
        echo $e;
        exit();
    }
}