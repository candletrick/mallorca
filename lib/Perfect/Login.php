<?php
namespace Perfect;

/**
	Standard login.
	*/
class Login extends \Perfect
	{

	use \NoAuth;

	/** Login id. */
	static public $id = 0;

	/** Escaped id for queries. */
	static public $esc = 0;

	/** User data. */
	static private $data = array();
	
	/**
		\param	string	$name	Data key.
		\return Get user data by key.
		*/
	static public function get($name, $else = '')
		{
		return array_key_exists($name, self::$data) ? self::$data[$name] : $else;
		}
	/**
		Check if the user is logged in.
		Runs every page load.
		*/
	static public function check()
		{
		$id = id_zero(sesh('login_id'));

		self::$data = select('user', ['*', m('id')->where($id)])->one_row() ?: array();

		if (empty(self::$data)) {
			return false;
			}

		self::$id = $id;
		self::$esc = \Db::esc($id);

		return true;
		}

	/**
		\return true If logged in user is admin.
		*/
	static public function is_admin()
		{
		return self::get('is_admin');
		}

	/* DISPLAY FUNCTIONS */

	/**
		Normal display.
		*/
	public function my_display($label = 'Login', $after = 'valid_check', $data = [])
		{
		if (empty($data)) $data = \Request::$data;

		if (! $label) $label = 'Login';

		return ''
		. div('login-wrapper',
			div('control', div('label'), div('input coral', $label)),
			// div('control', div('label'), div('input', alert(true))),
			action_group([
				input_text('email', 20)->label("Em:")
					->set_value(cook('login_email')),
				input_password('password')->label("Pa:")
					->set_value(cook('login_password')),
				input_check('remember')->add_label_class('small')->label('Remember Me')
					->set_value(cook('login_remember')),
				input_button('Login')->add_class('data-enter')->label('ThY')->click(array_merge([
					call($this, $after)->html('.m-content'),
					], website())),
				input_button('Forgot Password')->add_class('forgot')->click([
					call($this, 'forgot_display')->html('.m-content')
					]),
					// "<a class='forgot' href='" . \Path::base_to('user/forgot') . "'>Forgot Password</a>",
				sesh_alert()
				])->data($data)->my_display()
			);
		}

	/**
		Forgot password form.
		*/
	public function forgot_display()
		{
		return div('login-wrapper',
			div('control', div('label'), div('input coral', 'Forgot<br>Password')),
			action_group(array(
				// sesh_alert(),
				input_text('email', 60)->label('Em:'),
				input_button('Send Reset Email')->click([
					call($this, 'handle_forgot'),
					call($this, 'my_display')->html('.m-content')
					// call_path('user/login')
					]),
				))->my_display()
			);
		}

	public function reset_display()
		{
		$get = \Request::$json_get;

		return div('login-wrapper',
			// pv($_REQUEST),
			div('control', div('label'), div('input coral', 'Reset<br>Password')),
			div('notice', (get('email') ? 'For ' . get('email') : '' )),
			// sesh_alert(),
			action_group(array(
				input_password('password', 60),
				input_password('password_confirm', 60)->label('Confirm'),
				input_hidden('link', is($get, 'link')),
				input_hidden('email', is($get, 'email')),
				input_button('Reset')->add_class('data-enter')->click([
					call($this, 'handle_reset'),
					call($this, 'my_display')->html('.m-content')
					// call_path('user/login')
					])
				))->my_display()
			);
		}

	public function dismiss_alert($id = 0)
		{
		if (isset($_SESSION['alert'][$id])) unset($_SESSION['alert'][$id]);
		}

	/**
		*/
	public function handle_forgot()
		{
		self::send_reset_email(is(\Request::$data, 'email'));
		}

	/**
		*/
	public function handle_reset()
		{
		$d = \Request::$data;
		$email = is($d, 'email');
		$password = is($d, 'password');
		$confirm = is($d, 'password_confirm');
		$link = is($d, 'link');

		self::reset_password($email, $password, $confirm, $link);
		}

	/**
		Registration display.
		*/
	public function my_register()
		{
		$link = is(\Request::$json_get, 'confirmation_link');
		if ($link) {
			$ok = self::confirm($link);
			$web = website();
			\Request::send(array_pop($web)); 
			\Request::kill();
			}

		return $this->my_display('Register', 'register');
		}

	/**
		*/
	public function valid_check()
		{
		$ok = $this->validate();
		if (! $ok) {
			\Request::kill();
			return $this->my_display();
			}
		}
	
	/**
		Validate user.
		\param	array	$d	User data array (email, password, confirmed).
		*/
	public function validate($d = array(), $bypass = false)
		{
		$ok = false;
		if (empty($d)) $d = \Request::$data;

		if (empty($d)) {
			// if (! sesh('alert')) alert('Please login below..');
			}
		else {
			$a = \Db::one_row("select * from user where email=" . \Db::esc(is($d, 'email')));

			if (! $bypass) {
				if (! $a) {
					alert("Email: " . is($d, 'email') . " is not recognized.");
					setcookie('login_email', '', -1000);
					setcookie('login_password', '', -1000);
					setcookie('login_remember', '', -1000);
					}
				else if (! $bypass && $a['password'] != self::encrypt(is($d, 'password'), $a['salt'])) {
					alert("Password is not correct.");
					}
				else if (! $a['is_confirmed']) {
					self::send_confirmation_email(is($d, 'email'));
					alert("Please check your email to confirm your account.");
					}
				else $ok = true;
				}

			if ($ok || $bypass) {
				// cookies
				$expire = is($d, 'remember') ? time() + (3600 * 72) : time() - 1000;
				setcookie('login_email', $d['email'], $expire);

				// TODO this is for socrates only
				// $hard = "l0kxmal0y7&*";

				// setcookie('login_password', $d['password'], $expire);
				setcookie('login_password', is($d, 'password'), $expire);
				setcookie('login_remember', is($d, 'remember'), $expire);
				// setcookie('logout', false, (3600 * -1));

				alert('You are now logged in as ' . $a['email'] . '.');
				$_SESSION['login_id'] = $a['id'];
				self::check();
				$ok = true;
				}
			}

		return $ok;
		}

	/**
		Register new user.
		*/
	public function register()
		{
		$d = \Request::$data;
		$ok = self::create(is($d, 'email'), is($d, 'password'), $d);

		\Request::kill();

		if ($ok) {
			alert('An email has been sent to ' . is($d, 'email') . ' for confirmation.');
			return $this->my_display();
			}
		else {
			return $this->my_register();
			}
		}

	/**
		See if user exists.
		*/
	static public function exists($email)
		{
		return \Db::value("select 1 from user where email=" . \Db::esc($email));
		}

	/**
		Create salt.
		*/
	static public function salt()
		{
		return base64_encode(openssl_random_pseudo_bytes(24));
		}

	/**
		Encrypt password.
		*/
	static public function encrypt($password, $salt)
		{
		return hash_hmac('sha256', $salt . $password, "h0gburn");
		}

	/**
		Create new user.
		*/
	static public function create($email, $password, $data = array(), $notify = true)
		{
		if (! $email || ! $password) {
			alert("Please enter an email and password...");
			return;
			}

		if (self::exists($email)) {
			alert("There is already an account for $email.");
			return;	
			}

		$salt = self::salt();
		$new_id = \Db::match_insert("user", array(
			'name'=>is($data, 'name', $email),
			'email'=>$email,
			'salt'=>$salt,
			'password'=>self::encrypt($password, $salt),
			'created_on'=>date("Y-m-d H:i:s"),
			'confirmation_link'=>'',
			'is_confirmed'=>0,
			));

		if ($notify) self::send_confirmation_email($email, $data);
		$_SESSION['verify_email'] = $email;

		return $new_id;
		}

	static public function get_confirmation_link($email, $restore = false)
		{
		$link = self::encrypt($email . now(), self::salt());
		// $full = \Path::http() . \Config::$local_path . "user/register?confirmation_link=$link";

		$ok = \Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date('Y-m-d H:i:s', strtotime('+30 day')),
			'is_confirmed'=>0,
			// if restoring add confirmed so no multiple emails
			), " where email=" . \Db::esc($email) . ($restore ? " and is_confirmed=1" : ''));

		if (! $ok) {
			return false;
			}

		return $link;
		}

	/**
		Send confirmation email.
		*/
	static public function send_confirmation_email($email = '', $data = array(), $restore = false)
		{
		if (! $email) {
			alert('Your session has expired');
			return false;
			}

		$link = self::get_confirmation_link($email, $restore);
		$full = \Path::http() . \Config::$local_path . "user/register?confirmation_link=$link";

		if (! $full) {
			// alert('Link already sent.');
			return;
			}
		else {
			alert('Your session has expired.<br>An email is being sent to restore.');
			}

		if (! $restore) {
			$subject = "Complete registration for " . $_SERVER['HTTP_HOST'];
			$msg = "Please confirm your email address by following this link:<br><br><a target='_blank' href='$full'>$full</a>";
			}
		else {
			$subject = "Session expired for " . $_SERVER['HTTP_HOST'];
			$msg = "Please follow this link to restore your session:<br><br><a target='_blank' href='$full'>$full</a>";
			}

		\Email::send($email, $subject, $msg); 
		}

	/**
		*/
	static public function send_reset_email($email)
		{
		if (! $email) {
			alert("Please enter an email...");
			return;
			}

		$link = self::encrypt($email, self::salt());
		$full = "http://" . $_SERVER['HTTP_HOST'] . \Config::$local_path . "/user/reset&email=$email&link=$link";
		$subject = "Reset Password for " . $_SERVER['HTTP_HOST'];
		$msg = "A request for password reset has been submitted.<br>
		If you requested this, go to this confirmation link to reset your password:<br><br><a href='$full'>Reset Password</a>";

		\Email::send($email, $subject, $msg);
		alert("An email containing a link to reset password has been sent to $email");
		\Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date("Y-m-d H:i:s", strtotime('+1 day'))
			), " where email=" . \Db::esc($email));
		}

	static public function try_confirmation_link()
		{
		// see if link present
		$link = is(\Request::$json_get, 'confirmation_link');
		if ($link) {
			$ok = self::confirm($link);
			if ($ok) {
				return true;
				}
			return false;
			}

		// login with cookies
		$ok = false;
		$email = cook('login_email');
		$password = cook('login_password');
		if (! cook('logout') && ! sesh('logout') && $email && $password) {
			$login = new \Perfect\Login();
			$ok = $login->validate(array(
				'email'=>$email,
				'password'=>$password,
				'remember'=>true
				));

			return $ok;
			}

		$document_id = is(\Request::$json_get, 'document_id');
		if ($document_id) unset($_SESSION['logout']);
		
		if (! $ok && ! sesh('logout')) {
			// try off document_id
			if (! $email && $document_id) {
				// $document_id = is(\Request::$json_get, 'document_id');
				$user = select('document', array(
					m('id')->where($document_id),
					m('email')->left('user', ['id'=>'created_by'])
					))->one_row();
				if (! empty($user)) {
					$email = $user['email'];
					}
				}
			if ($email) {
				// alert('Your session has expired. An email is being sent to re-enable.');
				self::send_confirmation_email($email, [], true);
				}
			else {
				// alert('Please login below..');
				}
			}

		return $ok;
		}

	/**
		Mark user as email confirmed.
		*/
	static public function confirm($code)
		{
		$ok = false;
		$a = \Db::one_row("select * from user where confirmation_link=" . \Db::esc($code)
		. " and datediff(confirmation_expires_at, now())>=0 and is_confirmed<>1");

		if (! empty($a)) {
			\Db::query("update user set is_confirmed=1 where confirmation_link=" . \Db::esc($code));
			// \Email::send("fewkeep@gmail.com", "Confirmation request", "{$a['email']} has confirmed their link.");

			$login = new \Perfect\Login();
			$a['remember'] = true;
			$v = $login->validate($a, true);

			alert("Account confirmed! Please login below.");
			$ok = true;
			}
		else {
			// alert("Game over, you've been found out.");
			// alert("You have alerted coral!<br />This is NOT good.<br />I would get off of your computer immediately if I were you.");
			}

		return $ok;
		}

	/**
		Reset user password.
		*/
	static public function reset_password($email, $password, $confirm, $link)
		{
		if ($password != $confirm) {
			alert('The passwords entered do not match.');
			return false;
			}
		if (\Db::value("select 1 from user where confirmation_link=" . db()->esc($link)
		. " and confirmation_expires_at<now()")) {
			alert('This request is expired. Please re-submit.');
			return false;
			}

		$salt = self::salt();
		$up = \Db::match_update('user', array(
			'password'=>self::encrypt($password, $salt),
			'salt'=>$salt,
			'is_confirmed'=>1
			), " where email=" . db()->esc($email)
			. " and confirmation_link=" . db()->esc($link)
			. " and confirmation_expires_at>now()");

		if ($up) {
			alert('Password reset successfully');
			return true;
			}
		else {
			alert('Error with password reset, please re-submit your request.');
			return false;
			}
		}

	/**
		A function to run before making calls via request.
		TODO make it so that it only calls once
		\param 	string	$class	The name of the class calling.
		*/
	static public function before_call($class)
		{
		$uses = class_uses($class);
		$logged_in = self::check();
		// die(pv($_SESSION));

		if (! $logged_in) {

			// try to login with cookies
			$ok = \Perfect\Login::try_confirmation_link();
			// die(pv($ok));

			if ($ok) {
				// continue
				$web = website();
				\Request::send(array_pop($web));
				\Request::kill();
				}
			if (in_array('NoAuth', $uses)) {
				// OK (login class being called)
				}
			else {
				\Request::send(call_path('user/login'));
				\Request::kill();
				}
			}
		}
	}
