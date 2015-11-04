<?PHP
//If htdocs path is not set, it is our parent directory.
if (!isset($_SERVER['_HTDOCS_']))
{
	$_SERVER['_HTDOCS_'] = realpath(dirname(__FILE__) . '/..');
}

require_once $_SERVER['_HTDOCS_'] . '/base_include.php';

ob_start();

//Process input
{
	parse_str(file_get_contents('php://input'), $input);
	if (!empty($_GET))
	{
		array_merge($input, $_GET);
	}
	if (!empty($input))
	{
		$_REQUEST = $input;
	}

	//Add request method override required by clients that can't actually do put/delete requests
	{
		if (isset($_REQUEST['REQUEST_METHOD_OVERRIDE']) && in_array(strtolower($_REQUEST['REQUEST_METHOD_OVERRIDE']), array('get', 'post', 'put', 'delete')))
		{
			$_SERVER['REQUEST_METHOD'] = strtoupper($_REQUEST['REQUEST_METHOD_OVERRIDE']);
		}
	}
}

$request = new request();
$request->log();

try
{
	//Process request
	$dispatcher = new dispatcher();
	$dispatcher->start();
	$output = $dispatcher->call->output;
	headers::printHeaders($dispatcher->call);

	//Cleanup request
	unset($dispatcher->call);
	unset($dispatcher);
}
catch (Exception $e)
{
	if (is_numeric($e->statusHeader) && $e->statusHeader >= 400 && $e->statusHeader < 500 && isset($dispatcher) && $dispatcher instanceof dispatcher)
	{
		$dispatcher->addBruteForceAttempt();
	}

	$output = null;
	headers::printHeaders($e);
}

$request->endRequest();

output::commit($output, $request->getTotalRequestTime());

$request->log();
unset($output);

mysql::destroyConnections();

//TODO: refactor this stuff out of index and into some sort of managed something.  pls :)
if (defined('__RUN_GEARMAN_JOBS__') && __RUN_GEARMAN_JOBS__)
{
	asyncTracking::runGearman();
}