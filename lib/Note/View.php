<?php
namespace Note;

class View
	{
	public function my_display()
		{
		return input_group(array(
			input_text('note'),
			input_button('Say It')->stack(array(
				'.table'=>call($this, 'my_save', array(), 'prepend'),
				))
			))->display()
		. div('notes', $this->dataset()->my_display('table'))
		. div('save')
		;
		}

	public function dataset()
		{
		return dataset('note')
		->add_col(input_button('Delete')->stack(array(
			'.save'=>call($this, 'my_delete'),
			), 'remove_row'));
		}

	public function my_save($data = array())
		{
		$id = \Db::match_insert('note', $data);
		return $this->dataset()->one_row(
			select('note', array('*', m('id')->where($id)))->one_row()
			);
		}

	public function my_delete($data = array())
		{
		\Db::query("delete from note where id=" . id_zero(is($data, 'row_id')));
		}
	}
