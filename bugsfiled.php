<?php

/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Bugzilla C3PO.
 *
 * The Initial Developer of the Original Code is Mozilla.
 *
 * Portions created by the Initial Developer are Copyright (C) 2___
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s): Milos Dinic <milos@mozilla.com>, Pascal Chevrel <pascal@mozilla.com>
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */
function dashboardItem($locale, $bugnumber, $description, $tag='') {

    $value = <<<VALUE

'{$bugnumber}'=> array(
            '{$bugnumber}',
            '{$description}',
            '',
            array('{$locale}'),
            array('{$tag}'),
            array(''),
            ),

VALUE;

    return $value;
}

$time_start = microtime(true);

require_once 'controller.inc.php';

// Populate $locales var with a set of locales
// we want to file bugs for

if(isset($_POST['all-locales'])) {
    $locales = $full_languages;
} elseif (isset($_POST['locales'])) {
    $locales = clean_explode($_POST['locales'], ',');
} else {
    echo 'error: no locale codes';
    exit;
}

sort($locales);

// Summary, or title of the bug;
// An locale tag in form of "[ab-CD]" will preceed it
$bugsummary = $_POST['summary'];

// Login info that we'll get via POST
$xml_data_login = array(
    'login'    => $_POST['username'],
    'password' => $_POST['pwd'],
    'remember' => 1,
);

// Data we use for bug creation
// All data is provided on front page
$xml_data_create = array (
    'product'           => $_POST['product'],
    'component'         => $_POST['component'],
    'version'           => $_POST['version'],
    'op_sys'            => 'All', // Operating system
    'rep_platform'      => 'All', // Platform
    'status_whiteboard' => $_POST['whiteboard'],
    'blocked'           => $_POST['blocked'],
    'assigned_to'       => $_POST['assign_to'],
);

// Set the target for our requests
$curl_target = $bugzilla_url . 'xmlrpc.cgi';

// Create a cookie
$cookie = tempnam('', 'bugzilla-filer');

// Set cURL options
$curlopts = array(
    CURLOPT_URL            => $curl_target,
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => array( 'Content-Type: text/xml', 'charset=utf-8' )
);

// Initialize cURL
$curl_start = curl_init();
curl_setopt_array($curl_start, $curlopts);

// Create a request based on data we got from index.php
$request = xmlrpc_encode_request("User.login", $xml_data_login);
curl_setopt($curl_start, CURLOPT_POSTFIELDS, $request);
curl_setopt($curl_start, CURLOPT_COOKIEJAR,  $cookie); // Get the cookie from Bugzilla

$response = curl_exec($curl_start); // execute
$response = xmlrpc_decode($response); // Decoded response is logged-in user ID


// Check if response is all ok, and proceed. If not, throw an error
if (empty($response['id'])) {
    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
    die('failed to log in. login details below:<br>'. print_r($xml_data_login));
}

// If no errors were thrown at user, that means we're connected, cookie is saved
// which means we're logged in and session has started

// Now we loop through all locales from a $locales var and file a bug for each one
echo '<pre>';

foreach ($locales as $key => $shortcode) {
    // This set of vars needs to be in the loop as it depends on locale code
    $xml_data_create['bug_file_loc'] = str_replace('{{{locale}}}', $shortcode, $_POST['url']);
    $xml_data_create['summary']      = str_replace('{{{locale}}}', $shortcode, $_POST['summary']);
    $xml_data_create['description']  = str_replace('{{{locale}}}', $shortcode, $_POST['description']);

    $xml_data_create['bug_file_loc'] = str_replace('{{{lc_locale}}}', strtolower($shortcode), $xml_data_create['bug_file_loc']);
    $xml_data_create['summary']      = str_replace('{{{lc_locale}}}', strtolower($shortcode), $xml_data_create['summary']);
    $xml_data_create['description']  = str_replace('{{{lc_locale}}}', strtolower($shortcode), $xml_data_create['description']);
    $xml_data_create['cf_locale']    = $shortcode . ' / ' . $bugzilla_locales[$shortcode];

    // Make the request to file a bug
    $request = xmlrpc_encode_request("Bug.create", $xml_data_create); // create a request for filing bugs
    curl_setopt($curl_start, CURLOPT_POSTFIELDS, $request);
    curl_setopt($curl_start, CURLOPT_COOKIEFILE, $cookie);
    $buglist_array_item = xmlrpc_decode(curl_exec($curl_start)); // Get the ID of the filed bug
/*
    echo '<br><a href="'. $bugzilla_url . 'show_bug.cgi?id=' . $buglist_array_item['id'] . '">Bug ID=' . $buglist_array_item['id'] . '</a>';
*/
    if(isset($_POST['tag'])) {
        $tag = strip_tags($_POST['tag']);
    } else {
        $tag = '';
    }

    echo dashboardItem($shortcode, $buglist_array_item['id'], strip_tags($xml_data_create['summary']), $tag);
}

echo '</pre>';

curl_close($curl_start);
unlink($cookie);
$time_end = microtime(true);
$time     = $time_end - $time_start;
echo '<!--  Script execution time: '.$time.'  -->';


