<?php

/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * The MIT License (MIT)
 * Copyright (c) 2015 bricks-cms.org
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Bricks\Eventizr;

use Bricks\Config\Config;
use Bricks\Loader\Loader;
use Bricks\File\File;
use Composer;

class Eventizr {
	
	protected $config;
	protected $loader;
	protected $parser;
	
	public function __construct(Config $config,Loader $loader){
		$this->config = $config;
		$this->loader = $loader;
	}
	
	public function getConfig(){
		return $this->config;
	}
	
	public function getLoader(){
		return $this->loader;
	}
	
	public function setParser($parser){
		$this->parser = $parser;
	}
	
	public function getParser(){
		return $this->parser;
	}
	
	public function getCompileDir($namespace=null){
		$namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
		return $this->getConfig()->get('BricksEventizr.compileDir',$namespace);
	}
	
	public function eventize($class,$method,$namespace=null){		
		$namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
		
		/** @var File $origFile **/
		$origFile = $this->getOrigFile($class);
		$origContent = $origFile->fread($origFile->getSize());
		
		$parser = $this->getParser();
		/** @var \PhpParser\Node $parser **/
		$result = $parser->parse($origContent);
		$parser->eventize($result,$method);
		$content = $parser->compile($result);		
		
		//$this->writeNewFile($content,$namespace);
				
	}
	
	public function getOrigFile($class){		
		$autoloader = new Composer\Autoload\ClassLoader();
		return $this->getLoader()->get('Bricks\File\File',$autoloader->findFile($class));		
	}
	
	public function writeNewFile($content,$namespace=null){
		$namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
		
		$dir = $this->getCompileDir($namespace);
		$newFilePath = $dir.DIRECTORY_SEPARATOR.$newFile;
		$newFile = File::touch($newFilePath);
		
		if($newFile->getSize() != 0){
			$newFile->fseek(0);
			$newFile->ftruncate(0);
			$newFile->fseek(0);
		}
		$newFile->fwrite($content);
	}
	
}