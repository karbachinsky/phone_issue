#!/usr/bin/env php

<?php
$libraryPath = realpath(dirname(__FILE__) . '/../lib/');
set_include_path($libraryPath);

include "phone.class.php";
include "config.php";


function main()
{
    $options = getopt("i:");

    if (!array_key_exists("i", $options))
        die("Specify input via -i option\n");

    $phoneExtractor = new PhoneExtractor();

    $phones = $phoneExtractor->extract($options["i"]);

    if (!count($phones)) {
        print "No phones were detected";
        exit(0);
    }

    printf("Extracted phones: %s\n", join(" ", $phones));

    print("Connecting to database\n");

    $conn = new mysqli(DBConfig::$dbServer, DBConfig::$dbUser, DBConfig::$dbPass);
    if ($conn->connect_error) {
        die(sprintf("Database connection failed: %s\n",$conn->connect_error));
    }
    $conn->select_db(DBConfig::$dbName);

    print("Searching for clients\n");

    $q = "
      select 
        `order`.id order_id,
        `order`.client_id,
        phone.number
      from `order` 
      left join client 
        on client.id = `order`.client_id
      left join phone 
        on client.id = phone.client_id
      where 
        number = ?
     ";

    if (!$stmt = $conn->prepare($q)) {
        die("Failed to execute preapre statement\n");
    }

    foreach ($phones as $phone) {
        # FIXME: possible memory copy here, should chec how it works in php
        $phone = ltrim($phone, "8");
        if (!$stmt->bind_param("s", $phone)) {
            die(sprintf("Failed to execute query with phone: %s\n", $phone));
        }

        $stmt->execute();

        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            printf("Number: %s Client ID: %d  Order: %d\n",
                $row["number"],
                $row["order_id"],
                $row["client_id"]
            );
        }
    }

    $stmt->close();
    $conn->close();
}

main();

?>