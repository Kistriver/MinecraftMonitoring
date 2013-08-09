<?php
/**
 * Minecraft Server Status Class
 * @copyright	© 2011 Nox Nebula - Patrick Kleinschmidt
 * @website	https://github.com/NoxNebula/MC-Server-Status
 * @license	GNU Public Licence - Version 3
 * @author	Nox Nebula - Patrick Kleinschmidt
 * @author	Alexey Kachalov
 **/

class ms {
	private $Socket, $Info, $to;
	public $Online, $MOTD, $CurPlayers, $MaxPlayers, $IP, $Port, $Error;
	
	public function __construct($IP, $Port = '25565', $timeout='0.5') {
		$this->IP = $IP;
		$this->Port = $Port;
		$this->to = $timeout;
		
		// Remove any protocols from serveraddress
		if(preg_match('/(.*):\/\//', $this->IP)) {
			$this->IP = preg_replace('/(.*):\/\//', '', $this->IP);
		}
		if(strpos($this->IP, '/') !== false) {
			$this->IP = rtrim($this->IP, '/');
			if(strpos($this->IP, '/') !== false) {
				$this->Failed();
				$this->Error = 'Unsupported IP/Domain format, no \'/\'s allowed';
				return;
			}
		}
		if(preg_match_all('/:/', $this->IP, $matches) > 1) {
			unset($matches);
			// IP6
			if(strpos($this->IP, '[') === false && strpos($this->IP, ']') === false)
				$this->IP = '['.$this->IP.']';
		} else if(strpos($this->IP, ':') !== false) {
			$this->Failed();
			$this->Error = 'Unsupported IP/Domain format';
			return;
		}
		
		if($this->Socket = @stream_socket_client('tcp://'.$this->IP.':'.$Port, $ErrNo, $ErrStr, $this->to)) {
			// If IP6 remove brackets
			if(strpos($this->IP, '[') === 0 && strpos($this->IP, ']') === (strlen($this->IP) - 1))
				$this->IP = trim($this->IP, '[]');
			
			$this->Online = true;
			
			fwrite($this->Socket, "\xfe");
			$Handle = fread($this->Socket, 2048);
			//$Handle = str_replace("\x00", '', $Handle);
			//$Handle = substr($Handle, 2);
			//$this->Info = explode("\xa7", $Handle); // Separate Infos
			
			/*if(sizeof($this->Info) == 3) {
				$this->MOTD       = $this->Info[0];
				$this->CurPlayers = (int)$this->Info[1];
				$this->MaxPlayers = (int)$this->Info[2];
				$this->Error      = false;
			} else if(sizeof($this->Info) > 3) { // Handle error, Minecraft don't handle this.
				$Temp = '';
				for($i = 0; $i < sizeof($this->Info) - 2; $i++) {
					$Temp .= ($i > 0 ? '§' : '').$this->Info[$i];
				}
				$this->MOTD       = $Temp;
				$this->CurPlayers = (int)$this->Info[sizeof($this->Info) - 2];
				$this->MaxPlayers = (int)$this->Info[sizeof($this->Info) - 1];
				$this->Error      = 'Faulty motd or outdated script';
			} else {
				$this->Failed();
				$this->Error      = 'Unexpected error, cause may be an outdated script';
			}*/
			
			$Handle = substr($Handle, 1);
			if(strpos($Handle, "\x00\x00")!=0)
			{
				$Handle = explode("\x00\x00", $Handle);
				$Handle = str_replace("\x00", '', $Handle);
				$this->MOTD       = $Handle[3];
				$this->CurPlayers = $Handle[4];
				$this->MaxPlayers = $Handle[5];
				$this->Error      = false;
			}
			else
			{
				$Handle = explode("\xa7", $Handle);
				$Handle = str_replace("\x00", '', $Handle);
				$this->MOTD       = $Handle[0];
				$this->CurPlayers = $Handle[1];
				$this->MaxPlayers = $Handle[2];
				$this->Error      = false;
			}
			
			unset($Handle);
			fclose($this->Socket);
		} else {
			$this->Online = false;
			$this->Failed();
			$this->Error = 'Can not reach the server';
		}
	}
	
	public function Info() {
		return array(
			'MOTD'       => $this->MOTD,
			'CurPlayers' => $this->CurPlayers,
			'MaxPlayers' => $this->MaxPlayers
		);
	}
	
	private function Failed() {
		$this->MOTD       = false;
		$this->CurPlayers = false;
		$this->MaxPlayers = false;
	}
}
?>