<?php
include('vendor/autoload.php');

include('src/magentoRewrites.php');


/*------------------------------------------------*/

$inputFileName = './NEP-FR.xlsx';
$colOld='A';
$colNew='B';
$storeId=1;
$homePageId=2;
/*------------------------------------------------*/
$oRW = new magentoRewrites($inputFileName, $colOld, $colNew, $storeId, $homePageId);
$oRW->generate();
