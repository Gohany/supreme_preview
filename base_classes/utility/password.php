<?PHP
class passwordModel extends model
{
	
	public $index;
	public $password;
	public $passwordHash;
	public $passwordIndex;
	public $salt;

	const PASSWORD_INDEX_SALT = 'Cra*8b7fRA__s*46-za9egUcrEWadRUG@P+Axe46h@dr8wRuQer&9UtR4w6y?@Pu';
	const PASSWORD_SALT = 'y=hDTFBWzid_W_r_(@(vL}>lQ+hQ]JdLyZTIh%27l9<^Qht*QuD(uHz6WgtK9!Ls';

	public function salt()
	{
		if ($this->salt !== null)
		{
			return $this->salt;
		}

		$this->salt = utility::salt(22);
		return $this->salt;
	}

	public function passwordHash()
	{
		if ($this->passwordHash !== null)
		{
			return $this->passwordHash;
		}

		if ($this->password !== null)
		{
			$this->passwordHash = utility::hashPassword(self::PASSWORD_SALT . $this->password, $this->salt());
			$this->password = null;
			return $this->passwordHash;
		}

		return false;
	}

	public static function generateIndex($index)
	{
		return utility::hash($index . self::PASSWORD_INDEX_SALT);
	}

	public function index()
	{
		return self::generateIndex($this->index);
	}
}