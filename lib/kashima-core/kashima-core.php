<?php

// debug //////////
echo "kashima-core start\n";
echo memory_get_usage(true) . "\n";

// ------- PHP code ----------
require_once 'defines.php';
require_once 'irc-commands.php';
require_once 'message-handler.php';

require_once 'kashima-core-error.php';
require_once 'kashima-core-data.php';
require_once 'kashima-core-user.php';
require_once 'kashima-core-channel-user.php';


class Kashima_Core extends Kashima_MessageHandler
{

	const DEF_LOGFILE = 'Net_SmartIRC.log';
	const IP_PATTERN = '/(?:(?:(?:[0-9]{1,3}\.){3}[0-9]{1,3})|(?:\[[0-9A-Fa-f:]+\])|(?:[a-zA-Z0-9-_.]+)):[0-9]{1,5}/';
	const DEF_TX_RX_TIMEOUT = 300;
	const DEF_AUTORETRY_MAX = 5;
	const DEF_RECONNECT_DELAY = 10000;
	const DEF_DISCONNECT_TIME = 1000;
	const DEF_SEND_DELAY = 250;
	const DEF_MAX_TIMER = 300000;
	const DEF_RECEIVE_DELAY = 100;

	public $nreplycodes;
	protected $_logdestination = SMARTIRC_STDOUT; // 0
	protected $_debuglevel = SMARTIRC_DEBUG_NOTICE;
	protected $_logfilefp = 0;
	protected $_logfile = self::DEF_LOGFILE;
	protected $_actionhandler = array();
	protected $_actionhandlerid = 0;
	protected $_address;
	protected $_port;
	protected $_bindto = '0:0';
	protected $_socket;
	protected $_autoretrycount = 0;
	protected $_connectionerror = false;
	protected $_lasttx;
	protected $_lastrx;
	protected $_rxtimeout = self::DEF_TX_RX_TIMEOUT;
	protected $_timehandlerid = 0;
	protected $_timehandler = array();
	protected $_mintimer = false;
	protected $_state = SMARTIRC_STATE_DISCONNECTED;
	protected $_loggedin = false;
	protected $_channels = array();
	protected $_autoretry = false;
	protected $_autoretrymax = self::DEF_AUTORETRY_MAX;
	protected $_reconnectdelay = self::DEF_RECONNECT_DELAY;
	protected $_nick;
	protected $_username;
	protected $_realname;
	protected $_usermode;
	protected $_password;
	protected $_disconnecttime = self::DEF_DISCONNECT_TIME;
	protected $_channelsyncing = false;
	protected $_usersyncing = false;
	protected $_users = array();
	protected $_performs = array();
	protected $_messagebuffer = array(
		SMARTIRC_HIGH     => array(),
		SMARTIRC_MEDIUM   => array(),
		SMARTIRC_LOW 	  => array(),
	);
	protected $_interrupt = false;
	protected $_lastsentmsgtime = 0;
	protected $_messagebuffersize;
	protected $_senddelay = self::DEF_SEND_DELAY;
	protected $_maxtimer = self::DEF_MAX_TIMER;
	protected $_txtimeout = self::DEF_TX_RX_TIMEOUT;
	protected $_receivedelay = self::DEF_RECEIVE_DELAY;

	// ==========================================================================================
	// functions

