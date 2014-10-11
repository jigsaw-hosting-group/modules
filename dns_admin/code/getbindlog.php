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
session_start();
if (!isset($_SESSION['zpuid'])) {
    die("<h1>Unathorised request!</h1><p>You must be logged in before you are able to view the DNS logs on this server.</p>");
}
?>
<body bgcolor="#000000">
    <font color="#009900">
    <?php
    if (isset($_POST['inBindLog'])) {
        $bindlog = str_replace('..', '__', $_POST['inBindLog']); // Temporary fix until I can think of a better solution, this will however stop reverse iteration of the filesystem though by causing an error if '..'  eg. '../../' is requested.
        $logerror = array();
        $logwarning = array();
        $getlog = array();
        if (file_exists($bindlog)) {
            $handle = @fopen($bindlog, "r");
            $getlog = array();
            if ($handle) {
                while (!feof($handle)) {
                    $buffer = fgets($handle, 4096);
                    $getlog[] = $buffer;
                    if (strstr($buffer, 'error:') || strstr($buffer, 'error ')) {
                        $logerror[] = $buffer;
                    }
                    if (strstr($buffer, 'warning:') || strstr($buffer, 'warning ')) {
                        $logwarning[] = $buffer;
                    }
                }fclose($handle);
            }
        }


        if (isset($_POST['inViewErrors'])) {
            echo "<font color=\"#FFF\"><h2>BIND Errors:</h2></font>";
            foreach ($logerror as $logline) {
                $logline = str_replace("error", "<font color=\"#CC0000\">error</font>", $logline);
                echo $logline . "<br>";
            }
        }

        if (isset($_POST['inViewWarnings'])) {
            echo "<font color=\"#FFF\"><h2>BIND Warnings:</h2></font>";
            foreach ($logwarning as $logline) {
                $logline = str_replace("warning", "<font color=\"#FFFF99\">warning</font>", $logline);
                echo $logline . "<br>";
            }
        }

        if (isset($_POST['inViewLogs'])) {
            echo "<font color=\"#FFF\"><h2>BIND Full Logs:</h2></font>";
            foreach ($getlog as $logline) {
                if (strstr($logline, "succeeded") || strstr($logline, "SIGHUP")) {
                    $logline = "<font color=\"#00FF00\">" . $logline . "</font>";
                }
                if (strstr($logline, "error")) {
                    $logline = "<font color=\"#CC0000\">" . $logline . "</font>";
                }
                if (strstr($logline, "Failed")) {
                    $logline = "<font color=\"#AAAAAA\">" . $logline . "</font>";
                }
                if (strstr($logline, "warning")) {
                    $logline = "<font color=\"#FFFF99\">" . $logline . "</font>";
                }
                echo $logline . "<br>";
            }
        }
    } else {
        
    }
    ?>
    </font>
</body>
