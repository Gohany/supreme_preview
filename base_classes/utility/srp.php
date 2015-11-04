<?PHP

class srpModel extends model
{
	const PRIVATE_KEY = 'b92718d660e33fcab72047858eb044f61fd3e3b05de5ee9a6f864ebe6fe14d8281cf1d98d6e073fac9f7d5950856340afcf85e8dee6624576f6a0209dfcce405e8e6829fa8c2a90792a3dfacea9058cd11c800b970550fbf87b10cd6e3a97a86a4d66ee3c9e4a2f5571a939b6d3f64df1663518a98fe9bd4cdde2fd764531023';
	const ENCRYPTION_METHOD = 'sha256';
        /*
         *  00:b9:27:18:d6:60:e3:3f:ca:b7:20:47:85:8e:b0:
            44:f6:1f:d3:e3:b0:5d:e5:ee:9a:6f:86:4e:be:6f:
            e1:4d:82:81:cf:1d:98:d6:e0:73:fa:c9:f7:d5:95:
            08:56:34:0a:fc:f8:5e:8d:ee:66:24:57:6f:6a:02:
            09:df:cc:e4:05:e8:e6:82:9f:a8:c2:a9:07:92:a3:
            df:ac:ea:90:58:cd:11:c8:00:b9:70:55:0f:bf:87:
            b1:0c:d6:e3:a9:7a:86:a4:d6:6e:e3:c9:e4:a2:f5:
            57:1a:93:9b:6d:3f:64:df:16:63:51:8a:98:fe:9b:
            d4:cd:de:2f:d7:64:53:10:23
         */
	public $a_resource;
	public $A_resource;
	public $b_resource;
	public $B_resource;
	public $clientProof_resource;
	public $clientSessionKey_resource;
	public $g_resource;
	public $k_resource;
	public $key_resource;
	public $salt_resource;
	public $serverProof_resource;
	public $serverSessionKey_resource;
	public $u_resource;
	public $v_resource;
	public $x_resource;
	public $a;
	public $A;
	public $b;
	public $B;
	public $g = 2;
	public $id;
	public $k;
	public $password;
	public $salt;
	public $salt2;
	public $u;
	public $v;
	public $x;
	public $clientProof;
	public $clientSessionKey;
	public $serverProof;
	public $serverSessionKey;

	const KEY_BUFFER_SIZE = 512;
	const CACHE_LOGIN_RESPONSE_TIME = 1;

	public function __construct($array = array())
	{
		$this->key_resource = gmp_init(self::PRIVATE_KEY, 16);
		$this->g_resource = gmp_init($this->g, 16);

		foreach ($array as $type => $data)
		{
			switch ($type)
			{
				case 'values':
					foreach ($data as $property => $value)
					{
						if (property_exists(__CLASS__, $property) && !empty($value))
						{
							$this->{$property} = $value;
						}
						if (property_exists(__CLASS__, $property . '_resource') && !empty($value))
						{
							$this->{$property . '_resource'} = gmp_init($value, 16);
						}
					}
					break;
				case 'generate':
					foreach ($data as $method)
					{
						if (method_exists(__CLASS__, $method) && !$this->{$method}())
						{
							throw new error('Could not generate key: ' . $method);
						}
					}
					break;
				case 'id':
					$this->id = $data;
					break;
			}
		}
	}

	public static function fromPreAuth($id, $A, $passwordHash, $salt2)
	{
		$construct = array(
			'values' => array(
				'A' => $A,
				'salt2' => $salt2,
				'password' => $passwordHash
			),
			'id' => $id,
			'generate' => array(
				'salt',
				'x',
				'k',
				'v',
				'B'
			)
		);

		return new srpModel($construct);
	}

	public static function fromReply($id, $array)
	{
		$construct = array(
			'id' => $id,
			'values' => $array,
			'generate' => array(
				'u',
				'serverSessionKey',
				'clientProof',
				'serverProof'
			)
		);
		return new srpModel($construct);
	}

	public static function begin($id)
	{
		$construct = array(
			'id' => $id,
			'generate' => array(
				'A'
			)
		);

		return new srpModel($construct);
	}

	public static function end($id, $array)
	{
		$construct = array(
			'values' => $array,
			'id' => $id,
			'generate' => array(
				'x',
				'k',
				'v',
				'u',
				'clientSessionKey',
				'clientProofFromClient',
				'serverProofFromClient',
			),
		);

		return new srpModel($construct);
	}

