<?php
namespace Note;

class View
	{
	public function my_display()
		{
		return input_group(array(
			input_text('note', 140)->label('New Idea'),
			input_button('Create')->stack(array(
				'.table'=>call($this, 'my_save', array(), 'prepend'),
				), 'clear_inputs')->add_class('data-enter all-data')
			))->display()
		. div('notes', $this->dataset()->my_display('table'))
		. div('save')
		;
		}

	public function dataset()
		{
		return dataset('note', array('*', m('parent_id')->null()))
			->row(function ($row) {
			return array(
				input_check('check_' . $row['id']),
				($row['child_count'] ? input_button('open')->label($row['child_count'])->stack(array(
					'this'=>call('Note\View', 'children')
					))
				: ''),
				$row['note'],
				input_button('Delete')->stack(array('.save'=>call('Note\View', 'my_delete')), 'remove_row'),
				);
			});
		}

	public function children($data = array())
		{
		}

	public function my_save($data = array())
		{
		$id = \Db::match_insert('note', $data);

		// nest
		$child = $this->check_strip($data);
		if (! empty($child)) {
			\Db::match_update('note', array('parent_id'=>$id, "where id in (" . implode(',', $child) . ")"));
			\Db::match_update('note', array('child_count'=>count($child)), "where id=$id");
			}

		return $this->dataset()->one_row(
			select('note', array('*', m('id')->where($id)))->one_row()
			);
		}

	public function my_delete($data = array())
		{
		\Db::query("delete from note where id=" . id_zero(is($data, 'row_id')));
		}

	/**
		From an array such as check_1 => 0, check_2 => 1, check_3 => 1, return [2, 3]
		*/
	public function check_strip($data = array(), $mod = 'check_')
		{
		$strip = array();
		foreach ($data as $k=>$v) {
			if (strpos($k, $mod) !== false && $v) {
				list($_, $_id) = explode('_', $k);
				$strip[] = $_id;
				}
			}
		return $strip;
		}
	}
