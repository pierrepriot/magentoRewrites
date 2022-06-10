<?php
include('vendor/autoload.php');

include('src/magentoRewrites.php');


/*------------------------------------------------*/

$inputFileName = './some-rewrite-rules.xlsx';
$colOld='A';
$colNew='B';
$storeId=1;
$homePageId=2;
/*------------------------------------------------*/

$oRW = new magentoRewrites($inputFileName, $colOld, $colNew);
$oRW->generate();
