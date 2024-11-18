<?php
require 'database.php';

if($_GET["API_KEY"]=="Flight booker 1.2 plus edition"){
    switch($_GET["action"]){
        case "get":Get();break;
        case "login":Login();break;
        case "book":Book();break;
        default:break;
    }
}
else{
    echo "wrong Key";

}


function Get(){
    global $conn;
//    echo "1";
    switch($_GET["items"]){
        case "airport":
            $records = $conn->prepare('SELECT * FROM airports WHERE airport LIKE :search OR code LIKE :search');
            $records->execute(['search' => '%' . $_GET['search'] . '%']);
            $results = $records->fetchAll(PDO::FETCH_ASSOC);
            if(count($results) > 0) {
                $obj=["status"=>"200","statusDescription"=>"Ok"];
                array_unshift($results,$obj);
                $jsonObj= json_encode($results);
            }
            else {
                $obj=["status"=>"440","statusDescription"=>"No matching airports found"];
                $jsonObj="[".json_encode($obj)."]";
            }
            echo $jsonObj;
            break;
        case "flights":
            $records = $conn->prepare('SELECT * FROM flights WHERE (substring(date_time_depart,1,10) = :date OR substring(date_time_arriv,1,10) = :date) AND airport_from = :from AND airport_to = :to');
            $records->execute(['date' => $_GET['date'], 'from' => $_GET['from'], 'to' => $_GET['to']]);
            $results = $records->fetchAll(PDO::FETCH_ASSOC);
            if(count($results) > 0) {
                $obj=["status"=>"200","statusDescription"=>"Ok"];
                array_unshift($results,$obj);
                $jsonObj= json_encode($results);
            }
            else {
                $obj=["status"=>"460","statusDescription"=>"No matching flights found"];
                $jsonObj="[".json_encode($obj)."]";
            }
            echo $jsonObj;
    }
}

function Login(){
    global $conn;
//    echo "1";
    $records = $conn->prepare('SELECT * FROM passengers WHERE email = :email AND password = :pass');
    $records->execute(['email' => $_GET['email'], 'pass' => md5($_GET['pass'])]);
    $results = $records->fetchAll(PDO::FETCH_ASSOC);
    // var_dump( $results);
    if(count($results) > 0) {
        $token=md5(rand());
        $sql = "UPDATE passengers SET token = :token WHERE email = :email";
        $stmt = $conn->prepare($sql);
//        echo $sql;
        if( $stmt->execute(['token' => $token, 'email' => $_GET['email']]) ) {
            $results[0]["token"]=$token;
        }
        $obj=["status"=>"200","statusDescription"=>"Ok"];
        array_unshift($results,$obj);
        $jsonObj= json_encode($results);
    }
    else {
        $obj=["status"=>"450","statusDescription"=>"No user found"];
        $jsonObj="[".json_encode($obj)."]";
    }
    echo $jsonObj;
}

function Book(){
    global $conn;

    $records = $conn->prepare('SELECT * FROM passengers WHERE id = :id AND token = :token');
    $records->execute(['id' => $_GET['passenger'], 'token' => $_GET['token']]);
    $results = $records->fetchAll(PDO::FETCH_ASSOC);
    // var_dump( $results);
    if(count($results) > 0) {
        $sql = "INSERT INTO bookings (passenger, flight, status) VALUES (:passenger, :flight, 'OK')";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute(['passenger' => $_GET['passenger'], 'flight' => $_GET['flight']])){
            $obj=["status"=>"200","statusDescription"=>"OK"];
            $jsonObj="[".json_encode($obj)."]";
        }
        else{
            $obj=["status"=>"460","statusDescription"=>"problem with the booking"];
            $jsonObj="[".json_encode($obj)."]";

        }
    }
    else {
        $obj=["status"=>"470","statusDescription"=>"User token problem"];
        $jsonObj="[".json_encode($obj)."]";
    }
    echo $jsonObj;
}


?>


