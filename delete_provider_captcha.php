<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
ob_implicit_flush(true);
ini_set('max_execution_time', '9999999');
ob_end_flush();

define('DOCROOT', dirname(__FILE__).'/');
require('./engine/CMSMain.inc.php');
// CMSGlobal::setTEXTHeader();

$providerId = CMSLogicProvider::LUXOTTICA;

if(!isset($_GET['brand_id']) || empty($_GET['brand_id'])) {
	echo "Please enter brand_id from a list:<br>";
	echo "<pre>";
	CMSCaptchaRemover::showProviderBrands($providerId);
	die('...');
}

if(!is_numeric($_GET['brand_id']))
	die('Provider id must be a numeric only!!!');

if(!ctype_digit($_GET['brand_id']))
	die('Provider id must be a integer only!!!');

// попытки взлома отсекли, приступаем к определению провайдера
$brandId = $_GET['brand_id'];
echo "Selected brand id - ". $brandId ." ...<br>";



$imageReplacer = new CMSCaptchaRemover($providerId, $brandId);
$imgData = $imageReplacer->getImagesDataByBrand();

$imageReplacer->deleteImgCaptchaAndSave($imgData);