	public function __construct() {
		$this->nreplycodes = &$GLOBALS['SMARTIRC_nreplycodes'];

		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->setLogDestination(SMARTIRC_BROWSEROUT);
		}
	}

	protected function &throwError($message) {
		return new Kashima_Core_Error($message);
	}

	public function setLogDestination($type) {
		switch ($type) {
			case SMARTIRC_FILE:
			case SMARTIRC_STDOUT:
			case SMARTIRC_SYSLOG:
			case SMARTIRC_BROWSEROUT:
			case SMARTIRC_NONE:
				$this->_logdestination = $type;
				break;

			default:
				$this->log(SMARTIRC_DEBUG_NOTICE,
					'WARNING: unknown logdestination type ('.$type
					.'), will use STDOUT instead', __FILE__, __LINE__);
				$this->_logdestination = SMARTIRC_STDOUT;
		}
		return $this->_logdestination;
	}

	public function log($level, $entry, $file = null, $line = null) {
		if ( !( is_integer($level) && ($level & SMARTIRC_DEBUG_ALL)) ) {
			$this->log(SMARTIRC_DEBUG_NOTICE,
				'WARNING: invalid log level passed to log() ('.$level.')',
				__FILE__, __LINE__
			);
			return false;
		}

		if (!($level & $this->_debuglevel) || $this->_logdestination == SMARTIRC_NONE ) return true;

		if (substr($entry, -1) != "\n") $entry .= "\n";

		$entry = 'unknown(0) '.$entry;
		if ($file !== null && $line !== null) {
			$file = basename($file);
			$entry = $file.'('.$line.') '.$entry;
		}

		$formattedentry = date('M d H:i:s ').$entry;

		switch ($this->_logdestination) {
			case SMARTIRC_STDOUT:
				echo $formattedentry;
				flush();
				break;

			case SMARTIRC_BROWSEROUT:
				echo '<pre>'.htmlentities($formattedentry).'</pre>';
				break;

			case SMARTIRC_FILE:
				if (!is_resource($this->_logfilefp)) {
					$this->_logfilefp = fopen($this->_logfile,'w');
					if ($this->_logfilefp === null) {
						$this->_logfilefp = fopen($this->_logfile,'a');
					}
				}

				fwrite($this->_logfilefp, $formattedentry);
				fflush($this->_logfilefp);
				break;

			case SMARTIRC_SYSLOG:
				if (!is_int($this->_logfilefp)) {
					$this->_logfilefp = openlog(
						'Net_SmartIRC',
						LOG_NDELAY,
						LOG_DAEMON
					);
				}

				syslog(LOG_INFO, $entry);
		}
		return true;
	}

	public function registerActionHandler($handlertype, $regexhandler, &$object, $methodname ) {

		if (!($handlertype & SMARTIRC_TYPE_ALL)) {
			$this->log(SMARTIRC_DEBUG_NOTICE, 'WARNING: passed invalid handler'
				.'type to registerActionHandler()', __FILE__, __LINE__
			);
			return false;
		}

		$id = $this->_actionhandlerid++;
		$this->_actionhandler[] = array(
			'id' => $id,
			'type' => $handlertype,
			'message' => $regexhandler,
			'object' => &$object,
			'method' => $methodname,
		);
		$this->log(SMARTIRC_DEBUG_ACTIONHANDLER, 'DEBUG_ACTIONHANDLER: '
			.'actionhandler('.$id.') registered', __FILE__, __LINE__
		);
		return $id;
	}

	public function connect($addr, $port = 6667, $reconnecting = false) {
		ob_implicit_flush();
		$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: connecting',
			__FILE__, __LINE__
		);

		if ($hasPort = preg_match(self::IP_PATTERN, $addr)) {
			$colon = strrpos($addr, ':');
			$this->_address = substr($addr, 0, $colon);
			$this->_port = (int) substr($addr, $colon + 1);
		} elseif ($hasPort === 0) {
			$this->_address = $addr;
			$this->_port = $port;
			$addr .= ':' . $port;
		}

		$timeout = ini_get("default_socket_timeout");
		$context = stream_context_create(array('socket' => array('bindto' => $this->_bindto)));
		$this->log(SMARTIRC_DEBUG_SOCKET, 'DEBUG_SOCKET: binding to '.$this->_bindto,
			__FILE__, __LINE__);


		if ($this->_socket = stream_socket_client($addr, $errno, $errstr,
			$timeout, STREAM_CLIENT_CONNECT, $context)
		) {
			if (!stream_set_blocking($this->_socket, 0)) {
				$this->log(SMARTIRC_DEBUG_SOCKET, 'DEBUG_SOCKET: unable to unblock stream',
					__FILE__, __LINE__
				);
				$this->throwError('unable to unblock stream');
			}

			$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: connected',
				__FILE__, __LINE__
			);

			$this->_autoretrycount = 0;
			$this->_connectionerror = false;

			$this->registerTimeHandler($this->_rxtimeout * 125, $this, '_pingcheck');

			$this->_lasttx = $this->_lastrx = time();
			$this->_updatestate();
			return $this;
		}

		$error_msg = "couldn't connect to \"$addr\" reason: \"$errstr ($errno)\"";
		$this->log(SMARTIRC_DEBUG_SOCKET, 'DEBUG_NOTICE: '.$error_msg,
			__FILE__, __LINE__
		);
		$this->throwError($error_msg);

		return ($reconnecting) ? false : $this->reconnect();
	}

	public function registerTimeHandler($interval, &$object, $methodname) {
		$id = $this->_timehandlerid++;

		$this->_timehandler[] = array(
			'id' => $id,
			'interval' => $interval,
			'object' => &$object,
			'method' => $methodname,
			'lastmicrotimestamp' => microtime(true),
		);
		$this->log(SMARTIRC_DEBUG_TIMEHANDLER, 'DEBUG_TIMEHANDLER: timehandler('
			.$id.') registered', __FILE__, __LINE__
		);

		if (($this->_mintimer == false) || ($interval < $this->_mintimer)) {
			$this->_mintimer = $interval;
		}

		return $id;
	}

	protected function _updatestate() {
		if (is_resource($this->_socket)) {
			$rtype = get_resource_type($this->_socket);
			if ($this->_socket !== false
				&& (strtolower($rtype) == 'socket' || $rtype == 'stream')
			) {
				$this->_state = SMARTIRC_STATE_CONNECTED;
			}
		} else {
			$this->_state = SMARTIRC_STATE_DISCONNECTED;
			$this->_loggedin = false;
		}

		return $this->_state;
	}

	public function reconnect() {
		// remember in which channels we are joined
		$channels = array();
		foreach ($this->_channels as $value) {
			if (empty($value->key)) {
				$channels[] = array('name' => $value->name);
			} else {
				$channels[] = array('name' => $value->name, 'key' => $value->key);
			}
		}

		$this->disconnect(true);

		while ($this->_autoretry === true
			&& ($this->_autoretrymax == 0 || $this->_autoretrycount < $this->_autoretrymax)
			&& $this->_updatestate() != SMARTIRC_STATE_CONNECTED
		) {
			$this->_autoretrycount++;

			if ($this->_reconnectdelay > 0) {
				$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: delaying '
					.'reconnect for '.$this->_reconnectdelay.' ms',
					__FILE__, __LINE__
				);

				for ($i = 0; $i < $this->_reconnectdelay; $i++) {
					$this->_callTimeHandlers();
					usleep(1000);
				}
			}

			$this->_callTimeHandlers();
			$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: reconnecting...',
				__FILE__, __LINE__
			);

			if ($this->connect($this->_address, $this->_port, true) !== false) {
				break;
			}
		}

		if ($this->_updatestate() != SMARTIRC_STATE_CONNECTED) {
			return false;
		}

		$this->login(
			$this->_nick,
			$this->_realname,
			$this->_usermode,
			$this->_username,
			$this->_password
		);

		// rejoin the channels
		foreach ($channels as $value) {
			if (isset($value['key'])) {
				$this->join($value['name'], $value['key']);
			} else {
				$this->join($value['name']);
			}
		}

		return $this;
	}

	public function disconnect($quick = false) {
		if ($this->_updatestate() != SMARTIRC_STATE_CONNECTED) {
			return false;
		}

		if (!$quick) {
			$this->send('QUIT', SMARTIRC_CRITICAL);
			usleep($this->_disconnecttime*1000);
		}

		fclose($this->_socket);

		$this->_updatestate();
		$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: disconnected',
			__FILE__, __LINE__
		);

		if ($this->_channelsyncing) {
			// let's clean our channel array
			$this->_channels = array();
			$this->log(SMARTIRC_DEBUG_CHANNELSYNCING, 'DEBUG_CHANNELSYNCING: '
				.'cleaned channel array', __FILE__, __LINE__
			);
		}

		if ($this->_usersyncing) {
			// let's clean our user array
			$this->_users = array();
			$this->log(SMARTIRC_DEBUG_USERSYNCING, 'DEBUG_USERSYNCING: cleaned '
				.'user array', __FILE__, __LINE__
			);
		}

		if ($this->_logdestination == SMARTIRC_FILE) {
			fclose($this->_logfilefp);
			$this->_logfilefp = null;
		} else if ($this->_logdestination == SMARTIRC_SYSLOG) {
			closelog();
		}

		return $this;
	}

	protected function _callTimeHandlers() {
		foreach ($this->_timehandler as &$handlerinfo) {
			$microtimestamp = microtime(true);
			if ($microtimestamp >= $handlerinfo['lastmicrotimestamp']
				+ ($handlerinfo['interval'] / 1000.0)
			) {
				$methodobject = &$handlerinfo['object'];
				$method = $handlerinfo['method'];
				$handlerinfo['lastmicrotimestamp'] = $microtimestamp;

				if (method_exists($methodobject, $method)) {
					$this->log(SMARTIRC_DEBUG_TIMEHANDLER, 'DEBUG_TIMEHANDLER: '
						.'calling method "'.get_class($methodobject).'->'
						.$method.'"', __FILE__, __LINE__
					);
					$methodobject->$method($this);
				}
			}
		}
	}

	public function login($nick, $realname, $usermode = 0, $username = null, $password = null ) {

		$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: logging in',
			__FILE__, __LINE__
		);

		$this->_nick = str_replace(' ', '', $nick);
		$this->_realname = $realname;

		$this->_username = str_replace(' ', '', exec('whoami'));
		if ($username !== null) {
			$this->_username = str_replace(' ', '', $username);
		}

		if ($password !== null) {
			$this->_password = $password;
			$this->send('PASS '.$this->_password, SMARTIRC_CRITICAL);
		}

		if (!is_numeric($usermode)) {
			$ipos = strpos($usermode, 'i');
			$wpos = strpos($usermode, 'w');
			$val = 0;
			if ($ipos) $val += 8;
			if ($wpos) $val += 4;

			if ($val == 0) {
				$this->log(SMARTIRC_DEBUG_NOTICE, 'DEBUG_NOTICE: login() usermode ('
					.$usermode.') is not valid, using 0 instead',
					__FILE__, __LINE__
				);
			}
			$usermode = $val;
		}

		$this->send('NICK '.$this->_nick, SMARTIRC_CRITICAL);
		$this->send('USER '.$this->_username.' '.$usermode.' '.SMARTIRC_UNUSED
			.' :'.$this->_realname, SMARTIRC_CRITICAL
		);

		if (count($this->_performs)) {
			// if we have extra commands to send, do it now
			foreach ($this->_performs as $command) {
				$this->send($command, SMARTIRC_HIGH);
			}
			// if we sent "ns auth" commands, we may need to resend our nick
			$this->send('NICK '.$this->_nick, SMARTIRC_HIGH);
		}

		return $this;
	}

	public function send($data, $priority = SMARTIRC_MEDIUM) {
		switch ($priority) {
			case SMARTIRC_CRITICAL:
				$this->_rawsend($data);
				break;

			case SMARTIRC_HIGH:
			case SMARTIRC_MEDIUM:
			case SMARTIRC_LOW:
				$this->_messagebuffer[$priority][] = $data;
				break;

			default:
				$this->log(SMARTIRC_DEBUG_NOTICE, "WARNING: message ($data) "
					."with an invalid priority passed ($priority), message is "
					.'ignored!', __FILE__, __LINE__
				);
				return false;
		}

		return $this;
	}

	protected function _rawsend($data) {
		if ($this->_updatestate() != SMARTIRC_STATE_CONNECTED) {
			return false;
		}

		$this->log(SMARTIRC_DEBUG_IRCMESSAGES, 'DEBUG_IRCMESSAGES: sent: "'
			.$data.'"', __FILE__, __LINE__
		);

		$result = fwrite($this->_socket, $data.SMARTIRC_CRLF);

		if ($result === false) {
			$this->_connectionerror = true;
		} else {
			$this->_lasttx = time();
		}

		return ($result !== false);
	}

	public function listen() {
		set_time_limit(0);
		while ($this->listenOnce() && !$this->_interrupt) {}
		return $this;
	}

	public function listenOnce() {
		// if we're not connected, we can't listen, so return
		if ($this->_updatestate() != SMARTIRC_STATE_CONNECTED) {
			return false;
		}

		// before we listen...
		if ($this->_loggedin) {
			// see if any timehandler needs to be called
			$this->_callTimeHandlers();

			// also let's send any queued messages
			if ($this->_lastsentmsgtime == 0) {
				$this->_lastsentmsgtime = microtime(true);
			}

			$highcount = count($this->_messagebuffer[SMARTIRC_HIGH]);
			$mediumcount = count($this->_messagebuffer[SMARTIRC_MEDIUM]);
			$lowcount = count($this->_messagebuffer[SMARTIRC_LOW]);
			$this->_messagebuffersize = $highcount+$mediumcount+$lowcount;

			// don't send them too fast
			if ($this->_messagebuffersize
				&& microtime(true)
				>= ($this->_lastsentmsgtime+($this->_senddelay/1000))
			) {
				$result = null;
				if ($highcount) {
					$this->_rawsend(array_shift($this->_messagebuffer[SMARTIRC_HIGH]));
					$this->_lastsentmsgtime = microtime(true);
				} else if ($mediumcount) {
					$this->_rawsend(array_shift($this->_messagebuffer[SMARTIRC_MEDIUM]));
					$this->_lastsentmsgtime = microtime(true);
				} else if ($lowcount) {
					$this->_rawsend(array_shift($this->_messagebuffer[SMARTIRC_LOW]));
					$this->_lastsentmsgtime = microtime(true);
				}
			}
		}

		// calculate selecttimeout
		$compare = array($this->_maxtimer, $this->_receivedelay * 1000);

		if ($this->_mintimer) $compare[] = $this->_mintimer;

		$selecttimeout = ($this->_messagebuffersize != 0)
			? $this->_senddelay
			: min($compare)
		;

		// check the socket to see if data is waiting for us
		// this will trigger a warning when a signal is received
		$r = array($this->_socket);
		$w = null;
		$e = null;
		$result = stream_select($r, $w, $e, 0, $selecttimeout);

		$rawdata = null;

		if ($result) {
			// the socket got data to read
			$rawdata = '';
			do {
				if ($get = fgets($this->_socket)):
					$rawdata .= $get;
				endif;
				$rawlen = strlen($rawdata);
			} while ($rawlen && $rawdata{$rawlen - 1} != "\n");

		} else if ($result === false) {
			// panic! panic! something went wrong! maybe received a signal.
			exit;
		}
		// no data on the socket

		$timestamp = time();
		if (empty($rawdata)) {
			if ($this->_lastrx < ($timestamp - $this->_rxtimeout)) {
				$this->_connectionerror = true;
			} else if ($this->_lasttx < ($timestamp - $this->_txtimeout)) {
				$this->_connectionerror = true;
			}
		} else {
			$this->_lastrx = $timestamp;

			// split up incoming lines, remove any empty ones and
			// trim whitespace off the rest
			$rawdataar = array_map('trim', array_filter(explode("\r\n", $rawdata)));

			// parse and handle them
			foreach ($rawdataar as $rawline) {

				// building our data packet
				$ircdata = new Kashima_Core_Data();
				$ircdata->rawmessage = $rawline;
				$ircdata->rawmessageex = explode(' ', $rawline); // kept for BC

				// parsing the message {
				$prefix = $trailing = '';
				$prefixEnd = -1;

				// parse out the prefix
				if ($rawline{0} == ':') {
					$prefixEnd = strpos($rawline, ' ');
					$prefix = substr($rawline, 1, $prefixEnd - 1);
				}

				// parse out the trailing
				if ($trailingStart = strpos($rawline, ' :')) { // this is not ==
					$trailing = substr($rawline, $trailingStart + 2);
				} else {
					$trailingStart = strlen($rawline);
				}

				// parse out command and params
				$params = explode(' ', substr($rawline,
					$prefixEnd + 1,
					$trailingStart - $prefixEnd - 1
				));
				$command = array_shift($params);
				// }

				$ircdata->from = $prefix;
				$ircdata->params = $params;
				$ircdata->message = $trailing;
				$ircdata->messageex = explode(' ', $trailing);

				// parse ident thingy
				if (preg_match('/^(\S+)!(\S+)@(\S+)$/', $prefix, $matches)) {
					$ircdata->nick = $matches[1];
					$ircdata->ident = $matches[2];
					$ircdata->host = $matches[3];
				} else {
					$ircdata->nick = '';
					$ircdata->ident = '';
					$ircdata->host = $prefix;
				}

				// figure out what SMARTIRC_TYPE this message is
				switch ($command) {
					case SMARTIRC_RPL_WELCOME:
					case SMARTIRC_RPL_YOURHOST:
					case SMARTIRC_RPL_CREATED:
					case SMARTIRC_RPL_MYINFO:
					case SMARTIRC_RPL_BOUNCE:
						$ircdata->type = SMARTIRC_TYPE_LOGIN;
						break;
					case SMARTIRC_RPL_MOTDSTART:
					case SMARTIRC_RPL_MOTD:
					case SMARTIRC_RPL_ENDOFMOTD:
						$ircdata->type = SMARTIRC_TYPE_MOTD;
						break;
					case SMARTIRC_RPL_NAMREPLY:
						$ircdata->type = SMARTIRC_TYPE_NAME;
						if ($params[0] == $this->_nick):
							$ircdata->channel = $params[2];
						else:
							$ircdata->channel = $params[1];
						endif;
						break;
					case SMARTIRC_RPL_ENDOFNAMES:
						$ircdata->type = SMARTIRC_TYPE_NAME;
						if ($params[0] == $this->_nick):
							$ircdata->channel = $params[1];
						else:
							$ircdata->channel = $params[0];
						endif;
						break;
					case 'PRIVMSG':
						if (strspn($ircdata->params[0], '&#+!')) {
							$ircdata->type = SMARTIRC_TYPE_CHANNEL;
							$ircdata->channel = $params[0];
							break;
						}
						if ($ircdata->message{0} == chr(1)) {
							if (preg_match("/^\1ACTION .*\1\$/", $ircdata->message)) {
								$ircdata->type = SMARTIRC_TYPE_ACTION;
								$ircdata->channel = $params[0];
								break;
							}
							if (preg_match("/^\1.*\1\$/", $ircdata->message)) {
								$ircdata->type = (SMARTIRC_TYPE_CTCP_REQUEST | SMARTIRC_TYPE_CTCP);
								break;
							}
						}
						$ircdata->type = SMARTIRC_TYPE_QUERY;
						break;
					case 'NOTICE':
						if (preg_match("/^\1.*\1\$/", $ircdata->message)) {
							$ircdata->type = (SMARTIRC_TYPE_CTCP_REPLY | SMARTIRC_TYPE_CTCP);
							break;
						}
						$ircdata->type = SMARTIRC_TYPE_NOTICE;
						break;
					case 'JOIN':
						$ircdata->type = SMARTIRC_TYPE_JOIN;
						if (isset($params[0])) {
							$ircdata->channel = $params[0] ?: $ircdata->message;
						}else{
							$ircdata->channel = $ircdata->message;
						}
						break;
					case 'PART':
						$ircdata->type = SMARTIRC_TYPE_PART;
						$ircdata->channel = $params[0];
						break;
					case 'MODE':
						$ircdata->type = SMARTIRC_TYPE_MODECHANGE;
						$ircdata->channel = $params[0];
						break;
					case 'QUIT':
						$ircdata->type = SMARTIRC_TYPE_QUIT;
						break;
					default:
						$ircdata->type = SMARTIRC_TYPE_UNKNOWN;
						break;
				}
				// lets see if we have a messagehandler for it
				if (is_numeric($command)) {
					if (!array_key_exists($command, $this->nreplycodes)) {
						$methodname = 'event_' . $command;
					} else {
						$methodname = 'event_'.strtolower($this->nreplycodes[$command]);
					}
					$_methodname = '_'.$methodname;
				} else {
					$methodname = 'event_'.strtolower($command);
					$_methodname = '_'.$methodname;
				}
				if (method_exists($this, $_methodname)) {
					$this->$_methodname($ircdata);
				}
				foreach ($this->_actionhandler as $i => &$handlerinfo) {

					$hmsg = $handlerinfo['message'];
					$regex = ($hmsg{0} == $hmsg{strlen($hmsg) - 1})
						? $hmsg
						: '/' . $hmsg . '/';

					if (($handlerinfo['type'] & $ircdata->type)
						&& preg_match($regex, $ircdata->message)
					) {

						$methodobject = &$handlerinfo['object'];
						$method = $handlerinfo['method'];

						if (method_exists($methodobject, $method)) {
							$methodobject->$method($this, $ircdata);
						} else {
						}
					}
				}
				unset($ircdata);
			}
		}
		if ($this->_connectionerror) {
			$this->reconnect();
		}
		return $this;
	}
}