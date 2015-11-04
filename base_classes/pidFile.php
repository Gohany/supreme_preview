<?PHP

class pidFile
{

	private $_file;
	private $_running;

	public function __construct($dir, $name, $pid = false)
	{
		$this->_file = "$dir/$name.pid";

		if (file_exists($this->_file))
		{
			if (!$pid)
			{
				$pid = trim(file_get_contents($this->_file));
			}
			$this->_running = true;
		}

		if (!$this->_running)
		{
			file_put_contents($this->_file, $pid);
		}
	}

	public function __destruct()
	{
		if ((!$this->_running) && file_exists($this->_file))
		{
			unlink($this->_file);
		}
	}

	public function isRunning()
	{
		return $this->_running;
	}

}