<?php
abstract class Kashima_MessageHandler extends Kashima_IrcCommands
{
	protected function _event_ping($ircdata)
	{
		$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: Ping? Pong!',
			__FILE__, __LINE__
		);
		$this->send('PONG :' . $ircdata->message, SMARTIRC_CRITICAL);
	}
	protected function _event_error($ircdata) {
		if ($this->_autoretry) {
			$this->reconnect();
		} else {
			$this->disconnect(true);
		}
	}
	protected function _event_join($ircdata) {
		if ($this->_channelsyncing) {
			if ($this->_nick == $ircdata->nick) {
				$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
					'DEBUG_CHANNELSYNCING: joining channel: '.$ircdata->channel,
					__FILE__, __LINE__
				);
				$channel = new Kashima_Core_Channel_User();
				$channel->name = $ircdata->channel;
				$microint = microtime(true);
				$channel->synctime_start = $microint;
				$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
					'DEBUG_CHANNELSYNCING: synctime_start for '
					.$ircdata->channel.' set to: '.$microint, __FILE__, __LINE__
				);
				$this->_channels[strtolower($channel->name)] = $channel;

				// the class will get his own who data from the whole who channel list
				$this->mode($channel->name);
				$this->who($channel->name);
				$this->ban($channel->name);
			} else {
				// the class didn't join but someone else, lets get his who data
				$this->who($ircdata->nick);
			}

			$this->log(SMARTIRC_DEBUG_CHANNELSYNCING, 'DEBUG_CHANNELSYNCING: '
				.$ircdata->nick.' joins channel: '.$ircdata->channel,
				__FILE__, __LINE__
			);
			$channel = &$this->getChannel($ircdata->channel);
			$user = new Kashima_Core_Channel_User();
			$user->nick = $ircdata->nick;
			$user->ident = $ircdata->ident;
			$user->host = $ircdata->host;

			$this->_adduser($channel, $user);
		}
	}
	protected function _event_part($ircdata) {
		if ($this->_channelsyncing) {
			$this->_removeuser($ircdata);
		}
	}
	protected function _event_quit($ircdata) {
		if ($this->_channelsyncing) {
			$this->_removeuser($ircdata);
		}
	}
	protected function _event_mode($ircdata) {
		// check if its own usermode
		if ($ircdata->params[0] == $this->_nick) {
			$this->_usermode = $ircdata->message;
		} else if ($this->_channelsyncing) {
			// it's not, and we do channel syncing
			$channel = &$this->getChannel($ircdata->channel);
			$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
				'DEBUG_CHANNELSYNCING: updating channel mode for: '
				.$channel->name, __FILE__, __LINE__
			);
			$mode = $ircdata->params[1];
			$parameters = array_slice($ircdata->params, 2);

			$add = false;
			$remove = false;
			$modelength = strlen($mode);
			for ($i = 0; $i < $modelength; $i++) {
				switch($mode{$i}) {
					case '-':
						$remove = true;
						$add = false;
						break;

					case '+':
						$add = true;
						$remove = false;
						break;

					// user modes
					case 'q':
						$nick = array_shift($parameters);
						$lowerednick = strtolower($nick);
						if ($add) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: adding founder: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							$channel->founders[$nick] = true;
							$channel->users[$lowerednick]->founder = true;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removing founder: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							unset($channel->founders[$nick]);
							$channel->users[$lowerednick]->founder = false;
						}
						break;

					case 'a':
						$nick = array_shift($parameters);
						$lowerednick = strtolower($nick);
						if ($add) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: adding admin: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							$channel->admins[$nick] = true;
							$channel->users[$lowerednick]->admin = true;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removing admin: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							unset($channel->admins[$nick]);
							$channel->users[$lowerednick]->admin = false;
						}
						break;

					case 'o':
						$nick = array_shift($parameters);
						$lowerednick = strtolower($nick);
						if ($add) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: adding op: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							$channel->ops[$nick] = true;
							$channel->users[$lowerednick]->op = true;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removing op: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							unset($channel->ops[$nick]);
							$channel->users[$lowerednick]->op = false;
						}
						break;

					case 'h':
						$nick = array_shift($parameters);
						$lowerednick = strtolower($nick);
						if ($add) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: adding half-op: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							$channel->hops[$nick] = true;
							$channel->users[$lowerednick]->hop = true;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removing half-op: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							unset($channel->hops[$nick]);
							$channel->users[$lowerednick]->hop = false;
						}
						break;

					case 'v':
						$nick = array_shift($parameters);
						$lowerednick = strtolower($nick);
						if ($add) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: adding voice: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							$channel->voices[$nick] = true;
							$channel->users[$lowerednick]->voice = true;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removing voice: '.$nick
								.' to channel: '.$channel->name,
								__FILE__, __LINE__
							);
							unset($channel->voices[$nick]);
							$channel->users[$lowerednick]->voice = false;
						}
						break;

					case 'k':
						$key = array_shift($parameters);
						if ($add) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: stored channel key for: '
								.$channel->name, __FILE__, __LINE__
							);
							$channel->key = $key;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removed channel key for: '
								.$channel->name, __FILE__, __LINE__
							);
							$channel->key = '';
						}
						break;

					case 'l':
						if ($add) {
							$limit = array_shift($parameters);
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: stored user limit for: '
								.$channel->name, __FILE__, __LINE__
							);
							$channel->user_limit = $limit;
						}
						if ($remove) {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: removed user limit for: '
								.$channel->name, __FILE__, __LINE__
							);
							$channel->user_limit = false;
						}
						break;

					default:
						// channel modes
						if ($mode{$i} == 'b') {
							$hostmask = array_shift($parameters);
							if ($add) {
								$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
									'DEBUG_CHANNELSYNCING: adding ban: '
									.$hostmask.' for: '.$channel->name,
									__FILE__, __LINE__
								);
								$channel->bans[$hostmask] = true;
							}
							if ($remove) {
								$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
									'DEBUG_CHANNELSYNCING: removing ban: '
									.$hostmask.' for: '.$channel->name,
									__FILE__, __LINE__
								);
								unset($channel->bans[$hostmask]);
							}
						} else {
							$this->log(SMARTIRC_DEBUG_CHANNELSYNCING,
								'DEBUG_CHANNELSYNCING: updating unknown channelmode ('
								.$mode{$i}.') in channel->mode for: '
								.$channel->name, __FILE__, __LINE__
							);
							if ($add) {
								$channel->mode .= $mode{$i};
							}
							if ($remove) {
								$channel->mode = str_replace($mode{$i}, '',
									$channel->mode
								);
							}
						}
				}
			}
		}
	}
	protected function _event_privmsg($ircdata) {
		if ($ircdata->type & SMARTIRC_TYPE_CTCP_REQUEST) {
			// substr must be 1,4 because of \001 in CTCP messages
			if (substr($ircdata->message, 1, 4) == 'PING') {
				$this->message(SMARTIRC_TYPE_CTCP_REPLY, $ircdata->nick,
					'PING'.substr($ircdata->message, 5, -1)
				);
			} elseif (substr($ircdata->message, 1, 7) == 'VERSION') {
				if (!empty($this->_ctcpversion)) {
					$versionstring = $this->_ctcpversion;
				} else {
					$versionstring = SMARTIRC_VERSIONSTRING;
				}

				$this->message(SMARTIRC_TYPE_CTCP_REPLY, $ircdata->nick,
					'VERSION '.$versionstring
				);
			} elseif (substr($ircdata->message, 1, 10) == 'CLIENTINFO') {
				$this->message(SMARTIRC_TYPE_CTCP_REPLY, $ircdata->nick,
					'CLIENTINFO PING VERSION CLIENTINFO'
				);
			}
		}
	}
	protected function _event_rpl_welcome($ircdata) {
		$this->_loggedin = true;

		// updating our nickname, that we got (maybe cutted...)
		$this->_nick = $ircdata->params[0];

		$this->log(SMARTIRC_DEBUG_CONNECTION, 'DEBUG_CONNECTION: logged in as '
			. $this->_nick, __FILE__, __LINE__
		);

	}
	protected function _event_rpl_motdstart($ircdata) {
		$this->_motd[] = $ircdata->message;
	}
	protected function _event_rpl_motd($ircdata) {
		$this->_motd[] = $ircdata->message;
	}
	protected function _event_rpl_endofmotd($ircdata) {
		$this->_motd[] = $ircdata->message;
	}
	protected function _event_rpl_namreply($ircdata) {
		if ($this->_channelsyncing) {
			$userarray = explode(' ', rtrim($ircdata->message));
			$userarraycount = count($userarray);
			for ($i = 0; $i < $userarraycount; $i++) {
				$user = new Kashima_Core_Channel_User();

				switch ($userarray[$i]{0}) {
					case '~':
						$user->founder = true;
						$user->nick = substr($userarray[$i], 1);
						break;

					case '&':
						$user->admin = true;
						$user->nick = substr($userarray[$i], 1);
						break;

					case '@':
						$user->op = true;
						$user->nick = substr($userarray[$i], 1);
						break;

					case '%':
						$user->hop = true;
						$user->nick = substr($userarray[$i], 1);
						break;

					case '+':
						$user->voice = true;
						$user->nick = substr($userarray[$i], 1);
						break;

					default:
						$user->nick = $userarray[$i];
				}

				$channel = &$this->getChannel($ircdata->channel);
				$this->_adduser($channel, $user);
			}
		}
	}
	protected function _event_err_nicknameinuse($ircdata) {
		$newnick = substr($this->_nick, 0, 5) . rand(0, 999);
		$this->changeNick($newnick, SMARTIRC_CRITICAL);
	}
}
