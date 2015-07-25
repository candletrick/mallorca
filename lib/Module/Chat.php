<?php
namespace Module;

/**
	Simple Chat module based on RefreshForm.
	*/
class Chat extends \Module\RefreshForm
	{
	public function __construct($index)
		{
		\Login::only();

		if (post($this->my_table()))
			{
			$data = array_merge($_POST, who_when());
			\Db::match_insert($this->my_table(), $data);
			}

		parent::__construct($index);
		}

	public function my_list()
		{
		$send_to = id_zero(req('send_to', 0));
		$me = \Login::$esc;
		$show = $send_to ? 'show' : '';
		$query = $this->my_query();
		$rows = is_object($query) ? $query->results() : \Db::results($query);

		return ''
			. "<div class='lookup-wrapper'>"
			. "<div class='banner'>"
				. "<div class='title'>" . _to_words($this->my_table()) . "s</div>"
				. rug()
				. "</div>"
			. "<div class='steps-expander' onClick=\"$('.steps').slideToggle(200);\">"
			. "&darr; Touch to show conversation list.</div>"
			. "<div class='steps $show'>"
			. "<ul>"
			. implode(array_map(function ($row) use ($send_to) {
				$click = " onClick=\"get_comments('&send_to={$row['id']}');\" ";
				$class = $send_to == $row['id'] ? 'current' : '';
				$text = "<span class='text'>{$row['text']}</span>";
				return "<li class='$class' $click>$text</li>";
				}, $rows))
				. "</ul></div>"
			. \Form\Create::control_group($this->my_fields())
			. "<div class='messages'>"
			. ul(\Db::results($this->my_list_query()))
					// ->column_fn('created_on', function ($x) { return how_long_ago($x); })
					->row(function ($row) {
						return "<b>{$row['name']}:</b> {$row['text']} <span class='how_long_ago'>"
						. how_long_ago($row['created_on']) . "</span>";
						})
			. "</div>"
			. "</div>"
			;
		}

	public function my_list_query()
		{
		$send_to = id_zero(req('send_to', 0));
		$me = \Login::$esc;

		return "select a.message, a.created_on, b.name
		from message a
		left outer join user b on a.created_by=b.id
		where (a.created_by=$me and send_to=$send_to) or (a.created_by=$send_to and send_to=$me)"
		;
		}

	public function my_query()
		{
		return "select
			a.id, a.name, count(*) as num
		from user a
		left outer join message b on a.id=b.send_to or a.id=b.created_by
		where a.id<>" . \Login::$esc
		. " group by a.id, a.name ";
		}

	public function my_fields()
		{
		$send_to = id_zero(req('send_to', 0));
		$me = \Login::$esc;

		return array(
			input_select('send_to', \Db::two_column_array("select id, name from user where id<>$me"))->set_value($send_to),
			input_textarea('message'),
			input_button('Send')
			);
		}

	public function my_table()
		{
		return 'message';
		}
	}
