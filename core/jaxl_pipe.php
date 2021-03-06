<?php 
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2012, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Abhinav Singh nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

require_once JAXL_CWD.'/core/jaxl_loop.php';

/**
 * bidirectional communication pipes for processes
 *
 * This is how this will be (when complete): 
 * $pipe = new JAXLPipe() will return an array of size 2
 * $pipe[0] represents the read end of the pipe
 * $pipe[1] represents the write end of the pipe
 * 
 * Proposed functionality might even change (currently consider this as experimental)
 *
 * @author abhinavsingh
 *
 */
class JAXLPipe {
	
	protected $name = null;
	protected $perm = 0600;
	
	protected $fd = null;
	
	public function __construct($name, $perm=0600) {
		$this->name = $name;
		$this->perm = $perm;
		
		$pipe_path = $this->get_pipe_file_path();
		if(!file_exists($pipe_path)) {
			posix_mkfifo($pipe_path, $this->perm);
			$this->fd = fopen($pipe_path, 'r+');
			if(!$this->fd) _error("unable to open pipe");
			stream_set_blocking($this->fd, false);
			
			// watch for incoming data on pipe
			JAXLLoop::watch($this->fd, array(
				'read' => array(&$this, 'on_read_ready')
			));
		}
		else {
			_error("pipe with name $name already exists");
		}
	}
	
	public function get_pipe_file_path() {
		return JAXL_CWD.'/.jaxl/pipes/jaxl_'.$this->name.'.pipe';
	}
	
	public function __destruct() {
		@fclose($this->fd);
		@unlink($this->get_pipe_file_path());
		_debug("unlinking pipe file");
	}
	
	public function on_read_ready($fd) {
		$data = fread($fd, 1024);
		_debug($data);
	}
	
}

?>