	public function H($text)
	{
		$ctx = hash_init(self::ENCRYPTION_METHOD);
		hash_update($ctx, $text);
		return hash_final($ctx, true);
	}

	public static function gmp_bytes($x, $padAmount = false)
	{
		if ($padAmount)
		{
			return hex2bin(str_pad(gmp_strval($x, 16), $padAmount, '0', STR_PAD_LEFT));
		}
		return hex2bin(gmp_strval($x, 16));
	}

	public function generateSecret($bytes = 256)
	{
		$salt = utility::getRandomBytes($bytes);
		return gmp_init(bin2hex($salt), 16);
	}

	public function salt()
	{
		$this->salt = bin2hex(utility::getRandomBytes(256));
		$this->salt_resource = gmp_init($this->salt, 16);

		return true;
	}

	public function A()
	{
		if (!isset($this->g_resource, $this->key_resource))
		{
			return false;
		}

		$this->a_resource = self::generateSecret(256);
		$this->a = gmp_strval($this->a_resource, 16);
		$this->A_resource = gmp_powm($this->g_resource, $this->a_resource, $this->key_resource);
		$this->A = gmp_strval($this->A_resource, 16);

		return true;
	}

	public function B()
	{
		//B = (k * v + pow(g, b, N)) % N
		if (!isset($this->k_resource, $this->v_resource, $this->g_resource, $this->key_resource))
		{
			return false;
		}

		$this->b_resource = self::generateSecret(256);
		$this->b = str_pad(gmp_strval($this->b_resource, 16), 512, '0', STR_PAD_LEFT);
		$this->B_resource = gmp_mod(gmp_add(gmp_mul($this->k_resource, $this->v_resource), gmp_powm($this->g_resource, $this->b_resource, $this->key_resource)), $this->key_resource);
		$this->B = str_pad(gmp_strval($this->B_resource, 16), 512, '0', STR_PAD_LEFT);

		return true;
	}

	public function k()
	{
		if (!isset($this->key_resource, $this->g_resource))
		{
			return false;
		}

		$k_bin = $this->H(self::gmp_bytes($this->key_resource) . self::gmp_bytes($this->g_resource, self::KEY_BUFFER_SIZE));
		$this->k_resource = gmp_init(bin2hex($k_bin), 16);
		#$this->k_resource = gmp_mod($this->k_resource, $this->key_resource);
		$this->k = str_pad(gmp_strval($this->k_resource, 16), 64, '0', STR_PAD_LEFT);

		return true;
	}

