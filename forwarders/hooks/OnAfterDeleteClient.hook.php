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
 * @subpackage mail
 * @version $Id$
 * @author Bobby Allen - ballen@bobbyallen.me
 * @copyright (c) 2008-2014 Sentora Group - http://www.sentora.org/
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License v3
 */

DeleteForwardersForDeletedClient();

function DeleteForwardersForDeletedClient() {
    global $zdbh;
    $deletedclients = array();
    $sql = "SELECT COUNT(*) FROM x_accounts WHERE ac_deleted_ts IS NOT NULL";
    if ($numrows = $zdbh->query($sql)) {
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare("SELECT * FROM x_accounts WHERE ac_deleted_ts IS NOT NULL");
            $sql->execute();
            while ($rowclient = $sql->fetch()) {
                $deletedclients[] = $rowclient['ac_id_pk'];
            }
        }
    }

    // Include mail server specific file here.
    if (file_exists("modules/forwarders/hooks/" . ctrl_options::GetSystemOption('mailserver_php') . "")) {
        include("modules/forwarders/hooks/" . ctrl_options::GetSystemOption('mailserver_php') . "");
    }

    foreach ($deletedclients as $deletedclient) {
        //$result = $zdbh->query("SELECT * FROM x_forwarders WHERE fw_acc_fk=:deletedclient AND fw_deleted_ts IS NULL")->Fetch();       
        $numrows = $zdbh->prepare("SELECT * FROM x_forwarders WHERE fw_acc_fk=:deletedclient AND fw_deleted_ts IS NULL");
        $numrows->bindParam(':deletedclient', $deletedclient);
        $numrows->execute();
        $result = $numrows->fetch();

        if ($result) {
            $sql = $zdbh->prepare("UPDATE x_forwarders SET fw_deleted_ts=:time WHERE fw_acc_fk=:deletedclient");
            $time = time();
            $sql->bindParam(':time', $time);
            $sql->bindParam(':deletedclient', $deletedclient);
            $sql->execute();
        }
    }
}

?>
