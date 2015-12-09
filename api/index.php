<?php

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "demo-rob";

    $mysqli = new mysqli($hostname, $username, $password, $database);
    if (isset($_GET['table'])) {

        /** DELETE */
        if (isset($_GET['delete'])) {

            $sql = "delete from `". $_GET['table'] ."` where `id` = '". $_GET['delete']['id'] ."'";
            $result = $mysqli->query($sql);
            echo json_encode(['rows' => $mysqli->affected_rows]);

        /** POST */
        } else if (isset($_GET['post'])) {
            if (isset($_GET['post']['id'])) {
                $sql = "update ";
            } else {
                $sql = "insert into ";
            }
            $sql .= '`'. $_GET['table'] .'` set ';
            foreach ($_GET['post'] as $key => $value) {
                if ($key == 'password') {
                   $value = md5($value);
                }
                if ($key != 'id') {
                    $sql .= '`'.$key.'`'.'=\''.$value.'\', ';
                }
            }
            $sql = substr($sql, 0, -2);
            if (isset($_GET['post']['id'])) {
                $sql .= ' where `id` = \''. $_GET['post']['id'] .'\'';
            }
            $result = $mysqli->query($sql);
            echo json_encode(['id' => $mysqli->insert_id]);

        /** GET */
        } else {
            $sql = "select * from ". $_GET['table'];
            if (isset($_GET['filter'])) {
                $sql .= ' where ';
                foreach ($_GET['filter'] as $key => $value) {
                    if ($key == 'password') {
                       $value = md5($value);
                    }
                    $sql .= '`'.$key.'`'.'=\''.$value.'\' and ';
                }
                $sql = substr($sql, 0, -5);
            }

            $result = $mysqli->query($sql);
            $emparray = array();
            while($row = mysqli_fetch_assoc($result)) {
                $emparray[] = $row;
            }
            echo json_encode($emparray);
        }

    }


