<?php

abstract class Kashima_IrcCommands
{
	public function message($type, $destination, $messagearray,
	                        $priority = SMARTIRC_MEDIUM
	) {
		if (!is_array($messagearray)) {
			$messagearray = array($messagearray);
		}
		
		switch ($type) {
			case SMARTIRC_TYPE_CHANNEL:
			case SMARTIRC_TYPE_QUERY:
				foreach ($messagearray as $message) {
					$this->send('PRIVMSG '.$destination.' :'.$message, $priority);
				}
				break;
			
			case SMARTIRC_TYPE_ACTION:
				foreach ($messagearray as $message) {
					$this->send('PRIVMSG '.$destination.' :'.chr(1).'ACTION '
						.$message.chr(1), $priority
					);
				}
				break;
			
			case SMARTIRC_TYPE_NOTICE:
				foreach ($messagearray as $message) {
					$this->send('NOTICE '.$destination.' :'.$message, $priority);
				}
				break;
			
			case SMARTIRC_TYPE_CTCP: // backwards compatibilty
			case SMARTIRC_TYPE_CTCP_REPLY:
				foreach ($messagearray as $message) {
					$this->send('NOTICE '.$destination.' :'.chr(1).$message
						.chr(1), $priority
					);
				}
				break;
			
			case SMARTIRC_TYPE_CTCP_REQUEST:
				foreach ($messagearray as $message) {
					$this->send('PRIVMSG '.$destination.' :'.chr(1).$message
						.chr(1), $priority
					);
				}
				break;
			
			default:
				return false;
		}
		
		return $this;
	}
	
	// <IRC methods>
	public function join($channelarray, $key = null, $priority = SMARTIRC_MEDIUM)
	{
		if (!is_array($channelarray)) {
			$channelarray = array($channelarray);
		}
		
		$channellist = implode(',', $channelarray);
		
		if ($key !== null) {
			foreach ($channelarray as $idx => $value) {
				$this->send('JOIN '.$value.' '.$key, $priority);
			}
		} else {
			foreach ($channelarray as $idx => $value) {
				$this->send('JOIN '.$value, $priority);
			}
		}
		
		return $this;
	}

	public function part($channelarray, $reason = null,
	                     $priority = SMARTIRC_MEDIUM
	) {
		if (!is_array($channelarray)) {
			$channelarray = array($channelarray);
		}
		
		$channellist = implode(',', $channelarray);
		
		if ($reason !== null) {
			$this->send('PART '.$channellist.' :'.$reason, $priority);
		} else {
			$this->send('PART '.$channellist, $priority);
		}
		return $this;
	}

	public function kick($channel, $nicknamearray, $reason = null,
	                     $priority = SMARTIRC_MEDIUM
	) {
		if (!is_array($nicknamearray)) {
			$nicknamearray = array($nicknamearray);
		}
		
		$nicknamelist = implode(',', $nicknamearray);
		
		if ($reason !== null) {
			$this->send('KICK '.$channel.' '.$nicknamelist.' :'.$reason, $priority);
		} else {
			$this->send('KICK '.$channel.' '.$nicknamelist, $priority);
		}
		return $this;
	}

	public function getList($channelarray = null, $priority = SMARTIRC_MEDIUM)
	{
		if ($channelarray !== null) {
			if (!is_array($channelarray)) {
				$channelarray = array($channelarray);
			}
			
			$channellist = implode(',', $channelarray);
			$this->send('LIST '.$channellist, $priority);
		} else {
			$this->send('LIST', $priority);
		}
		return $this;
	}

	public function names($channelarray = null, $priority = SMARTIRC_MEDIUM)
	{
		if ($channelarray !== null) {
			if (!is_array($channelarray)) {
				$channelarray = array($channelarray);
			}
			
			$channellist = implode(',', $channelarray);
			$this->send('NAMES '.$channellist, $priority);
		} else {
			$this->send('NAMES', $priority);
		}
		return $this;
	}

	public function setTopic($channel, $newtopic, $priority = SMARTIRC_MEDIUM)
	{
		$this->send('TOPIC '.$channel.' :'.$newtopic, $priority);
		return $this;
	}

	public function getTopic($channel, $priority = SMARTIRC_MEDIUM)
	{
		$this->send('TOPIC '.$channel, $priority);
		return $this;
	}

	public function mode($target, $newmode = null, $priority = SMARTIRC_MEDIUM)
	{
		if ($newmode !== null) {
			$this->send('MODE '.$target.' '.$newmode, $priority);
		} else {
			$this->send('MODE '.$target, $priority);
		}
		return $this;
	}

	public function founder($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '+q '.$nickname, $priority);
	}

	public function defounder($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '-q '.$nickname, $priority);
	}

	public function admin($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '+a '.$nickname, $priority);
	}

	public function deadmin($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '-a '.$nickname, $priority);
	}

	public function op($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '+o '.$nickname, $priority);
	}

	public function deop($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '-o '.$nickname, $priority);
	}

	public function hop($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '+h '.$nickname, $priority);
	}

	public function dehop($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '-h '.$nickname, $priority);
	}

	public function voice($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '+v '.$nickname, $priority);
	}

	public function devoice($channel, $nickname, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '-v '.$nickname, $priority);
	}

	public function ban($channel, $hostmask = null, $priority = SMARTIRC_MEDIUM)
	{
		if ($hostmask !== null) {
			$this->mode($channel, '+b '.$hostmask, $priority);
		} else {
			$this->mode($channel, 'b', $priority);
		}
		return $this;
	}

	public function unban($channel, $hostmask, $priority = SMARTIRC_MEDIUM)
	{
		return $this->mode($channel, '-b '.$hostmask, $priority);
	}

	public function invite($nickname, $channel, $priority = SMARTIRC_MEDIUM)
	{
		return $this->send('INVITE '.$nickname.' '.$channel, $priority);
	}

	public function changeNick($newnick, $priority = SMARTIRC_MEDIUM)
	{
		$this->_nick = $newnick;
		return $this->send('NICK '.$newnick, $priority);
	}

	public function who($target, $priority = SMARTIRC_MEDIUM)
	{
		return $this->send('WHO '.$target, $priority);
	}

	public function whois($target, $priority = SMARTIRC_MEDIUM)
	{
		return $this->send('WHOIS '.$target, $priority);
	}

	public function whowas($target, $priority = SMARTIRC_MEDIUM)
	{
		return $this->send('WHOWAS '.$target, $priority);
	}

	public function quit($quitmessage = null, $priority = SMARTIRC_CRITICAL)
	{
		if ($quitmessage !== null) {
			$this->send('QUIT :'.$quitmessage, $priority);
		} else {
			$this->send('QUIT', $priority);
		}
		
		return $this->disconnect(true);
	}
}
