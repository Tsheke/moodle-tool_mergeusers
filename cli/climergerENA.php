<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package tool
 * @subpackage mergeusers
 * @author Jordi Pujol-Ahulló <jordi.pujol@urv.cat>
 * @copyright 2013 Servei de Recursos Educatius (http://www.sre.urv.cat)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("CLI_SCRIPT", true);

require_once __DIR__ . '/../../../../config.php';

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL | E_STRICT);

global $CFG;

require_once $CFG->dirroot . '/lib/clilib.php';
require_once __DIR__ . '/../lib/autoload.php';

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'debugdb'    => false,
        'alwaysRollback' => false,
        'toid' => false,
        'fromid'=>false,
        'help'    => false,
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help']) {
    $help =
        "Command Line user merger. These are the available options:

Options:
--help            Print out this help
--debugdb         Output all db statements used to do the merge
--alwaysRollback  Do the full merge but rollback the transaction at the last opportunity
--toid userid to keep
--fromid userid to delete
";

    echo $help;
    exit(0);
}

// loads current configuration
$config = tool_mergeusers_config::instance();

$config->debugdb = !empty($options['debugdb']);
$config->alwaysRollback = !empty($options['alwaysRollback']);

// initializes merger tool
$mut = new MergeUserTool($config); //may abort execution if database is not supported
$merger = new Merger($mut);

if(is_numeric($options['fromid']) && is_numeric($options['toid'])){
  $toid=intval($options['toid']);
  $fromid=intval($options['fromid']);
  list($success, $log, $id) = $mut->merge($toid, $fromid);
  // only shows results on cli script
  if (defined("CLI_SCRIPT")) {
    echo (($success)?get_string("success"):get_string("error")) . ". Log id: " . $id . "\n\n";
     } 

     if (defined("CLI_SCRIPT")) {
        echo get_string('ok') .", exit!\n\n";
    }
}else{
    
echo "Not here !!!\n";print_r($options); exit;
// initializes gathering instance
$gatheringname = $config->gathering;
$gathering = new $gatheringname();

//collects and performs user mergings
$merger->merge($gathering);
}
exit(0); // if arrived here, all ok ;-)
