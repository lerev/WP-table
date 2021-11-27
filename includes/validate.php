<?php

class Plg_Table_Validate
{
	/**
	 * Data
	 * @var array
	 */
	private $_data = array();

	/**
	 * Labels
	 * @var array
	 */
	private $_labels = array();

	/**
	 * Filters
	 * @var array
	 */
	private $_filters = array();

	/**
	 * Rules
	 * @var array
	 */
	private $_rules = array();

	/**
	 * Errors
	 * @var array
	 */
	private $_errors = array(
		'all' => array(),
		'first' => array(),
		'last' => array(),
		'tree' => array(),
	);

	/**
	 * Error fields
	 * @var array
	 */
	private $_error_fields = array();

	/**
	 * Is valide
	 * @var bool
	 */
	private $is_valid = false;

	/**
	 * Error messages
	 * @var array
	 */
	private $_messages = [];

	/**
	 * Create Validate object
	 * @param array $data
	 * @param string $type
	 */
	private function __construct($data)
	{
		$this -> _data = $data;
		$this -> _messages = array(
			'in_array'		=> __('"{field}" must be one of the available options'),
			'min_length'	=> __('"{field}" must be at least "{param1}" characters long'),
			'max_length'	=> __('"{field}" must not exceed "{param1}" characters long'),
			'required'		=> __('"{field}" must not be empty'),
			'regex'			=> __('"{field}" does not match the required format'),
			'email'			=> __('"{field}" must be a email'),
			'equal'			=> __('"{field}" must be a equal "{param1}"'),
			'not_equal'		=> __('"{field}" must not be a equal "{param1}"'),
			'less'			=> __('"{field}" must be a less "{param1}"'),
			'great'			=> __('"{field}" must be a great "{param1}"'),
		);
	}

	/**
	 * Create Validate object
	 * @param array $data
	 * @param string $type
	 * @return \Lance_Library_Validate
	 */
	public static function factory(array $data)
	{
		return new self($data);
	}

	/**
	 * Set labels
	 * @param array $labels labels
	 * @return \Lance_Library_Validate
	 */
	public function setLabels(array $labels)
	{
		foreach($labels as $k => $v)
		{
			$this -> _labels[$k] = $v;
		}

		if(sizeof($this -> _data) == 0)
		{
			$this -> _data = array_map(function()
			{
				return '';
			}, $this -> _labels);
		}

		return $this;
	}

	/**
	 * Get label
	 * @param string $key
	 * @return string
	 */
	public function getLabel($key = '')
	{
		if($key == '')
		{
			return $this -> _labels;
		}
		return $this -> _labels[$key];
	}

	/**
	 * Set filters
	 * @param mixed $field
	 * @param array $filters filters
	 * @return \Lance_Library_Validate
	 */
	public function setFilters($field, array $filters)
	{
		$fields = (array) $field;
		foreach($fields as $field)
		{
			foreach($filters as $k => $v)
			{
				$this -> _filters[$field][$k] = $v;
			}
		}

		return $this;
	}

	/**
	 * Set rulles
	 * @param mixed $field field
	 * @param array $rules rules
	 * @return \Lance_Library_Validate
	 */
	public function setRules($field, array $rules)
	{
		$fields = (array) $field;
		foreach($fields as $field)
		{
			foreach($rules as $k => $v)
			{
				$this -> _rules[$field][$k] = $v;
			}
		}

		return $this;
	}

	/**
	 * Set error messages
	 * @param array $messages
	 * @return \Lance_Library_Validate
	 */
	public function setMessages(array $messages)
	{
		foreach($messages as $k => $v)
		{
			$this -> _messages[$k] = $v;
		}

		return $this;
	}

	/**
	 * Set data
	 * @param array|string $k
	 * @param string $v
	 */
	public function setData($k, $v = '')
	{
		if(is_array($k) == true || is_object($k) == true)
		{
			$this -> _data = $k;
		}
		else
		{
			$this -> _data[$k] = $v;
		}
	}

