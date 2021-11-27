<?php defined('ABSPATH') || exit;

class Plg_Table_Helpers
{
	/**
	 * Flash Init
	 */
	public static function flashInit()
	{
		if(session_id() == false)
		{
			session_start();
		}

		add_action('admin_notices', function()
		{
			if(isset($_SESSION['flash_redirect']))
			{
				self::flashShow($_SESSION['flash_redirect']['class'], $_SESSION['flash_redirect']['message']);
				unset($_SESSION['flash_redirect']);
			}
		});
	}
	
	/**
	 * Flash Redirect
	 * @param string $uri
	 * @param string $message
	 * @param bool $type
	 */
	public static function flashRedirect($uri, $message, $type = true)
	{
		$_SESSION['flash_redirect'] = [
			'class'   => $type == true ? 'updated' : 'error',
			'message' => $message,
		];
		wp_redirect($uri);
		exit;
	}
	
	/**
	 * Flash Show
	 */
	public static function flashShow($class, $message)
	{
		$messages = (array) $message;
		
		echo '<div class="'.$class.' notice is-dismissible">';
		echo '<button type="button" class="notice-dismiss"></button>';
		foreach($messages as $message)
		{
			echo '<p>'.$message.'</p>';
		}
		echo '</div>';
	}
	

	//===========================================================
	// Request
	//===========================================================
	/**
	 * Check, REQUEST_METHOD is POST
	 *
	 * @return bool
	 */
	public static function isRequestPost()
	{
		 return $_SERVER['REQUEST_METHOD'] == "POST";
	}

	/**
	 * Check, is Ajax
	 *
	 * @return bool
	 */
	public static function isRequestAjax()
	{
		 return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}
	

	//===========================================================
	// View
	//===========================================================
	/**
	 * Get template content
	 *
	 * @param string $path path to view
	 * @param array $array data array
	 * @param string $ext file extension
	 * @return string
	 */
	public static function view($path, $array = [], $ext = 'php')
	{
		if(is_array($array))
		{
			extract($array, EXTR_SKIP);
		}

		ob_start();

		try
		{
			include $path.'.'.$ext;
		}
		catch (Exception $e)
		{
			ob_end_clean();

			throw $e;
		}

		return ob_get_clean();
	}
}