<?php

function PDO_InsertRecord(&$link_id, $tablename, $record_parameters, $debug = false)
{

    if (!is_array($record_parameters)) {

        if ($debug) {
            echo "No supplied parameters.<br>";
        }

        return false;
    }

    $arr_qry_params = array();
    $i = 0;
    foreach ($record_parameters as $field_value) {
        $arr_qry_params[$i++] = $field_value;
    }

    $fields = '';
    $values = '';

    foreach ($record_parameters as $field => $value) {
        $fields .= "$field,";
        $values .= '?,';
    }

    // $xlen = strlen($fields) - 1;
    // $fields[$xlen] = '';
    // $fields = trim($fields);

    // // $xlen = strlen($values) - 1;
    // // $values[$xlen] = '';
    // $values = trim($values);

    // 20210722 - Darius
    $fields = rtrim($fields, ",");
    $values = rtrim($values, ",");

    $xqry = "INSERT INTO $tablename ($fields) VALUES($values);";

    $stmt = $link_id->prepare($xqry);
    $stmt->execute($arr_qry_params);

    if ($debug) {
        echo '<hr><br>Statement : <br>';
        var_dump($stmt);
        echo '<br>Error Information<br>';
        var_dump($stmt->errorInfo());
        echo '<br>Parameters<br>';
        var_dump($arr_qry_params);
    }
    // $xerror=$xstmt3->errorinfo();
    // if($xerror['2']!='')
    // {
    //     return $xstmt3->errorinfo();
    // }
    // else
    // {
    //     return true;
    // }
    $xerror = $stmt->errorinfo();
    if ($xerror['2'] != '') {
        return $xerror['2'];
    } else {
        return true;
    }
}

function PDO_UpdateRecord(&$link_id, $tablename, $record_parameters, $condition = ' true ', $condition_parameters, $debug = false)
{

    $args = '';
    $i = 0;

    $update_parameters = array();

    foreach ($record_parameters as $field => $value) {
        $update_parameters[$i++] = $value;
        $args .= "$field=?,";

    }

    // $xlen = strlen($args) - 1;
    // $args[$xlen] = '';
    // $args = trim($args);

    $args = trim($args);
    $args = rtrim($args, ",");

    if (is_array($condition_parameters)) {
        foreach ($condition_parameters as $field => $value) {
            $update_parameters[$i++] = $value;
        }
    }

    $xqry = "UPDATE $tablename SET $args WHERE $condition";

    $stmt = $link_id->prepare($xqry);
    $stmt->execute($update_parameters);

    if ($debug) {
        echo '<hr><br>Statement : <br>';
        var_dump($stmt);
        echo '<br>Error Information<br>';
        var_dump($stmt->errorInfo());
        echo '<br>Parameters<br>';
        var_dump($update_parameters);
    }

    return true;

}

function PDO_DeleteRecord(&$link_id, $tablename, $condition = " true ", $condition_parameters = [], $debug = false)
{
    try {
        $xqry = "DELETE FROM $tablename WHERE $condition";

        $stmt = $link_id->prepare($xqry);
        $stmt->execute($condition_parameters);

        if ($debug) {
            echo "<hr><b>DELETE QUERY:</b><br>";
            var_dump($xqry);
            echo "<br><b>PARAMETERS:</b><br>";
            var_dump($condition_parameters);
            echo "<br><b>ERROR INFO:</b><br>";
            var_dump($stmt->errorInfo());
        }

        return true;

    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function PDO_Refreshid($par_tablename)
{
    global $link_id;
    $xcount = 0;

    $xqry = "SELECT recid FROM `$par_tablename` ORDER BY recid";
    $stmt = $link_id->prepare($xqry);
    $stmt->execute();

    while ($rs = $stmt->fetch()) {
        $xcount = $xcount + 1;

        $xqry = "UPDATE `$par_tablename` SET recid=? WHERE recid=? ";
        $stmt_upt = $link_id->prepare($xqry);

        $arr_qry_params = array($xcount, $rs['recid']);

        $stmt_upt->execute($arr_qry_params);
    }

    $xcount++;

    $xqry = "ALTER TABLE `$par_tablename` AUTO_INCREMENT = $xcount";
    $stmt_upt = $link_id->prepare($xqry);
    $stmt_upt->execute();

}

function PDO_UserActivityLog($link_id, $xusrcde, $xusrname, $xtrndte, $xprog_module, $xactivity, $xfullname, $xremarks, $linenum, $parameter, $trncde, $trndsc, $compname = "", $xusrnme = "")
{
    $xquery = "SELECT userlogmaxrec FROM syspar";
    $xstmt = $link_id->prepare($xquery);
    $xstmt->execute();
    $xrs = $xstmt->fetch(PDO::FETCH_ASSOC);
    $maxcount = $xrs['userlogmaxrec'];
    if ($maxcount == "") {
        $maxcount = "10000";
    }
    //~ var_dump($maxcount);die();

    $xqry_genledger = "SELECT count(*) as xcount FROM genledgerfile2 WHERE trncde = 'GLD'";
    $stmt_chkcount = $link_id->prepare($xqry_genledger);
    $stmt_chkcount->execute();
    $rs_chkcount = $stmt_chkcount->fetch();
    if ($rs_chkcount["xcount"] == 0) {
        $xremarks .= " Warning! GLD Deleted";
    }

    $xarr_rec = array();
    $xarr_rec['usrcde'] = $xusrcde;
    $xarr_rec['usrname'] = $xusrcde;
    $xarr_rec['usrdte'] = date("Y-m-d H:i:s");
    $xarr_rec['usrtim'] = date("H:i:s");
    $xarr_rec['trndte'] = $xtrndte;
    $xarr_rec['module'] = $xprog_module;
    $xarr_rec['activity'] = $xactivity;
    $xarr_rec['empcode'] = $xusrcde;
    $xarr_rec['fullname'] = $xfullname;
    $xarr_rec['remarks'] = $xremarks;
    $xarr_rec['linenum'] = $linenum;
    $xarr_rec['parameter'] = $parameter;
    $xarr_rec['trncde'] = $trncde;
    $xarr_rec['trndsc'] = $trndsc;
    $xarr_rec['compname'] = $compname;
    $xarr_rec['usrnam'] = $xusrnme;
    PDO_InsertRecord($link_id, 'useractivitylogfile', $xarr_rec, false);


    $qry_chkcount = "SELECT MAX(recid) as max_recid, MAX(recid) - MIN(recid) AS xcount FROM useractivitylogfile";
    $stmt_chkcount = $link_id->prepare($qry_chkcount);
    $stmt_chkcount->execute();
    $rs_chkcount = $stmt_chkcount->fetch();
    if ($rs_chkcount["xcount"] > $maxcount) {
        $xexcess = $rs_chkcount['max_recid'] - $maxcount;
        $xqry_del = "DELETE FROM useractivitylogfile WHERE recid < $xexcess";
        $xstmt_del = $link_id->prepare($xqry_del);
        $xstmt_del->execute();
        PDO_Refreshid("useractivitylogfile");
    }
}

