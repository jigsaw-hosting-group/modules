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
 * @subpackage apache admin
 * @version $Id$
 * @author Bobby Allen - ballen@bobbyallen.me
 * @copyright (c) 2008-2014 Sentora Group - http://www.sentora.org/
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License v3
 */

SetWriteApacheConfigTrue();
DeleteApacheClientFiles();

function SetWriteApacheConfigTrue() {
    global $zdbh;
    $sql = $zdbh->prepare("UPDATE x_settings
								SET so_value_tx='true'
								WHERE so_name_vc='apache_changed'");
    $sql->execute();
}

function DeleteApacheClientFiles()
{
    global $zdbh;
    $sql     = "SELECT * FROM x_accounts WHERE ac_deleted_ts IS NOT NULL";
    $numrows = $zdbh->query( $sql );
    if ( $numrows->fetchColumn() <> 0 ) {
        $sql                = $zdbh->prepare( $sql );
        $res                = array( );
        $sql->execute();
        while ( $rowdeletedaccounts = $sql->fetch() ) {
            // Check for an active user with same username
            $sql2     = "SELECT COUNT(*) FROM x_accounts WHERE ac_user_vc=:user AND ac_deleted_ts IS NULL";
            $numrows2 = $zdbh->prepare( $sql2 );
            $user     = $rowdeletedaccounts[ 'ac_user_vc' ];
            $numrows2->bindParam( ':user', $user );
            if ( $numrows2->execute() ) {
                if ( $numrows2->fetchColumn() == 0 ) {
                    if ( file_exists( ctrl_options::GetSystemOption( 'hosted_dir' ) . $rowdeletedaccounts[ 'ac_user_vc' ] ) ) {
                        fs_director::RemoveDirectory( ctrl_options::GetSystemOption( 'hosted_dir' ) . $rowdeletedaccounts[ 'ac_user_vc' ] );
                    }
                }
            }
        }
    }
}

?>
