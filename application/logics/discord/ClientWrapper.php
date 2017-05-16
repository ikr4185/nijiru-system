<?php
namespace Logics\Discord;

use WebSocket\Client;
use WebSocket\ConnectionException;

use Cli\Commons\Console;

/**
 * Class ClientWrapper
 * Textalk/websocket-php Wrapper for Discord API
 */
class ClientWrapper extends Client
{
    public function receive()
    {
        if (!$this->is_connected) {
            $this->connect();
        } /// @todo This is a client function, fixme!
        
        $this->huge_payload = '';
        
        $response = null;
        while (is_null($response)) {
            $response = $this->receive_fragment();
        }
    
        // if op close
        if (!$this->is_connected) {
//            return json_encode(false);
            Console::log("is NOT connected", "CLOSE");
        }
        
        return $response;
    }
    
    protected function receive_fragment()
    {
        // Just read the main fragment information first.
//        Console::log("receive_fragment 1", "SYSTEM");
        $data = $this->read(2);
        
        // read timeout
        if (empty($data)) {
            return $data;
        }
        
        // Is this the final fragment?  // Bit 0 in byte 0
        /// @todo Handle huge payloads with multiple fragments.
        $final = (boolean)(ord($data[0]) & 1 << 7);
        
        // Should be unused, and must be false…  // Bits 1, 2, & 3
        $rsv1 = (boolean)(ord($data[0]) & 1 << 6);
        $rsv2 = (boolean)(ord($data[0]) & 1 << 5);
        $rsv3 = (boolean)(ord($data[0]) & 1 << 4);
        
        // Parse opcode
        $opcode_int = ord($data[0]) & 31; // Bits 4-7
        $opcode_ints = array_flip(self::$opcodes);
        if (!array_key_exists($opcode_int, $opcode_ints)) {
            throw new ConnectionException("Bad opcode in websocket frame: $opcode_int");
        }
        $opcode = $opcode_ints[$opcode_int];
        
        // record the opcode if we are not receiving a continutation fragment
        if ($opcode !== 'continuation') {
            $this->last_opcode = $opcode;
        }
        
        // Masking?
        $mask = (boolean)(ord($data[1]) >> 7);  // Bit 0 in byte 1
        
        $payload = '';
        
        // Payload length
        $payload_length = (integer)ord($data[1]) & 127; // Bits 1-7 in byte 1
        if ($payload_length > 125) {
            if ($payload_length === 126) {
//                Console::log("receive_fragment 2", "SYSTEM");
                $data = $this->read(2);
            } // 126: Payload is a 16-bit unsigned int
            else {
//                Console::log("receive_fragment 3", "SYSTEM");
                $data = $this->read(8);
            } // 127: Payload is a 64-bit unsigned int
            $payload_length = bindec(self::sprintB($data));
        }
        
        // Get masking key.
        if ($mask) {
//            Console::log("receive_fragment 4", "SYSTEM");
            $masking_key = $this->read(4);
        }
        
        // Get the actual payload, if any (might not be for e.g. close frames.
        if ($payload_length > 0) {
//            Console::log("receive_fragment 5", "SYSTEM");
            $data = $this->read($payload_length);
            
            if ($mask) {
                // Unmask payload.
                for ($i = 0; $i < $payload_length; $i++) {
                    $payload .= ($data[$i] ^ $masking_key[$i % 4]);
                }
            } else {
                $payload = $data;
            }
        }
        
        if ($opcode === 'close') {
            
            Console::log("opcode close", "CLOSE");
            
            // Get the close status.
            if ($payload_length >= 2) {
                $status_bin = $payload[0] . $payload[1];
                $status = bindec(sprintf("%08b%08b", ord($payload[0]), ord($payload[1])));
                $this->close_status = $status;
                $payload = substr($payload, 2);
                
                if (!$this->is_closing) {
                    $this->send($status_bin . 'Close acknowledged: ' . $status, 'close', true);
                } // Respond.
            }
            
            if ($this->is_closing) {
                $this->is_closing = false;
            } // A close response, all done.
            
            // And close the socket.
            fclose($this->socket);
            $this->is_connected = false;
        }
        
        // if this is not the last fragment, then we need to save the payload
        if (!$final) {
            $this->huge_payload .= $payload;
            return null;
        } // this is the last fragment, and we are processing a huge_payload
        else {
            if ($this->huge_payload) {
                // sp we need to retreive the whole payload
                $payload = $this->huge_payload .= $payload;
                $this->huge_payload = null;
            }
        }
        
        return $payload;
    }
    
    protected function read($length)
    {
        $data = '';
        $timeout = time() + $this->options["timeout"];
        
//        Console::log("", "SYSTEM", false);
        
        while (strlen($data) < $length) {
            
            // fix HeartBeat Timeout
            stream_set_blocking($this->socket, false);
            
//            echo ".";
            $buffer = fread($this->socket, $length - strlen($data));
            
            // fix HeartBeat Timeout
            stream_set_blocking($this->socket, true);
            
            if ($buffer === false) {
//                echo PHP_EOL;
                $metadata = stream_get_meta_data($this->socket);
                throw new ConnectionException('Broken frame, read ' . strlen($data) . ' of stated ' . $length . ' bytes.  Stream state: ' . json_encode($metadata));
            }
            if ($buffer === '') {
                
                if (time() > $timeout) {
//                    echo PHP_EOL;
//                    Console::log("read() Timeout", "SYSTEM");
                    break;
                }
                
                usleep(50000);
                continue;
//                $metadata = stream_get_meta_data($this->socket);
//                throw new ConnectionException('Empty read; connection dead?  Stream state: ' . json_encode($metadata));
            }
            $data .= $buffer;
        }
        
//        echo PHP_EOL;
//        Console::log("{$data}", "<STREAM>");
        return $data;
    }
    
    public function close($status = 1000, $message = 'ttfn')
    {
        $status_binstr = sprintf('%016b', $status);
        $status_str = '';
        foreach (str_split($status_binstr, 8) as $binstr) {
            $status_str .= chr(bindec($binstr));
        }
        $this->send($status_str . $message, 'close', true);
        
        $this->is_closing = true;
        $response = $this->receive(); // Receiving a close frame will close the socket now.
        
        return $response;
    }
    
}