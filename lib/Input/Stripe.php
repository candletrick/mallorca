<?php
namespace Input;

class Stripe extends \Input
	{
	// demo
	// static public $secret_key = "sk_test_BQokikJOvBiI2HlWgH4olfQ2";
	// static public $publishable_key = "pk_test_6pRNASCoBOKtIshFeQd4XMUh";

	// test sherissa
	static public $secret_key = "sk_test_jOe3ZoOd3ZPKyP3PSN5OWz3E";
	static public $publishable_key = "pk_test_cgy34UaavgtIE7ccdn584KV1";

	public function my_construct($amount = 0, $id = 0)
		{
		// require_once 'ext/stripe-php-1.18.0/lib/Stripe.php';
		\Stripe::setApiKey(self::$secret_key);
		$this->booking_id = $id;
		$this->amount = $amount;
		$this->label = '';
		$this->mand = false;
		}

	public function my_display()
		{
		ob_start();
		?>
		<input type='button' class='stripe-button button' value='Purchase'>
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
				'name' : 'booking_id',
				'value' : '<?php echo $this->booking_id; ?>'
				}).appendTo(fm);
				
			fm.appendTo('body');
			fm.submit(function (e) {
				e.preventDefault();
				$.post('<?php echo \Path::base_to('submit_stripe'); ?>', $('#stripe_form').serialize(), function (html) {
					get_refresh('&current_group=receipt');
					setTimeout(function() {
						$('.details').append(html);
						}, 500);
					});
				return false;
				});

			var stripeToken = function (res) {
				var $input = $('<input type=hidden name=stripeToken />').val(res.id);
				$('#stripe_form').append($input).submit();
				};

			$('.stripe-button').click(function () {
				StripeCheckout.open({
					key : '<?php echo \Input\Stripe::$publishable_key; ?>',
					address : false,
					amount : '<?php echo $this->amount * 100; ?>',
					currency : 'usd',
					name : 'Checkout',
					description : 'Pay Booking Fee',
					token : stripeToken
					});
				});
			});
		</script>
		<?php
		return ob_get_clean();
		}

	static public function save()
		{
		if (empty($_POST)) return;
		\Stripe::setApiKey(\Input\Stripe::$secret_key);
		$token  = $_POST['stripeToken'];

		try {
			$customer = \Stripe_Customer::create(array(
				'email' => \Login::get('email'),
				'card'  => $token
				));

			$charge = \Stripe_Charge::create(array(
				'customer' => $customer->id,
				'amount'   => post('amount') * 100,
				'currency' => 'usd'
				));
			}
		catch(\Stripe_CardError $e) { // Since it's a decline, Stripe_CardError will be caught
			$body = $e->getJsonBody();
			$err = $body['error'];
			return ''
			. 'Status is:' . $e->getHttpStatus() . "\n"
			. 'Type is:' . $err['type'] . "\n"
			. 'Code is:' . $err['code'] . "\n"
			// param is '' in this ca
			. 'Param is:' . $err['param'] . "\n"
			. 'Message is:' . $err['message'] . "\n"
			;
			}
		catch (\Stripe_InvalidRequestError $e) {
			ob_start();
			print_var($e);
			return ob_get_clean();
			// Invalid parameters were supplied to Stripe's API
			}
		catch (\Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			}
		catch (\Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			}
		catch (\Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			}
		catch (\Exception $e) { // Something else happened, completely unrelated to Stripe }
			}

		$data = array_merge($_POST, who_when());

		$id = \Db::match_insert('payment', $data);
		\Db::match_update('booking', array(
			'paid'=>1,
			'receipt_seen'=>1,
			), " where id=" . id_zero(post('booking_id')));

		\Notify::booking(post('booking_id'), 'paid');

		return "<h2>Thank You</h2><p>Your payment to Bananaberry Sitters LLC of $" . post('amount') . " has been submitted.</p>";
		}
	}
