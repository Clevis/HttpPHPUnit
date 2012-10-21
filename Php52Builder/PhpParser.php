<?php

namespace HttpPHPUnit\PHP52Builder;

use /*HttpPHPUnit\*/Nette;


/**
 * Simple tokenizer for PHP.
 */
class PhpParser extends Nette\Utils\Tokenizer
{

	function __construct($code)
	{
		$this->ignored = array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE);
		foreach (token_get_all($code) as $token) {
			$this->tokens[] = is_array($token) ? self::createToken($token[1], $token[0]) : $token;
		}
	}



	public function replace($s, $start = NULL)
	{
		for ($i = ($start === NULL ? $this->position : $start) - 1; $i < $this->position - 1; $i++) {
			$this->tokens[$i] = '';
		}
		$this->tokens[$this->position - 1] = $s;
	}

	/**
	 * @param string
	 * @return string
	 * @author David Grudl
	 * @author Petr Procházka
	 */
	public static function replaceClosures($s)
	{
		// replace closures with create_function
		$parser = new PhpParser($s);
		$s = '';
		while (($token = $parser->fetch()) !== FALSE) {
			if ($parser->isCurrent(T_FUNCTION) && $parser->isNext('(')) { // lamda functions
				$parser->fetch('(');
				$token = "create_function('" . $parser->fetchUntil(')') . "', '";
				$parser->fetch(')');
				if ($use = $parser->fetch(T_USE)) {
					$parser->fetch('(');
					$token .= 'extract(HttpPHPUnit_PHP52_Callback::$vars[\'.HttpPHPUnit_PHP52_Callback::uses(array('
						. preg_replace('#&?\s*\$([^,\s]+)#', "'\$1'=>\$0", $parser->fetchUntil(')'))
						. ')).\'], EXTR_REFS);';
					$parser->fetch(')');
				}
				$body = '';
				$deep = 0;
				do {
					$body .= $parser->fetchUntil('}', '{');
					$next = $parser->fetchToken();
					$body .= $next['value'];
					if ($parser->isCurrent('{'))
					{
						$deep++;
					}
					else if ($parser->isCurrent('}'))
					{
						$deep--;
					}
				} while ($next !== false AND $deep > 0);

				$body = substr(static::replaceClosures('<?php ' . $body), 6);

				$token .= substr(var_export(substr(trim($body), 1, -1), TRUE), 1, -1) . "')";
			}
			$s .= $token;
		}
		return $s;
	}

	/**
	 * @param string
	 * @return string
	 */
	public static function replaceDirConstant($s)
	{
		return str_replace('__DIR__', 'dirname(__FILE__)', $s);
	}

	/**
	 * @param string
	 * @return string
	 */
	public static function replaceLateStaticBinding($s)
	{
		$s = str_replace('new static', 'new self', $s);
		$s = str_replace('static::', 'self::', $s);
		$s = str_replace('get_called_class()', '__CLASS__', $s);
		return $s;
	}

}

