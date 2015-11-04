<?php

$gmWorker->addFunction("accountCreated", "accountCreated");

function accountCreated($job)
{
	
	try
	{
		
		$workload = $job->workload();
		$data = unserialize($workload);

		if (!dataEngine::write('cccountCreationLog', array(
				'action' => 'insert',
				'data' =>  $data,
				)
			)
		)
		{
			throw new error(errorCodes::ERROR_INTERNAL_ERROR);
		}

		return true;
		
	}
	catch (Exception $e)
	{
		echo error::listErrors();
		flush();
	}
}