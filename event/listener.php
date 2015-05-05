<?php
/**
*
* Zodiacs extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\zodiacs\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* the path to the images directory
	*
	*@var string
	*/
	protected $zodiacs_path;

	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$phpbb_root_path,
		$php_ext,
		$zodiacs_path)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->images_path = $zodiacs_path;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'							=> 'user_setup',
			'core.viewtopic_cache_user_data'			=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'			=> 'viewtopic_cache_guest_data',
			'core.viewtopic_modify_post_row'			=> 'viewtopic_modify_post_row',
			'core.memberlist_view_profile'				=> 'memberlist_view_profile',
			'core.search_get_posts_data'				=> 'search_get_posts_data',
			'core.search_modify_tpl_ary'				=> 'search_modify_tpl_ary',
		);
	}

	/**
	* Set up the the lang vars
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function user_setup($event)
	{
		$page_name = substr($this->user->page['page_name'], 0, strpos($this->user->page['page_name'], '.'));

		if ($page_name == 'search' || $page_name == 'viewtopic' || $page_name == 'memberlist')
		{
			$this->user->add_lang_ext('rmcgirr83/zodiacs', 'zodiacs');
		}
	}

	/**
	* Update viewtopic user data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_birthday'] = $event['row']['user_birthday'];
		$event['user_cache_data'] = $array;
	}

	/**
	* Update viewtopic guest data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_birthday'] = '';
		$event['user_cache_data'] = $array;
	}
	/**
	* Modify the viewtopic post row
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_modify_post_row($event)
	{
		$zodiac = $this->get_user_zodiac($event['user_poster_data']['user_birthday']);

		$event['post_row'] = array_merge($event['post_row'],array(
			'USER_ZODIAC' => $zodiac,
		));
	}

	/**
	* Display zodiac on viewing user profile
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function memberlist_view_profile($event)
	{
		$zodiac = $this->get_user_zodiac($event['member']['user_birthday']);

		$this->template->assign_vars(array(
			'USER_ZODIAC'	=> $zodiac,
		));
	}

	/**
	* Get birthday on search
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function search_get_posts_data($event)
	{
		$array = $event['sql_array'];
		$array['SELECT'] .= ', u.user_birthday';
		$event['sql_array'] = $array;
	}

	/**
	* Display zodiac on search
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function search_modify_tpl_ary($event)
	{
		if ($event['show_results'] == 'topics')
		{
			return;
		}

		$array = $event['tpl_ary'];
		$zodiac = $this->get_user_zodiac($event['row']['user_birthday']);
		$array = array_merge($array, array(
			'USER_ZODIAC'	=> $zodiac,
		));

		$event['tpl_ary'] = $array;
	}

	/**
	 * Get user zodiac
	 *
	 * @author RMcGirr83
	 * @param string $user_birthday User's Birthday
	 * @return string Zodiac image
	 */
	private function get_user_zodiac($user_birthday)
	{
		$zodiac = '';
		if (!empty($user_birthday))
		{
			list($day, $month) = explode ("-", $user_birthday);
			if ((trim($month) == 3 && $day > 20) || (trim($month) == 4 && $day < 20))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'aries.gif" alt="' . $this->user->lang['ZODIAC'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 4 && $day > 19) || (trim($month) == 5 && $day < 21))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'taurus.gif" alt="' . $this->user->lang['ZODIAC'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 5 && $day > 20) || (trim($month) == 6 && $day < 21))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'gemini.gif" alt="' . $this->user->lang['GEMINI'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 6 && $day > 20) || (trim($month) == 7 && $day < 23))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'cancer.gif" alt="' . $this->user->lang['CANCER'] . '" title="' . $this->user->lang['CANCER'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 7 && $day > 22) || (trim($month) == 8 && $day < 23))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'leo.gif" alt="' . $this->user->lang['LEO'] . '" title="' . $this->user->lang['LEO'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 8 && $day > 22) || (trim($month) == 9 && $day < 23))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'virgo.gif" alt="' . $this->user->lang['VIRGO'] . '" title="' . $this->user->lang['VIRGO'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 9 && $day > 22) || (trim($month) == 10 && $day < 23))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'libra.gif" alt="' . $this->user->lang['LIBRA'] . '" title="' . $this->user->lang['LIBRA'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 10 && $day > 22) || (trim($month) == 11 && $day < 22))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'scorpio.gif" alt="' . $this->user->lang['SCORPIO'] . '" title="' . $this->user->lang['SCORPIO'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 11 && $day > 21) || (trim($month) == 12 && $day < 22))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'sagittarius.gif" alt="' . $this->user->lang['SAGITTARIUS'] . '" title="' . $this->user->lang['SAGITTARIUS'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 12 && $day > 21) || (trim($month) == 1 && $day < 20))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'capricorn.gif" alt="' . $this->user->lang['CAPRICORN'] . '" title="' . $this->user->lang['CAPRICORN'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 1 && $day > 19) || (trim($month) == 2 && $day < 19))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'aquarius.gif" alt="' . $this->user->lang['AQUARIUS'] . '" title="' . $this->user->lang['AQUARIUS'] . '" style="vertical-align:middle;" />';
			}
			else if ((trim($month) == 2 && $day > 18) || (trim($month) == 3 && $day < 21))
			{
				$zodiac = '<img src="' . $this->root_path . $this->images_path . 'pisces.gif" alt="' . $this->user->lang['PISCES'] . '" title="' . $this->user->lang['PISCES'] . '" style="vertical-align:middle;" />';
			}
		}
		return $zodiac;
	}
}
