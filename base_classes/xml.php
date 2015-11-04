<?PHP
class xml
{
	public $xml;
	public $entityType;
	public $result = array();
	public $currentElement;
	public $arrayKey = 0;
	public $root;

	public function __construct($filename, $encoding = 'utf8')
	{
		$this->xml = new XMLReader;
		$this->xml->open($filename, $encoding);
		$this->parseXML();
	}

	public function parseXML()
	{
		while ($this->xml->read())
		{
			if (is_null($this->root))
			{
				$this->root = $this->xml->name;
			}

			if ($this->xml->nodeType == XMLReader::ELEMENT)
			{
				$this->currentElement = $this->xml->name;
				$this->result[$this->root][$this->currentElement][$this->arrayKey] = array();
			}
			elseif ($this->xml->nodeType == XMLReader::SIGNIFICANT_WHITESPACE)
			{
				$this->arrayKey++;
			}

			if ($this->xml->hasAttributes)
			{
				$this->getAttributes();
			}
		}
	}

	public function getAttributes()
	{
		for ($i = 0, $c = $this->xml->attributeCount; $c > $i; $i++)
		{
			$this->xml->moveToNextAttribute();
			if ($this->xml->hasValue)
			{
				$this->result[$this->root][$this->currentElement][$this->arrayKey][$this->xml->name] = $this->xml->value;
			}
		}

		$this->xml->moveToElement();
	}

	public function __destruct()
	{
		$this->xml->close();
	}

	/**
	 *
	 * @param string $filename
	 * @return array
	 */
	public static function xmlToArray($filename)
	{
		return utility::objectToArray(simplexml_load_file($filename), true);
	}

	/**
	 *
	 * @param string $string
	 * @return array
	 */
	public static function xmlStringToArray($string)
	{
		return utility::objectToArray(simplexml_load_string($string, null, LIBXML_NOCDATA), true);
	}
}