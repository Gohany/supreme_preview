<?PHP
if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = getcwd();
}
require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

set_time_limit(60 * 10);
ini_set("memory_limit", "1024M");
ini_set('display_errors', true);
ini_set('log_errors', true);
ini_set('html_errors', false);

$startTime = 0;
$endTime = strtotime('-3 minutes');

echo 'Parsing records between ' . date('Y-m-d H:i:s', $startTime) . ' and ' . date('Y-m-d H:i:s', $endTime) . PHP_EOL;

$parser = new requestParser($startTime, $endTime);
if ($parser->parseRequestTimes())
{
	$parser->analyzeData();
	$parser->removeParsedRecords();
}
