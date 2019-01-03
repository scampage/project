<?php
/**
 * @Author: Eka Syahwan
 * @Date:   2017-09-14 07:43:42
 * @Last Modified by:   Eka Syahwan
 * @Last Modified time: 2018-04-26 08:31:56
 */
error_reporting(0);
ini_set('memory_limit','-1');
define( 'ROOT', dirname(__FILE__) . '/' );
require_once ROOT.'/modules/sendinbox/sendinbox.php';
require_once ROOT.'/modules/src/SmtpEmailValidation.php';
require_once ROOT.'/modules/src/SmtpSocket.php';
require_once ROOT.'/smtp-config.php';
require_once ROOT.'/modules/src/dompdf-0.5.1/dompdf_config.inc.php';