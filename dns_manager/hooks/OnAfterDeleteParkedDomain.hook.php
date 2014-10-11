<?php

/**
 *
 * Sentora - The open source control panel.
 * @contact developers@sentora.org
 *
 * This program (Sentora) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package Sentora
 * @subpackage dns admin
 * @version $Id$
 * @author Bobby Allen - ballen@bobbyallen.me
 * @copyright (c) 2008-2014 Sentora Group - http://www.sentora.org/
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License v3
 */

DeleteDNSRecordsForDeletedDomain();

function DeleteDNSRecordsForDeletedDomain() {
    global $zdbh;
    $deleteddomains = array();
    $sql = "SELECT COUNT(*) FROM x_vhosts WHERE vh_deleted_ts IS NOT NULL";
    if ($numrows = $zdbh->query($sql)) {
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare("SELECT * FROM x_vhosts WHERE vh_deleted_ts IS NOT NULL");
            $sql->execute();
            while ($rowvhost = $sql->fetch()) {
                $deleteddomains[] = $rowvhost['vh_id_pk'];
            }
        }
    }
    foreach ($deleteddomains as $deleteddomain) {
        //$result = $zdbh->query("SELECT * FROM x_dns WHERE dn_vhost_fk=" . $deleteddomain . " AND dn_deleted_ts IS NULL")->Fetch();
        $numrows = $zdbh->prepare("SELECT * FROM x_dns WHERE dn_vhost_fk=:deleteddomain AND dn_deleted_ts IS NULL");
        $numrows->bindParam(':deleteddomain', $deleteddomain);
        $numrows->execute();
        $result = $numrows->fetch();
        if ($result) {
            $sql = $zdbh->prepare("UPDATE x_dns SET dn_deleted_ts=:time WHERE dn_vhost_fk=:deleteddomain");
            $sql->bindParam(':deleteddomain', $deleteddomain);
            $time = time();
            $sql->bindParam(':time', $time);
            $sql->execute();
            TriggerDNSUpdate($result['dn_vhost_fk']);
        }
    }
}

function TriggerDNSUpdate($id) {
    global $zdbh;
    $GetRecords = ctrl_options::GetSystemOption('dns_hasupdates');
    $records = explode(",", $GetRecords);
    foreach ($records as $record) {
        $RecordArray[] = $record;
    }
    if (!in_array($id, $RecordArray)) {
        $newlist = $GetRecords . "," . $id;
        $newlist = str_replace(",,", ",", $newlist);
        $sql = "UPDATE x_settings SET so_value_tx=:newlist WHERE so_name_vc='dns_hasupdates'";
        $sql = $zdbh->prepare($sql);
        $sql->bindParam(':newlist', $newlist);
        $sql->execute();
        return true;
    }
}

?>
