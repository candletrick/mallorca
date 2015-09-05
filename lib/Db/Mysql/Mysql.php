<?php
namespace Db;

/**
	Mysql interface.
	*/
class Mysql extends \Db\Engine {

	public function connect($host, $username, $password, $database, $port = '3306', $socket = '/var/run/mysqld/mysqld.sock') {
		$conn = mysqli_init(); 
		if ($port) {
			$conn->real_connect($host, $username, $password, $database, $port, $socket, MYSQLI_CLIENT_FOUND_ROWS);
			}
		if (! $conn) die('Error with mysqli_connect..');
		return $conn;
		}

	public function format($type, $name, $alias = '') {
		return $type == 'time' ? "date_format($name, '%l:%i %p')"
		: ($type == 'date' ? "date_format($name, '%c/%e/%Y')"
		: $name);
		}

	public function esc($s) {
		if ($s === '') return 'NULL';
		return "'" . str_replace("'", "\\'", $s) . "'";
		}

	public function ent($s) {
		return "`$s`";
		}

	public function query($query, $second = false) {
		$result = mysqli_query($this->conn, $query);
		if (! $result) {
			$error = mysqli_error($this->conn);
			if (! $second && strpos($error, 'Unknown') !== false) {
				echo \Model\Scan::my_display();
				echo "\n\n";
				self::query($query, true);
				}
			else {
				echo "MySQL error:\n" . $error;
				echo "\n\nQuery:\n" . $query;
				die;
				}
			}
		return $result;
		}

	public function row($handle) {
		if ($handle) return mysqli_fetch_assoc($handle);
		}

	public function free($result) {
		if ($result) mysqli_free_result($result);
		}
	
	public function insert($table, $data) {
		$this->query("insert into " . $this->ent($table)
		. " (" . implode(',', array_map(array($this, 'ent'), array_keys($data))) . ")"
		. " values (" . implode(',', $data) . ")");

		return \Db::value("select @@identity");
		}

	public function update($table, $data, $where = "1 = 0") {
		$pairs = array();
		foreach ($data as $k=>$v) $pairs[] = $this->ent($k) . '=' . $v;
		
		if (empty($pairs)) return 0;

		$query = "update " . $this->ent($table)
		. " set " . implode(',', $pairs)
		. " " . $where;
	
		$this->query($query);

		// return mysqli_affected_rows($this->conn);
		return mysqli_affected_rows($this->conn);
		}

	public function describe($table) {
		return \Db::results("describe $table");
		}
	}
