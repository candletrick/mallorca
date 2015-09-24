<?php
namespace Input;
require_once 'ext/stripe-php-1.18.0/lib/Stripe.php';

class Stripe extends \Input
	{
	// demo
	// static public $secret_key = "sk_test_BQokikJOvBiI2HlWgH4olfQ2";
	// static public $publishable_key = "pk_test_6pRNASCoBOKtIshFeQd4XMUh";

	// test sherissa
	// static public $secret_key = "sk_test_jOe3ZoOd3ZPKyP3PSN5OWz3E";
	// static public $publishable_key = "pk_test_cgy34UaavgtIE7ccdn584KV1";

	/** Stripe Keys. */
	static public $secret_key;
	static public $publishable_key;

	/** Whether to charge right away. */
	public $charge = false;

	/** Mallorca function to run after. */
	public $after;

	/** The table that the payment references, ex) game_id. */
	public $key_name;

	/** The id for that table. */
	public $key_id;

	public function my_construct($amount = 0, $id = 0, $charge = false)
		{
		// require_once 'ext/stripe-php-1.18.0/lib/Stripe.php';

		\Stripe::setApiKey(is(\Config::$stripe, 'secret_key'));
		// $this->booking_id = $id;
		$this->amount = $amount;
		$this->label = 'Purchase';
		$this->mand = false;
		$this->charge = $charge;
		}

	/**
		Set the key that the payment references.
		*/
	public function set_key($key_name, $key_id)
		{
		$this->key_name = $key_name;
		$this->key_id = $key_id;
		return $this;
		}

	/**
		Mallorca function to run after.
		*/
	public function after($after)
		{
		$this->after = $after;
		return $this;
		}

	/**
		Mark for charging.
		*/
	public function charge()
		{
		$this->charge = true;
		return $this;
		}

	public function my_display()
		{
		ob_start();
		?>
		<input type='button' class='stripe-button ' value='<?php echo $this->label; ?>'>
		<div class='stripe-badge'><?php echo image_tag('stripe.png'); ?></div>
		<script>
		$(document).ready(function() {
			var fm = $('<form />').attr({
				'id' : 'stripe_form',
				'method' : 'post',
				// 'action' : '<?php echo \Path::base_to('submit_stripe'); ?>'
				});
			$('<input />').attr({
				'type' : 'hidden',
				'value' : '<?php echo $this->amount; ?>',
				'name' : 'amount',
				}).appendTo(fm);
			$('<input />').attr({
				'type' : 'hidden',
				'name' : '<?php echo $this->key_name; ?>',
				'value' : '<?php echo $this->key_id; ?>'
				}).appendTo(fm);
				
			fm.appendTo('body');
			/*
			fm.submit(function (e) {
				e.preventDefault();
				$.post('<?php echo \Path::base_to('submit_stripe'); ?>', $('#stripe_form').serialize(), function (html) {
					// get_refresh('&current_group=receipt');
					run_stack(<?php echo stack(
						); ?>);
					setTimeout(function() {
						$('.details').append(html);
						}, 500);
					});
				return false;
				});
				*/

			var stripeToken = function (res) {
				var $input = $('<input type=hidden name=stripeToken />').val(res.id);
				// $('#stripe_form').append($input).submit();
				$('#stripe_form').append($input);
				Mallorca.run_stack("<?php echo stack([
					callStatic('Input\Stripe', 'save'),
					$this->after
					]); ?>", $('#stripe_form').serialize());
				};

			$('.stripe-button').unbind('click').click(function () {
				<?php if ($this->charge) : ?>
				StripeCheckout.open({
					key : '<?php echo is(\Config::$stripe, 'publishable_key'); ?>',
					address : false,
					amount : '<?php echo $this->amount * 100; ?>',
					currency : 'usd',
					email : '<?php echo \Login::get('email'); ?>',
					name : 'Checkout',
					description : 'Booking Fee',
					'panel-label' : 'Confirm Amount : ',
					token : stripeToken
					});
				<?php else: ?>
				Mallorca.run_stack("<?php echo stack([
					callStatic('Input\Stripe', 'save'),
					$this->after
					]); ?>", $('#stripe_form').serialize());
				/*
				get_refresh('&<?php echo http_build_query(array(
					'charge'=>1
					)); ?>');
					*/
				<?php endif; ?>
				});
			});
		</script>
		<?php
		return ob_get_clean();
		}

	static public function run_charge($booking_id)
		{
		$booking = \Db::one_row("select created_by, charged from booking where id=" . $booking_id);
		$user_id = is($booking, 'created_by');
		$charged = is($booking, 'charged');
		$amount = \Db::value("select amount from payment where booking_id=" . $booking_id);
		$customer_id = self::get_customer_id($user_id);

		if ($charged) {
			alert('Booking has already been charged.');
			return false;
			}

		try {
			\Stripe::setApiKey(\Input\Stripe::$secret_key);
			$charge = \Stripe_Charge::create(array(
				'customer' => $customer_id,
				'amount'   => $amount * 100,
				'currency' => 'usd'
				));
			}
		// Since it's a decline, Stripe_CardError will be caught
		catch(\Stripe_CardError $e) { 
			die(pv($e));
			/*
			$body = $e->getJsonBody();
			$err = $body['error'];
			return ''
			. 'Status is:' . $e->getHttpStatus() . "\n"
			. 'Type is:' . $err['type'] . "\n"
			. 'Code is:' . $err['code'] . "\n"
			. 'Param is:' . $err['param'] . "\n"
			. 'Message is:' . $err['message'] . "\n"
			;
			*/
			}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe_InvalidRequestError $e) {
			die(pv($e));
			}
		catch (\Stripe_AuthenticationError $e) {
			die(pv($e));
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			}
		catch (\Stripe_ApiConnectionError $e) {
			die(pv($e));
			// Network communication with Stripe failed
			}
		catch (\Stripe_Error $e) {
			die(pv($e));
			// Display a very generic error to the user, and maybe send
			// yourself an email
			}
		catch (\Exception $e) { // Something else happened, completely unrelated to Stripe }
			die(pv($e));
			}

		// die(pv($charge));
		\Db::match_update('booking', array(
			// 'stripe_customer_id'=>$customer->id,
			'charged'=>1,
			), " where id=" . $booking_id);

		return true;
		}

	static public function get_customer_id($user_id, $token = null)
		{
		$row = \Db::one_row("select email, stripe_customer_id from user where id=" . \Db::esc($user_id));
		$customer_id = is($row, 'stripe_customer_id');
		$email = is($row, 'email');

		if (! $customer_id && $token) {
			$customer = \Stripe_Customer::create(array(
				'email'=>$email,
				'card'=>$token
				));

			\Db::match_update('user', array('stripe_customer_id'=>$customer->id), " where id=" . \Db::esc($user_id));
			$customer_id = $customer->id;
			}

		return $customer_id;
		}

	static public function save()
		{
		$data = \Request::$data;

		if (empty($data)) return;

		\Stripe::setApiKey(is(\Config::$stripe, 'secret_key'));
		$token  = is($data, 'stripeToken');

		$customer_id = self::get_customer_id(\Login::$id, $token);

		$data = array_merge($data, who_when());

		$id = \Db::match_insert('payment', $data);
		/*
		\Db::match_update('booking', array(
			// 'stripe_customer_id'=>$customer->id,
			'charged'=>0,
			'paid'=>1,
			'receipt_seen'=>1,
			), " where id=" . id_zero(post('booking_id')));
			*/

		// \Notify::booking(post('booking_id'), 'paid');
		return $id;

		/*
		$notice = "<h2>Thank You</h2>"
		. "<p>Your request and payment details have been received.<br>"
		. "We will notify you as soon as your reservation is confirmed.</p>"
		;
		// alert($notice);
		return $notice;
		// and you will be charged $" . post('amount') . " has been submitted.</p>";
		*/
		}
	}