	/**
	 * Get data
	 * @param string $key
	 * @param string $default
	 * @return array
	 */
	public function getData($key = '', $default = NULL)
	{
		if($key == '')
		{
			return $this -> _data;
		}
		return isset($this -> _data[$key]) ? $this -> _data[$key] : $default;
	}

	/**
	 * Get errors
	 * @param string $list key of error list
	 * @return array
	 */
	public function getErrors($list = 'all')
	{
		return $this -> _errors[$list];
	}

	/**
	 * Get error fields
	 * @return array
	 */
	public function getErrorFields()
	{
		return $this -> _error_fields;
	}

	/**
	 * Add errors
	 * @param string $rule rule or message
	 * @param string $field
	 * @param string $label label
	 * @param string $params
	 */
	public function addError($rule, $field, $label = '', $params = [])
	{
		$a = array('{field}' => $label);

		if(is_array($params))
		{
			foreach ($params as $i => $value)
			{
				$a['{param'.$i.'}'] = is_array($value) || is_object($value) ? '' : (string)$value;
			}
		}

		if(isset($this -> _messages[$rule]))
		{
			$message = $this -> _messages[$rule];
		}
		else
		{
			$message = $rule;
		}

		$this -> _error_fields[$field] = true;
		
		$error = strtr($message, $a);
		$this -> _errors['all'][] = $error;
		$this -> _errors['tree'][$field][] = $error;
		$this -> _errors['last'][$field] = $error;
		if(isset($this -> _errors['first'][$field]) == false)
		{
			$this -> _errors['first'][$field] = $error;
		}
	}

	/**
	 * Check exists error or not
	 * @return bool
	 */
	public function isErrors()
	{
		return sizeof($this -> _errors['all']) ? true : false;
	}

	/**
	 * Vilid data or not
	 * @return bool
	 */
	public function isValid()
	{
		return $this -> is_valid;
	}

	/**
	 * Run validator
	 * @return boolean
	 */
	public function validate()
	{
		//Sets
		$labels_ar = array_keys($this -> _labels);
		$data_ar   = array();

		foreach($labels_ar as $label)
		{
			if(isset($this -> _data[$label]))
			{
				$data_ar[$label] = $this -> _data[$label];
			}
			else
			{
				$data_ar[$label] = '';
			}
		}

		$filters_ar = $this -> _mergeFunction($labels_ar, $this -> _filters);
		foreach($filters_ar as $field => $filters)
		{
			foreach ($filters as $filter_key => $filter_val)
			{
				$function = $filter_key;
				$params = $filter_val;
				if(is_numeric($filter_key))
				{
					$function = $filter_val;
					$params = [];
				}

				array_unshift($params, $data_ar[$field]);

				if(is_callable($function) && is_string($function) == false)
				{
					$data_ar[$field] = call_user_func_array($function, $params);
				}
				else if(method_exists($this, $function))
				{
					$Object = new ReflectionMethod($this, $function);
					if($Object -> isStatic())
					{
						$data_ar[$field] = $Object -> invokeArgs(NULL, $params);
					}
					else
					{
						$data_ar[$field] = call_user_func_array(array($this, $function), $params);
					}
				}
				else if(strpos($function, '::') === FALSE)
				{
					$Object = new ReflectionFunction($function);
					$data_ar[$field] = $Object -> invokeArgs($params);
				}
				else
				{
					list($class, $method) = explode('::', $function, 2);

					$Object = new ReflectionMethod($class, $method);
					$data_ar[$field] = $Object -> invokeArgs(NULL, $params);
				}
			}
		}

		$rules_ar = $this -> _mergeFunction($labels_ar, $this -> _rules);
		foreach ($rules_ar as $field => $rules)
		{
			foreach($rules as $rule_key => $rule_val)
			{
				$function = $rule_key;
				$params = $rule_val;
				if(is_numeric($rule_key))
				{
					$function = $rule_val;
					$params = [];
				}

				array_unshift($params, $data_ar[$field]);

				if(is_callable($function) && is_string($function) == false)
				{
					$params = array_merge($params, array($this));
					$result = call_user_func_array($function, $params);
				}
				else if(method_exists($this, $function))
				{
					$params = array_merge($params, array($this));

					$Object = new ReflectionMethod($this, $function);
					if($Object -> isStatic())
					{
						$result = $Object -> invokeArgs(NULL, $params);
					}
					else
					{
						$result = call_user_func_array(array($this, $function), $params);
					}
				}
				else if(strpos($function, '::') === FALSE)
				{
					$Object = new ReflectionFunction($function);
					$result = $Object -> invokeArgs($params);
				}
				else
				{
					$params = array_merge($params, array($this));
					list($class, $method) = explode('::', $function, 2);

					$Object = new ReflectionMethod($class, $method);
					$result = $Object -> invokeArgs(NULL, $params);
				}

				if($result === false && is_string($function))
				{
					$this -> addError($function, $field, $this -> _labels[$field], $params);
				}
			}
		}

		$this -> _data = $data_ar;
		$this -> is_valid = !$this -> isErrors();

		return $this -> is_valid;
	}