	public function x()
	{
		if (empty($this->password) || !isset($this->key_resource))
		{
			return false;
		}

		$x_bin = $this->H(self::gmp_bytes($this->salt_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes(gmp_init(bin2hex($this->H($this->id . ':' . $this->password)), 16), 64));
		$this->x_resource = gmp_init(bin2hex($x_bin), 16);
		$this->x_resource = gmp_mod($this->x_resource, $this->key_resource);
		$this->x = str_pad(gmp_strval($this->x_resource, 16), 64, '0', STR_PAD_LEFT);

		return true;
	}

	public function v()
	{
		if (!isset($this->g_resource, $this->x_resource, $this->key_resource))
		{
			return false;
		}

		$this->v_resource = gmp_powm($this->g_resource, $this->x_resource, $this->key_resource);
		$this->v = gmp_strval($this->v_resource, 16);

		return true;
	}

	public function u()
	{
		if (!isset($this->A_resource, $this->B_resource))
		{
			return false;
		}

		$u_bin = $this->H(self::gmp_bytes($this->A_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->B_resource, self::KEY_BUFFER_SIZE));
		$this->u_resource = gmp_init(bin2hex($u_bin), 16);
		$this->u = str_pad(gmp_strval($this->u_resource, 16), 64, '0', STR_PAD_LEFT);

		return true;
	}

	public function clientSessionKey()
	{
		if (!isset($this->B_resource, $this->k_resource, $this->g_resource, $this->x_resource, $this->key_resource, $this->a_resource, $this->u_resource))
		{
			return false;
		}

		//pow(B - k * pow(g, x, N), a + u * x, N)
		$eq = gmp_powm(gmp_sub($this->B_resource, gmp_mul($this->k_resource, gmp_powm($this->g_resource, $this->x_resource, $this->key_resource))), gmp_add($this->a_resource, gmp_mul($this->u_resource, $this->x_resource)), $this->key_resource);
		$this->clientSessionKey_resource = gmp_init(bin2hex($this->H(self::gmp_bytes($eq, 512))), 16);
		$this->clientSessionKey_resource = gmp_mod($this->clientSessionKey_resource, $this->key_resource);
		$this->clientSessionKey = gmp_strval($this->clientSessionKey_resource, 16);

		return true;
	}

	public function serverSessionKey()
	{
		if (!isset($this->A_resource, $this->v_resource, $this->u_resource, $this->key_resource, $this->b_resource))
		{
			return false;
		}

		//pow(A * pow(v, u, N), b, N)
		$eq = gmp_powm(gmp_mul($this->A_resource, gmp_powm($this->v_resource, $this->u_resource, $this->key_resource)), $this->b_resource, $this->key_resource);
		$this->serverSessionKey_resource = gmp_init(bin2hex($this->H(self::gmp_bytes($eq, 512))), 16);
		$this->serverSessionKey = str_pad(gmp_strval($this->serverSessionKey_resource, 16), 64, '0', STR_PAD_LEFT);

		return true;
	}

	public function clientProof()
	{
		if (!isset($this->key_resource, $this->g_resource, $this->id, $this->salt_resource, $this->A_resource, $this->B_resource, $this->serverSessionKey_resource))
		{
			return false;
		}

		//H(H(N) ^ H(g), H(I), s, A, B, K_c)
		$this->clientProof_resource = gmp_init(bin2hex($this->H(self::gmp_bytes(gmp_xor(gmp_init(bin2hex($this->H(self::gmp_bytes($this->key_resource, self::KEY_BUFFER_SIZE))), 16), gmp_init(bin2hex($this->H(self::gmp_bytes($this->g_resource, self::KEY_BUFFER_SIZE))), 16)), 64) . $this->H($this->id) . self::gmp_bytes($this->salt_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->A_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->B_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->serverSessionKey_resource, 64))), 16);
		$this->clientProof = str_pad(gmp_strval($this->clientProof_resource, 16), 64, '0', STR_PAD_LEFT);

		return true;
	}

	public function clientProofFromClient()
	{
		if (!isset($this->key_resource, $this->g_resource, $this->id, $this->salt_resource, $this->A_resource, $this->B_resource, $this->clientSessionKey_resource))
		{
			return false;
		}

		//H(H(N) ^ H(g), H(I), s, A, B, K_c)
		$this->clientProof_resource = gmp_init(bin2hex($this->H(self::gmp_bytes(gmp_xor(gmp_init(bin2hex($this->H(self::gmp_bytes($this->key_resource, self::KEY_BUFFER_SIZE))), 16), gmp_init(bin2hex($this->H(self::gmp_bytes($this->g_resource, self::KEY_BUFFER_SIZE))), 16)), 64) . $this->H($this->id) . self::gmp_bytes($this->salt_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->A_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->B_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->clientSessionKey_resource, 64))), 16);
		$this->clientProof = str_pad(gmp_strval($this->clientProof_resource, 16), 64, '0', STR_PAD_LEFT);

		return true;
	}

	public function serverProof()
	{
		if (!isset($this->A_resource, $this->clientProof_resource, $this->serverSessionKey_resource))
		{
			return false;
		}

		//H(A, M_c, K_s)
		$this->serverProof_resource = gmp_init(bin2hex($this->H(self::gmp_bytes($this->A_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->clientProof_resource, 64) . self::gmp_bytes($this->serverSessionKey_resource, 64))), 16);
		$this->serverProof = gmp_strval($this->serverProof_resource, 16);

		return true;
	}

	public function serverProofFromClient()
	{
		if (!isset($this->A_resource, $this->clientProof_resource, $this->clientSessionKey_resource))
		{
			return false;
		}

		//H(A, M_c, K_s)
		$this->serverProof_resource = gmp_init(bin2hex($this->H(self::gmp_bytes($this->A_resource, self::KEY_BUFFER_SIZE) . self::gmp_bytes($this->clientProof_resource, 64) . self::gmp_bytes($this->clientSessionKey_resource, 64))), 16);
		$this->serverProof = gmp_strval($this->serverProof_resource, 16);

		return true;
	}
}