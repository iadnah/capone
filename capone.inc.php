<?php
/*
capone 0.1 - source code comment stripper

usage:

	Open file "source.c", strip comments, print to stdout:
		$capone = new capone("source.c");
		$capone->parse();
		$capone->output();

	Read source from stdin, strip comments, print to stdout:
		$capone = new capone("-");
		$capone->parse();
		$capone->output();

	Read source from the variable $source, print to stdout:
		$capone = new capone($source, "var");
		$capone->parse();
		$capone->output();

	Return output as variable instead of printing to stdout:
		$capone = new capone("source.c");
		$capone->setopt("outmode", "ret_buffer");
		$capone->parse();
		$output = $capone->output();


Copyright (C) 2011 by iadnah@uplinklounge.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
class capone {
	private $in_buffer = null;
	private $out_buffer = '';
	private $source = null;

	//state variables
	private $in_comment = 0;
	private $w_for_nl = false;

	//config variables
	private $outmode = 'buffer';
	

	var $linelen = 4096;

	function __construct($target, $opts = null) {
		if ($opts == "var") {
			if ($this->source = explode("\n", $target)) {
				return true;
			}
			return false;
		}

		if ($target == "-") {
			if ($this->source = fopen("php://stdin", "r")) {
				return true;
			}
		} elseif (is_file($target)) {
			if ($this->source = fopen("$target", "r")) {
				return true;
			}
		} else {
			return false;
		}
	}

	public function setopt($optname, $value) {
		$this->$optname = $value;
	}

	private function getline() {
		if (is_array($this->source)) {
			if (count($this->source) > 0) {
				$line = array_shift($this->source). "\n";
			} else {
				$line = false;
			}
		} elseif (!feof($this->source)) {
			$line = fgets($this->source, $this->linelen);
		} else {
			$line = false;
		}
		return $line;
	}

	private function buffer($out) {
		switch ($this->outmode) {
			case 'buffer':
			case 'ret_buffer':

				$t = trim($out);
				if (strlen($t) > 0) {
					$this->out_buffer .= $out;
				}
			break;

			case 'direct':
				echo $out;
			break;
		}
	}

	public function parse() {
		while ($line = $this->getline()) {
			$outline = $this->parseline($line);

			$this->buffer($outline. "\n");
		}
		return strlen($this->out_buffer);
	}

	public function parseline($line) {
		$outline = '';
		for ($x = 0; $x < strlen($line); $x++) {
			if ($this->w_for_nl == true && $line[$x] == "\n") {
				//end C-style comment
				$this->w_for_nl = false;
				$x++;
				continue;
			}
	
			if (@isset($line[$x+1])) {
				$pair = $line[$x]. $line[$x+1];
			}
			else {
				continue;
			}
	
			switch ($pair) {
				case '//': 
					//start C-style comment
					$this->w_for_nl = true;
					$x++;
					continue;
				break;
				case '/*': 
					//start C++ style block comment
					$this->in_comment++;
					$x++;
					continue;
				break;
				case '*/':
					//end C++ style block comment
					$this->in_comment--;
					$x++; $x++;
					continue;
				break;
			}
	
			if ($this->in_comment == 0 && $this->w_for_nl == false) {
				$outline .= $line[$x];
			}
		}
		return $outline;
	}

	public function output() {
		if ($this->outmode = "ret_buffer") {
			return $this->out_buffer;
		}

		echo $this->out_buffer;
	}
}
?>