	/**
	 * Megre functions array
	 * @param array $labels_ar labal fields
	 * @param array $function_ar functions
	 * @return array
	 */
	private function _mergeFunction($labels_ar, $function_ar)
	{
		if(isset($function_ar['*']))
		{
			$common_ar = $function_ar['*'];
			unset($function_ar['*']);

			foreach($labels_ar as $label)
			{
				if(isset($function_ar[$label]))
				{
					$function_ar[$label] = array_merge($common_ar, $function_ar[$label]);
				}
				else
				{
					$function_ar[$label] = $common_ar;
				}
			}
		}

		return $function_ar;
	}

	//===========================================================

	/**
	 * Checks a field is empty or not
	 * @param string $t
	 * @return bool
	 */
	public static function required($t)
	{
		return ! in_array($t, array(null, false, '', array()), true);
	}

	/**
	 * Checks a field using regular expression
	 * @param string $t
	 * @param string $expression
	 * @return bool
	 */
	public static function regex($t, $expression)
	{
		return (bool) preg_match($expression, (string) $t);
	}

	/**
	 * Checks a field on the min length
	 * @param type $t
	 * @param int $l
	 * @return bool
	 */
	public static function min_length($t, $l)
	{
		return self::_strlen($t) >= $l;
	}

	/**
	 * Checks a field on the max length
	 * @param type $t
	 * @param int $l
	 * @return bool
	 */
	public static function max_length($t, $l)
	{
		return self::_strlen($t) <= $l;
	}

	/**
	 * Checks a field on email
	 * @param string $t
	 * @return bool
	 */
	public static function email($t)
	{
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $t)) ? false : true;
	}

	/**
	 * Checks a field on email
	 * @param string $t
	 * @return bool
	 */
	public static function email_empty($t)
	{
		if($t == '')
		{
			return true;
		}
		
		return self::email($t);
	}
	
	/**
	 * Checks great than the specified value. If Yes - returns true
	 *
	 * @param string $value
	 * @param string $num
	 * @return bool
	 */
	public static function great($value, $num)
	{
		$value = (double) $value;
		$num = (double) $num;

		if($value > $num)
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks less than the specified value. If Yes - returns true
	 *
	 * @param string $value
	 * @param string $num
	 * @return bool
	 */
	public static function less($value, $num)
	{
		$value = (double) $value;
		$num = (double) $num;

		if($value < $num)
		{
			return true;
		}
		return false;
	}

	/**
	 * Checks is not equal fields. Is equal return false
	 *
	 * @param string $value
	 * @param string $num
	 * @return bool
	 */
	public static function not_equal($value, $num)
	{
		if($value == $num)
		{
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Checks is not equal fields. Is equal return true
	 *
	 * @param string $value
	 * @param string $num
	 * @return bool
	 */
	public static function equal($value,$num)
	{
		if($value == $num)
		{
			return true;
		}
		return false;
	}

	/********************************************************************************************************************/
	/************************************************* PRIVATE METHODS **************************************************/
	/********************************************************************************************************************/

	/**
	 * Get string length
	 *
	 * @param string $str string
	 */
	private static function _strlen($str)
	{
		if(function_exists('mb_strlen'))
		{
			return mb_strlen($str);
		}

		return strlen(utf8_decode($str));
	}
}
