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
 * @subpackage domains
 * @version $Id$
 * @author Bobby Allen - ballen@bobbyallen.me
 * @copyright (c) 2008-2014 Sentora Group - http://www.sentora.org/
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License v3
 */
class webservice extends ws_xmws {

    /**
     * Get the full list of currently active domains on the server.
     * @global type $zdbh
     * @return type 
     */
    public function GetAllDomains() {
        global $zdbh;
        $response_xml = "\n";
        $alldomains = module_controller::ListDomains();
        foreach ($alldomains as $domain) {
            $response_xml = $response_xml . ws_xmws::NewXMLContentSection('domain', array(
                        'id' => $domain['id'],
                        'uid' => $domain['uid'],
                        'domain' => $domain['name'],
                        'homedirectory' => $domain['directory'],
                        'active' => $domain['active'],
                    ));
        }
        $dataobject = new runtime_dataobject();
        $dataobject->addItemValue('response', '');
        $dataobject->addItemValue('content', $response_xml);
        return $dataobject->getDataObject();
    }

    /**
     * Gets a list of all the domains that a user has configured on their hosting account (the user id needs to be sent in the <content> tag).
     * @global type $zdbh
     * @return type 
     */
    public function GetDomainsForUser() {
        global $zdbh;
        $request_data = $this->RawXMWSToArray($this->wsdata);
        $response_xml = "\n";
        $alldomains = module_controller::ListDomains($request_data['content']);
        if (!fs_director::CheckForEmptyValue($alldomains)) {
            foreach ($alldomains as $domain) {
                $response_xml = $response_xml . ws_xmws::NewXMLContentSection('domain', array(
                            'id' => $domain['id'],
                            'uid' => $domain['uid'],
                            'domain' => $domain['name'],
                            'homedirectory' => $domain['directory'],
                            'active' => $domain['active'],
                        ));
            }
        }

        $dataobject = new runtime_dataobject();
        $dataobject->addItemValue('response', '');
        $dataobject->addItemValue('content', $response_xml);

        return $dataobject->getDataObject();
    }

    /**
     * Enables an authenticated user to create a domain on their hosting account.
     * @return type 
     */
    public function CreateDomain() {
        $request_data = $this->RawXMWSToArray($this->wsdata);
        $dataobject = new runtime_dataobject();
        $dataobject->addItemValue('response', '');
        if (module_controller::ExecuteAddDomain(ws_generic::GetTagValue('uid', $request_data['content']), ws_generic::GetTagValue('domain', $request_data['content']), ws_generic::GetTagValue('destination', $request_data['content']), ws_generic::GetTagValue('autohome', $request_data['content']))) {
            $dataobject->addItemValue('content', ws_xmws::NewXMLTag('domain', ws_generic::GetTagValue('domain', $request_data['content'])) . ws_xmws::NewXMLTag('created', 'true'));
        } else {
            $dataobject->addItemValue('content', ws_xmws::NewXMLTag('domain', ws_generic::GetTagValue('domain', $request_data['content'])) . ws_xmws::NewXMLTag('created', 'false'));
        }
        return $dataobject->getDataObject();
    }

    /**
     * Delete a specified domain using the content <domainid> tag to pass the domain DB ID through.
     * @return type 
     */
    public function DeleteDomain() {
        $request_data = $this->RawXMWSToArray($this->wsdata);
        $contenttags = $this->XMLDataToArray($request_data['content']);
        $dataobject = new runtime_dataobject();
        $dataobject->addItemValue('response', '');

        if (module_controller::ExecuteDeleteDomain($contenttags['domainid'])) {
            $dataobject->addItemValue('content', ws_xmws::NewXMLTag('domainid', $contenttags['domainid']) . ws_xmws::NewXMLTag('deleted', 'true'));
        } else {
            $dataobject->addItemValue('content', ws_xmws::NewXMLTag('domainid', $contenttags['domainid']) . ws_xmws::NewXMLTag('deleted', 'false'));
        }
        return $dataobject->getDataObject();
    }

}

?>
