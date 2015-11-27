<?php

if (isset($_POST['kdatid']))
{
    /////////////////////////////
    /// Open MySQL connection ///
    /////////////////////////////

    include_once 'datensatz.copier.login.php';

    $mysqli = new mysqli($host, $username, $passwd, $dbname);

    unset($host);
    unset($username);
    unset($passwd);
    unset($dbname);

    if ($mysqli->connect_errno)
    {
        echo 'Failed to connect to MySQL: ' . $mysqli->connect_error;
        exit();
    }



    //////////////////////////////
    /// tx_bolonline_Kerndaten ///
    //////////////////////////////

    $kdatid = $_POST['kdatid'];

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_Kerndaten WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP kerndaten_id;';
    $query .= 'UPDATE tmp SET a2=CONCAT(\'KOPIE: \', a2);';
    $query .= 'UPDATE tmp SET a3=\'\';';
    $query .= 'UPDATE tmp SET a5=\'\';';
    $query .= 'UPDATE tmp SET a14b=\'\';';
    $query .= 'UPDATE tmp SET a14ba=\'\';';
    $query .= 'UPDATE tmp SET a15b=\'\';';
    $query .= 'UPDATE tmp SET a15ba=\'\';';
    $query .= 'UPDATE tmp SET a16b=\'\';';
    $query .= 'UPDATE tmp SET a16ba=\'\';';
    $query .= 'UPDATE tmp SET a18=\'\';';
    $query .= 'UPDATE tmp SET a19=\'\';';
    $query .= 'UPDATE tmp SET a20=\'\';';
    $query .= 'UPDATE tmp SET a22=\'\';';
    $query .= 'UPDATE tmp SET a23=\'\';';
    $query .= 'UPDATE tmp SET f2b=\'\';';
    $query .= 'UPDATE tmp SET f3b=\'\';';
    $query .= 'ALTER TABLE tx_bolonline_Kerndaten AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_Kerndaten SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_Kerndaten: ' .  $mysqli->error;
        exit();
    }

    $query = 'SELECT MAX(kerndaten_id) FROM tx_bolonline_Kerndaten';

    if ($res = $mysqli->query($query))
    {
        $row = $res->fetch_assoc();
        $nkdatid = $row['MAX(kerndaten_id)'];
        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_Kerndaten: ' .  $mysqli->error;
        exit();
    }



    ////////////////////////////////////////////
    /// tx_bolonline_HauptkategorieZuordnung ///
    ////////////////////////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_HauptkategorieZuordnung WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';
    $query .= 'ALTER TABLE tx_bolonline_HauptkategorieZuordnung AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_HauptkategorieZuordnung SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_Kerndaten: ' .  $mysqli->error;
        exit();
    }



    //////////////////////////
    /// tx_bolonline_PartI ///
    //////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_PartI WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';
    $query .= 'UPDATE tmp SET e2=\'\';';
    $query .= 'UPDATE tmp SET e5c=\'\';';
    $query .= 'ALTER TABLE tx_bolonline_PartI AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_PartI SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartI: ' .  $mysqli->error;
        exit();
    }

    $p1id = array();
    $query = 'SELECT id FROM tx_bolonline_PartI WHERE kerndaten_id=' . $kdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($p1id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartI: ' .  $mysqli->error;
        exit();
    }

    $np1id = array();
    $query = 'SELECT id FROM tx_bolonline_PartI WHERE kerndaten_id=' . $nkdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($np1id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartI: ' .  $mysqli->error;
        exit();
    }

    $p1assoc = array_combine($p1id, $np1id);



    ///////////////////////////
    /// tx_bolonline_PartII ///
    ///////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_PartII WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';
    $query .= 'UPDATE tmp SET b7a=\'\';';
    $query .= 'UPDATE tmp SET b7c=\'\';';
    $query .= 'UPDATE tmp SET b8=\'\';';
    $query .= 'UPDATE tmp SET b12=\'\';';
    $query .= 'ALTER TABLE tx_bolonline_PartII AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_PartII SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartII: ' .  $mysqli->error;
        exit();
    }

    $p2id = array();
    $query = 'SELECT id FROM tx_bolonline_PartII WHERE kerndaten_id=' . $kdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($p2id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartII: ' .  $mysqli->error;
        exit();
    }

    $np2id = array();
    $query = 'SELECT id FROM tx_bolonline_PartII WHERE kerndaten_id=' . $nkdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($np2id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartII: ' .  $mysqli->error;
        exit();
    }

    $p2assoc = array_combine($p2id, $np2id);



    ////////////////////////////
    /// tx_bolonline_PartIII ///
    ////////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_PartIII WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';
    $query .= 'UPDATE tmp SET c4=\'\';';
    $query .= 'UPDATE tmp SET c7c=\'\';';
    $query .= 'ALTER TABLE tx_bolonline_PartIII AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_PartIII SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIII: ' .  $mysqli->error;
        exit();
    }

    $p3id = array();
    $query = 'SELECT id FROM tx_bolonline_PartIII WHERE kerndaten_id=' . $kdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($p3id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIII: ' .  $mysqli->error;
        exit();
    }

    $np3id = array();
    $query = 'SELECT id FROM tx_bolonline_PartIII WHERE kerndaten_id=' . $nkdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($np3id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIII: ' .  $mysqli->error;
        exit();
    }

    $p3assoc = array_combine($p3id, $np3id);



    /////////////////////////////////////////
    /// tx_bolonline_PartIII_associations ///
    /////////////////////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_PartIII_associations WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';

    foreach ($p1assoc as $old => $new)
    {
        $query .= 'UPDATE tmp SET partI_id=' . $new . ' WHERE partI_id=' . $old . ';';
    }

    foreach ($p2assoc as $old => $new)
    {
        $query .= 'UPDATE tmp SET partII_id=' . $new . ' WHERE partII_id=' . $old . ';';
    }

    foreach ($p3assoc as $old => $new)
    {
        $query .= 'UPDATE tmp SET partIII_id=' . $new . ' WHERE partIII_id=' . $old . ';';
    }

    $query .= 'ALTER TABLE tx_bolonline_PartIII_associations AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_PartIII_associations SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIII_associations: ' .  $mysqli->error;
        exit();
    }



    ///////////////////////////
    /// tx_bolonline_PartIV ///
    ///////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_PartIV WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';
    $query .= 'ALTER TABLE tx_bolonline_PartIV AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_PartIV SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIV: ' .  $mysqli->error;
        exit();
    }

    $p4id = array();
    $query = 'SELECT id FROM tx_bolonline_PartIV WHERE kerndaten_id=' . $kdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($p4id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIV: ' .  $mysqli->error;
        exit();
    }

    $np4id = array();
    $query = 'SELECT id FROM tx_bolonline_PartIV WHERE kerndaten_id=' . $nkdatid;

    if ($res = $mysqli->query($query))
    {
        while ($row = $res->fetch_assoc())
        {
            array_push($np4id, $row['id']);
        }

        $res->free();
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIV: ' .  $mysqli->error;
        exit();
    }

    $p4assoc = array_combine($p4id, $np4id);



    ////////////////////////////////////////
    /// tx_bolonline_PartIV_associations ///
    ////////////////////////////////////////

    $query = 'CREATE TEMPORARY TABLE tmp SELECT * FROM tx_bolonline_PartIV_associations WHERE kerndaten_id=' . $kdatid . ';';
    $query .= 'ALTER TABLE tmp DROP id;';
    $query .= 'UPDATE tmp SET kerndaten_id=' . $nkdatid . ';';

    foreach ($p1assoc as $old => $new)
    {
        $query .= 'UPDATE tmp SET partI_id=' . $new . ' WHERE partI_id=' . $old . ';';
    }

    foreach ($p2assoc as $old => $new)
    {
        $query .= 'UPDATE tmp SET partII_id=' . $new . ' WHERE partII_id=' . $old . ';';
    }

    foreach ($p4assoc as $old => $new)
    {
        $query .= 'UPDATE tmp SET partIV_id=' . $new . ' WHERE partIV_id=' . $old . ';';
    }

    $query .= 'ALTER TABLE tx_bolonline_PartIV_associations AUTO_INCREMENT=1;';
    $query .= 'INSERT INTO tx_bolonline_PartIV_associations SELECT 0,tmp.* FROM tmp;';
    $query .= 'DROP TABLE tmp;';

    if ($mysqli->multi_query($query))
    {
        while ($mysqli->next_result())
        {
            if ($res = $mysqli->store_result())
            {
                $res->free();
            }
        }
    }
    else
    {
        echo 'Failed to execute MySQL query on tx_bolonline_PartIII_associations: ' .  $mysqli->error;
        exit();
    }



    //////////////////////////////
    /// Close MySQL connection ///
    //////////////////////////////

    $mysqli->close();
}

?>
