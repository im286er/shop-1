<?php
class Letter {
	static public function sendInviteMail($to, $args) {
		$cfg = C ( 'USER_INVITE' );
		$content = replace_string_vars ( L ( $cfg ['CONTENT'] ), $args );
		$res = self::_sendMail ( $to, $to, L ( $cfg ['TITLE'] ), $content );
		return $res;
	}
	static public function sendResetMail($to, $args) {
		$cfg = C ( 'USER_RESET' );
		$content = replace_string_vars ( L ( $cfg ['CONTENT'] ), $args );
		$res = self::_sendMail ( $to, $to, L ( $cfg ['TITLE'] ), $content );
		return $res;
	}
	static public function sendActiveMail($to, $args) {
		$cfg = C ( 'USER_ACTIVE' );
		$content = replace_string_vars ( L ( $cfg ['CONTENT'] ), $args );
		$res = self::_sendMail ( $to, $to, L ( $cfg ['TITLE'] ), $content );
		return $res;
	}
	static function _sendMail($to, $toname, $title, $content) {
		return think_send_mail ( $to, $toname, $title, $content );
	}
}
?>