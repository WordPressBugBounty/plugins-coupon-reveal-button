<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class spbcta_DB_Table {
	private $db;

	function __construct() {
		global $wpdb;
		$this->db = $wpdb;
		$this->table_name = $this->db->prefix ."spbcta";
		$this->db_version = "1.0";
	}

	public static function get_instance() {
		static $instance = null;
		if($instance == null){
			$instance = new spbcta_DB_Table();
		}
		return $instance;
	}

	public function create_table() {

       $sql = "
            CREATE TABLE $this->table_name (
                id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                name TINYTEXT NOT NULL,
               	utext LONGTEXT NOT NULL,
               	ureveal LONGTEXT NOT NULL,
                link LONGTEXT NOT NULL,
                color TINYTEXT NOT NULL,
                style TINYTEXT NOT NULL,
                blank INT NOT NULL,
                nofollow INT NOT NULL,
                UNIQUE KEY id (id)
            );
        ";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'spbcta_db_version', $this->db_version );
	}

	public function add($name, $utext, $ureveal, $link, $color, $blank, $nofollow, $style) {
		$name 	= wp_strip_all_tags(wp_unslash($name));
		$utext 		= $this->serialize(wp_unslash($utext));
		$ureveal 	= $this->serialize(wp_unslash($ureveal));
		$link 		= $this->serialize(wp_unslash($link));
		$color 		= $this->serialize(wp_unslash($color));

		$result = $this->db->insert( $this->table_name, array('name' => $name, 'utext' => $utext, 'ureveal' => $ureveal, 'link' => $link, 'color' => $color, 'blank' => $blank, 'nofollow' => $nofollow, 'style' => $style) );
		if($result){
			return $this->db->insert_id;
		} else {
		return false;
		}
	}

	public function update($id, $name, $utext, $ureveal, $link, $color, $blank, $nofollow, $style) {
		$name 	= wp_strip_all_tags(wp_unslash($name));
		$utext 		= $this->serialize(wp_unslash($utext));
		$ureveal 	= $this->serialize(wp_unslash($ureveal));
		$link 		= $this->serialize(wp_unslash($link));
		$color 		= $this->serialize(wp_unslash($color));

		return $this->db->update( $this->table_name, array('name' => $name, 'utext' => $utext, 'ureveal' => $ureveal, 'link' => $link, 'color' => $color, 'blank' => $blank, 'nofollow' => $nofollow, 'style' => $style ), array( 'id' => $id ) );
	}

	public function copy($id, $name){
		$query = $this->db->prepare("INSERT INTO $this->table_name (name, utext, ureveal, link, color, blank, nofollow, style) SELECT (%s), utext, ureveal, link, color, blank, nofollow, style FROM $this->table_name WHERE id IN (%d)", $name, $id);
		return $this->db->query($query);
	}

	public function drop_table() {
		$query = "DROP TABLE $this->table_name";
		return $this->db->query($query);
	}

	public function delete($id) {
		$query = $this->db->prepare("DELETE FROM $this->table_name WHERE id IN (%d)", $id);
		return $this->db->query($query);
	}

	public function get($id){
		$query = $this->db->prepare("SELECT * FROM $this->table_name WHERE id IN (%d)", $id);
		$row = $this->db->get_row($query, ARRAY_A);
		if($row){
			$row['utext'] = $this->unserialize($row['utext']);
			$row['ureveal'] = $this->unserialize($row['ureveal']);
			$row['link'] = $this->unserialize($row['link']);
			$row['color'] = $this->unserialize($row['color']);
			return $row;
		}
		return false;
	}

	public function get_all(){
		$query = "SELECT id, name, link FROM $this->table_name";
		$results = $this->db->get_results($query, ARRAY_A);
		if($results){
			return $results;
		}
		return false;
	}

	private function serialize($item){
		return base64_encode(serialize($item));
	}

	private function unserialize($item){
		return unserialize(base64_decode($item));
	}
	
}

?>