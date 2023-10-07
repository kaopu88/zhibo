<?php
namespace Xly;

class Email
{
	var $smtp_port;//端口
	var $time_out;//超时等待
	var $host_name;//主机名
	var $log_file;//日志文件
	var $relay_host;//接收主机step.163.com
	var $debug;//是否开启调试
	var $auth;//是否签名邮件
	var $user;//用户名 123@test.com
	var $pass;//密码
	var $sock;
	var $mail_type;//邮件类型 HTML
	var $user_mail;//用户邮箱

	public function __construct($config=array())
	{
		$config=array_merge(array(
				"debug"=>false,
				"smtp_port"=>25,
				"relay_host"=>"",
				"time_out"=>30,
				"auth"=>true,
				"user"=>"",
				"pass"=>"",
				"host_name"=>"localhost",
				"log_file"=>"",
				"sock"=>false,
				"mail_type"=>'HTML',
				"user_mail"=>"",
		),$config);
		$this->debug = $config['debug'];
		$this->smtp_port =$config['smtp_port'];
		$this->relay_host =$config['relay_host'];
		$this->time_out = $config['time_out'];
		$this->auth = $config['auth'];
		$this->user = $config['user'];
		$this->pass = $config['pass'];
		$this->host_name =$config['host_name'];
		$this->log_file =$config['log_file'];
		$this->sock = $config['sock'];
		$this->mail_type=$config['mail_type'];
		$this->user_mail=$config['user_mail'];
	}

