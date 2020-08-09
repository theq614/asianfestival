<?php

include "setup.php";

function get_daily_practices() {
    global $db;

    $query = "select concat(time_format(start_time, '%h:%i %p'), ' - ', time_format(end_time, '%h:%i %p')) as time, location_name, performance.name from practice, location, performance, timeslot
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

    $query = "select concat(time_format(start_time, '%h:%i %p'), ' - ', time_format(end_time, '%h:%i %p')) as time, start_time, end_time, time_id 
              from timeslot where is_public = 1 order by start_time";

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

    $query = "select concat(time_format(start_time, '%h:%i %p'), ' - ', time_format(end_time, '%h:%i %p')) as time, location_name, performance.name from practice, location, performance, timeslot
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

    $query = "select concat(time_format(start_time, '%h:%i %p'), ' - ', time_format(end_time, '%h:%i %p')) as time, location_name, performance.name from practice, location, performance, timeslot
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

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

function add_user($email, $password, $first_name, $last_name, $grade, $performances) {
    global $db;
    global $auth;

    $user_id = $auth->register($email, $password, $first_name . " " . $last_name);

    $query = "insert into performance_user_xref (performance_id, user_id) values ";
    foreach($performances as $performance) {
        $query .= " (" . $performance . ", " . $user_id . "), ";
    }

    $query = str_lreplace(",", "", $query);

    try {
        $statement = $db->prepare($query);
        $statement->execute();
        $statement->closeCursor();
    }

    catch (PDOException $e) {
        echo $e;
        exit();
    }

    $query = "insert into user_info (user_id, grade) values (" . $user_id . ", " . $grade . ")";

    try {
        $statement = $db->prepare($query);
        $statement->execute();
        $statement->closeCursor();
    }

    catch (PDOException $e) {
        echo $e;
        exit();
    }
}

function get_performances_by_user_id($user_id) {
    global $db;

    $query = "select * from performance, performance_user_xref, country
              where user_id = :user_id 
              and performance.performance_id = performance_user_xref.performance_id
              and performance.country_id = country.country_id";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":user_id", $user_id);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();

        return $result;
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function get_practices_by_performance_id($performance_id) {
    global $db;

    $query = "select location_name, date_format(date, '%M %D, %Y') as formatted_date, concat(time_format(start_time, '%h:%i %p'), ' - ', time_format(end_time, '%h:%i %p')) as time from practice, location, timeslot 
              where performance_id = :performance_id
              and practice.location_id = location.location_id
              and practice.time_id = timeslot.time_id
              and date >= CURDATE()
              order by date
              limit 3";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":performance_id", $performance_id);
        $statement->execute();
        $result = $statement->fetchAll();
        $statement->closeCursor();

        return $result;
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function change_admin_status($user_id, $performance_id, $status) {
    global $db;

    $query = "update performance_user_xref 
              set is_performance_leader = :status 
              where user_id = :user_id 
              and performance_id = :performance_id";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":user_id", $user_id);
        $statement->bindValue(":performance_id", $performance_id);
        $statement->bindValue(":status", $status);
        $statement->execute();
        $statement->closeCursor();

    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function get_user_info($user_id) {
    global $db;

    $query = "select * from user_info where user_id = :user_id";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":user_id", $user_id);
        $statement->execute();
        $result = $statement->fetch();

        $statement->closeCursor();

        return $result;
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function check_performance_leader($user_id, $performance_id) {
    global $db;

    $query = "select * from performance_user_xref where user_id = :user_id and performance_id = :performance_id";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":user_id", $user_id);
        $statement->bindValue(":performance_id", $performance_id);

        $statement->execute();
        $result = $statement->fetch();

        $statement->closeCursor();

        return $result["is_performance_leader"];
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function get_pending_admin_requests() {
    global $db;

    $query = "select * from performance_user_xref, users, performance
              where performance_user_xref.user_id = users.id and performance_user_xref.performance_id = performance.performance_id
              and is_performance_leader = 'P'";

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

function get_locations() {
    global $db;

    $query = "select * from location";

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
//create_custom_timeslot($start_time, $end_time);
//create_practice_from_custom_times($performance_id, $location_id, $date, $start_time, $end_time);

function create_custom_timeslot($start_time, $end_time) {
    global $db;

    $query = "INSERT INTO timeslot (start_time, end_time, is_public)
              (SELECT * FROM (SELECT :start_time, :end_time, 0) AS tmp 
              WHERE NOT EXISTS (select time_id from timeslot where start_time = :start_time and end_time = :end_time))";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":start_time", $start_time);
        $statement->bindValue(":end_time", $end_time);
        $statement->execute();
        $statement->closeCursor();
    } catch(PDOException $e) {
        echo $e;
        exit();
    }

}


//function get_location_id($location_name) {
//    global $db;
//
//    $query = "SELECT location_id FROM location WHERE location_name = " . $location_name;
//
//    try {
//        $statement = $db->prepare($query);
//        $statement->execute();
//        $result = $statement->fetch();
//        $statement->closeCursor();
//
//        return $result;
//    } catch (PDOException $e) {
//        echo $e;
//        exit();
//    }
//}

function get_time_id($start_time, $end_time) {
    global $db;

    $query = "SELECT time_id FROM timeslot WHERE start_time = :start_time AND end_time = :end_time";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":start_time", $start_time);
        $statement->bindValue(":end_time", $end_time);
        $statement->execute();
        $result = $statement->fetch();
        $statement->closeCursor();

        return $result;
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function create_practice_from_custom_times($performance_id, $location_id, $date, $start_time, $end_time) {
    global $db;

    $query = "INSERT INTO practice (location_id, performance_id, time_id, date) 
              VALUES (:location_id, :performance_id, 
                (select time_id
                 from timeslot
                 where start_time = :start_time
                 and end_time = :end_time
                 limit 1), 
              :date)";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":location_id", $location_id);
        $statement->bindValue(":performance_id", $performance_id);
        $statement->bindValue(":date", $date);
        $statement->bindValue(":start_time", $start_time);
        $statement->bindValue(":end_time", $end_time);
        $statement->execute();
        $statement->closeCursor();
    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}

function create_practice($performance_id, $location_id, $date, $time_id) {
    global $db;

    $query = "INSERT INTO practice (location_id, performance_id, time_id, date) VALUES (:location_id, :performance_id, :time_id, :date)";

    try {
        $statement = $db->prepare($query);

        $statement->bindValue(":location_id", $location_id);
        $statement->bindValue(":performance_id", $performance_id);
        $statement->bindValue(":time_id", $time_id);
        $statement->bindValue(":date", $date);

        $statement->execute();
        $statement->closeCursor();
    } catch (PDOException $e) {
        echo $e;
        exit();
    }
}

function delete_practice($practice_id) {
    global $db;

    $query = "DELETE FROM practice WHERE practice_id = :practice_id";

    try {
        $statement = $db->prepare($query);
        $statement->bindValue(":practice_id", $practice_id);
        $statement->execute();
        $statement->closeCursor();
    } catch (PDOException $e) {
        echo $e;
        exit();
    }
}