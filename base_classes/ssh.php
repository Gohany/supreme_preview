<?PHP

class sshFingerprints
{

	public static $fingerprints = array(
	    '192.168.1.240' => '71E56C149C7819C09ADAAA8298AD5F1A',
	);

	public static function acceptFingerprint($host, $fingerprint)
	{
		return true;
		if (in_array($host, self::$fingerprints) && self::$fingerprints[$host] == $fingerprint)
		{
			return true;
		}
		return false;
	}

}

class ssh
{

	public $connection;
	public $fingerPrint;
	public $sftp;
	public $fileStream;

	public function __construct($connection)
	{

		$this->connection = $connection;
		#$this->fingerPrint = $fingerprint;
	}

	public static function fromPassword($username, $password, $address, $port = 22)
	{

		$connection = ssh2_connect($address, $port);
		#$fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
		if (
		#sshFingerprints::acceptFingerprint($fingerprint) && 
			ssh2_auth_password($connection, $username, $password)
		)
		{
			return new ssh($connection);
		}
		return false;
	}

	public static function fromKey($address, $port, $username, $key, $privkey = '', $password = null, $methods = null, $callbacks = null)
	{

		$connection = ssh2_connect($address, $port, $methods, $callbacks);
		$fingerprint = ssh2_fingerprint($connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
		if (
			sshFingerprints::acceptFingerprint($address, $fingerprint) &&
			ssh2_auth_pubkey_file($connection, $username, $key, $privkey, $password)
		)
		{
			return new ssh($connection, $fingerprint);
		}
		return false;
	}

	public function sftp()
	{
		if (isset($this->sftp))
		{
			return $this->sftp;
		}

		$this->sftp = ssh2_sftp($this->connection);
		return $this->sftp;
	}

	public function sftpFilesize($location)
	{
		return filesize("ssh2.sftp://" . $this->sftp() . $location);
	}

	public function sftpIsDir($dir, $mkdir = false, $recursive = false)
	{

		$return = false;
		$create_dir = '';

		$dir = trim($dir, '/');

		if (is_dir("ssh2.sftp://" . $this->sftp() . $dir))
		{
			$return = true;
		}
		else
		{
			if ($mkdir)
			{
				$explode = explode('/', trim($dir, '/'));
				if (!$recursive)
				{
					if (ssh2_sftp_mkdir($this->sftp(), end($explode)))
					{
						$return = true;
					}
				}
				else
				{
					for ($i = 0, $c = count($explode); $i < $c; $i++)
					{
						if (!is_dir("ssh2.sftp://" . $this->sftp() . $explode[$i]))
						{
							if (ssh2_sftp_mkdir($this->sftp(), $explode[$i]))
							{
								if (is_dir("ssh2.sftp://" . $this->sftp() . $explode[$i]))
								{
									continue;
								}
								else
								{
									break;
								}
							}
							else
							{
								break;
							}
						}
					}
				}
				if ($this->sftpIsDir($dir))
				{
					$return = true;
				}
			}
		}

		return $return;
	}

	public function sftpFileStream($location, $method)
	{
		if (isset($this->fileStream))
		{
			return $this->fileStream;
		}
		$this->fileStream = fopen("ssh2.sftp://" . $this->sftp() . $location, $method);
		return $this->fileStream;
	}

}