	public function send($data, $from='', $subject = "", $body = "", $mailtype='html', $cc = "", $bcc = "", $additional_headers = "")
	{
		if(is_array($data)) {
			$data['mailtype']  = empty($data['mailtype'])? $this->mail_type:$data['mailtype'];
			$data['mailfrom']  = empty($data['mailfrom'])? $this->user_mail:$data['mailfrom'];
			$data['subject']  = empty($data['subject'])? 'no subject':$data['subject'];
			$data['body']  = empty($data['body']) ? 'no title':$data['body'];
			$from = $data['mailfrom'];
			$subject = $data['subject'];
			$body = $data['body'];
			$mailtype = $data['mailtype'];
			$to = $data['mailto'];
		} else {
			$to = $data;
		}
		$mail_from = $this->get_address($this->strip_comment($from));
		$body = ereg_replace("(^|(\r\n))(\\.)", "\\1.\\3", $body);
		$header='';
		$header .= "MIME-Version:1.0\r\n";
		if($mailtype=="HTML") {
			$header .= "Content-Type:text/html\r\n";
		}
		 $header .= "To: ".$to."\r\n";
		if ($cc != "") {
			$header .= "Cc: ".$cc."\r\n";
		}
		$header .= "From: $from<".$from.">\r\n";
		$header .= "Subject: ".$subject."\r\n";
		$header .= $additional_headers;
		$header .= "Date: ".date("r")."\r\n";
		$header .= "X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
		list($msec, $sec) = explode(" ", microtime());
		$header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".".$mail_from.">\r\n";
		$TO = explode(",", $this->strip_comment($to));
		if ($cc != "") {
			$TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
		}
		if ($bcc != "") {
			$TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
		}
		$sent = TRUE;
		foreach ($TO as $rcpt_to) {
		$rcpt_to = $this->get_address($rcpt_to);
		if (!$this->smtp_sockopen($rcpt_to)) {
			$this->log_write("Error: Cannot send email to ".$rcpt_to."\n");
			$sent = FALSE;
			continue;
		}
		if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body)) {
			$this->log_write("E-mail has been sent to <".$rcpt_to.">\n");
		} else {
			$this->log_write("Error: Cannot send email to <".$rcpt_to.">\n");
			$sent = FALSE;
		}
		fclose($this->sock);
			$this->log_write("Disconnected from remote host\n");
		}
		if($this->debug) {
			echo "<br>";
			echo $header;
		}
		return $sent;
	}
	
	public function debug($debug)
	{
		$this->debug = $debug;
		return $this;
	}
	
	/* Private Functions */
	function smtp_send($helo, $from, $to, $header, $body = "")
	{
		if (!$this->smtp_putcmd("HELO", $helo)) {
			return $this->smtp_error("sending HELO command");
		}
		#auth
		if($this->auth){
		if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user))) {
			return $this->smtp_error("sending HELO command");
		}
		if (!$this->smtp_putcmd("", base64_encode($this->pass))) {
			return $this->smtp_error("sending HELO command");
		}
		}
		if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">")) {
			return $this->smtp_error("sending MAIL FROM command");
		}
		if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">")) {
			return $this->smtp_error("sending RCPT TO command");
		}
		if (!$this->smtp_putcmd("DATA")) {
			return $this->smtp_error("sending DATA command");
		}
		if (!$this->smtp_message($header, $body)) {
			return $this->smtp_error("sending message");
		}
		if (!$this->smtp_eom()) {
			return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
		}
		if (!$this->smtp_putcmd("QUIT")) {
			return $this->smtp_error("sending QUIT command");
		}
			return TRUE;
	}
 
	function smtp_sockopen($address)
	{
		if ($this->relay_host == "") {
		    return $this->smtp_sockopen_mx($address);
		} else {
		    return $this->smtp_sockopen_relay();
		}
	}

	function smtp_sockopen_relay()
	{
		$this->log_write("Trying to ".$this->relay_host.":".$this->smtp_port."\n");
		$this->sock = @fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);
		if (!($this->sock && $this->smtp_ok())) {
            $this->log_write("Error: Cannot connenct to relay host ".$this->relay_host."\n");
            $this->log_write("Error: ".$errstr." (".$errno.")\n");
            return FALSE;
		}
		$this->log_write("Connected to relay host ".$this->relay_host."\n");
		return TRUE;
	}

 
	function smtp_sockopen_mx($address)
	{
		$domain = ereg_replace("^.+@([^@]+)$", "\\1", $address);
		if (!@getmxrr($domain, $MXHOSTS)) {
			$this->log_write("Error: Cannot resolve MX \"".$domain."\"\n");
			return FALSE;
		}
		foreach ($MXHOSTS as $host) {
			$this->log_write("Trying to ".$host.":".$this->smtp_port."\n");
			$this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);
			if (!($this->sock && $this->smtp_ok())) {
				$this->log_write("Warning: Cannot connect to mx host ".$host."\n");
				$this->log_write("Error: ".$errstr." (".$errno.")\n");
				continue;
			}
			$this->log_write("Connected to mx host ".$host."\n");
			return TRUE;
		}
		$this->log_write("Error: Cannot connect to any mx hosts (".implode(", ", $MXHOSTS).")\n");
		return FALSE;
	}
 
	function smtp_message($header, $body)
	{
		fputs($this->sock, $header."\r\n".$body);
		$this->smtp_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));
		return TRUE;
	}
 
	function smtp_eom()
	{
		fputs($this->sock, "\r\n.\r\n");
		$this->smtp_debug(". [EOM]\n"); 
		return $this->smtp_ok();
	}
 
	function smtp_ok()
	{
		$response = str_replace("\r\n", "", fgets($this->sock, 512));
		$this->smtp_debug($response."\n");
        if (!ereg("^[23]", $response)) {
            fputs($this->sock, "QUIT\r\n");
            fgets($this->sock, 512);
            $this->log_write("Error: Remote host returned \"".$response."\"\n");
            return FALSE;
        }
		return TRUE;
	}
 
	function smtp_putcmd($cmd, $arg = "")
	{
		if ($arg != "") {
			if($cmd=="") $cmd = $arg;
			else $cmd = $cmd." ".$arg;
		} 
		fputs($this->sock, $cmd."\r\n");
		$this->smtp_debug("> ".$cmd."\n");
		return $this->smtp_ok();
	}
 
	function smtp_error($string)
	{
		$this->log_write("Error: Error occurred while ".$string.".\n");
		return FALSE;
	}
 
	function log_write($message)
	{
		$this->smtp_debug($message);
		if ($this->log_file == "") {
			return TRUE;
		}
		$message = date("M d H:i:s ").get_current_user()."[".getmypid()."]: ".$message;
		if (!@file_exists($this->log_file) || !($fp = @fopen($this->log_file, "a"))) {
			$this->smtp_debug("Warning: Cannot open log file \"".$this->log_file."\"\n");
			return FALSE;
		}
		flock($fp, LOCK_EX);
		fputs($fp, $message);
		fclose($fp);
		return TRUE;
	}
 
	function strip_comment($address)
	{
		$comment = "\\([^()]*\\)";
		while (ereg($comment, $address)) {
			$address = ereg_replace($comment, "", $address);
		}
		return $address;
	}
 
	function get_address($address)
	{
		$address = ereg_replace("([ \t\r\n])+", "", $address);
		$address = ereg_replace("^.*<(.+)>.*$", "\\1", $address); 
		return $address;
	}
 
	function smtp_debug($message)
	{
		if ($this->debug) {
			echo $message."<br>";
		}
	}
 
	function get_attach_type($image_tag) 
	{
		$filedata = array();
		$img_file_con=fopen($image_tag,"r");
		unset($image_data);
		while ($tem_buffer=AddSlashes(fread($img_file_con,filesize($image_tag))))
		$image_data.=$tem_buffer;
		fclose($img_file_con);
		$filedata['context'] = $image_data;
		$filedata['filename']= basename($image_tag);
		$extension=substr($image_tag,strrpos($image_tag,"."),strlen($image_tag)-strrpos($image_tag,"."));
		switch($extension)
		{
			case ".gif":
			$filedata['type'] = "image/gif";
			break;
			case ".gz":
			$filedata['type'] = "application/x-gzip";
			break;
			case ".htm":
			$filedata['type'] = "text/html";
			break;
			case ".html":
			$filedata['type'] = "text/html";
			break;
			case ".jpg":
			$filedata['type'] = "image/jpeg";
			break;
			case ".tar":
			$filedata['type'] = "application/x-tar";
			break;
			case ".txt":
			$filedata['type'] = "text/plain";
			break;
			case ".zip":
			$filedata['type'] = "application/zip";
			break;
			default:
			$filedata['type'] = "application/octet-stream";
			break;
		}
		return $filedata;
	}
 }
?>