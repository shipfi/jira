<?php
namespace mysoft\mqs\log;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use mysoft\mqs\LogAbs;

/**
 * 
 * @author yangzhen
 * 
 * 'components'=>[
 * 
 *    ...
 *    'qlog'=>[
 *        'class'=>'mysoft\mqs\log\File',
 *        'logFile'=>'@runtime/logs/'.date('Ymd'), //不写则默认为runtime/logs/mqs.log
 *    ]
 * 
 * 
 * ]
 * 
 *
 */
class File extends LogAbs
{

  /**
     * @var string log file path or path alias. If not set, it will use the "@runtime/logs/app.log" file.
     * The directory containing the log files will be automatically created if not existing.
     */
    public $logFile;
    /**
     * @var integer maximum log file size, in kilo-bytes. Defaults to 10240, meaning 10MB.
     */
    public $maxFileSize = 10240; // in KB
    /**
     * @var integer number of log files used for rotation. Defaults to 5.
     */
    public $maxLogFiles = 5;
    /**
     * @var integer the permission to be set for newly created log files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;
    /**
     * @var boolean Whether to rotate log files by copy and truncate in contrast to rotation by
     * renaming files. Defaults to `true` to be more compatible with log tailers and is windows
     * systems which do not play well with rename on open files. Rotation by renaming however is
     * a bit faster.
     *
     * The problem with windows systems where the [rename()](http://www.php.net/manual/en/function.rename.php)
     * function does not work with files that are opened by some process is described in a
     * [comment by Martin Pelletier](http://www.php.net/manual/en/function.rename.php#102274) in
     * the PHP documentation. By setting rotateByCopy to `true` you can work
     * around this problem.
     */
    public $rotateByCopy = true;
		
	
	public function init()
	{
		parent::init();
		
		if($this->logFile == null)
		{
		  $this->logFile = Yii::$app->getRuntimePath() . '/logs/mqs.log';	
			
		}else{
			
		   $this->logFile = Yii::getAlias($this->logFile);
			
		}
		
		$logPath = dirname($this->logFile);
		if (!is_dir($logPath)) {
			FileHelper::createDirectory($logPath, $this->dirMode, true);
		}
		
		
	}
	
	
	/**
	 * 实现文件类型写日志的行为
	 * @see \mysoft\mqs\LogAbs::write()
	 */
	protected function write($messages)
	{
	    $text = $this->_format($messages)."\n";
	     
	    if (($fp = @fopen($this->logFile, 'a')) === false) {
	    	throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
	    }
	    @flock($fp, LOCK_EX);
	    // clear stat cache to ensure getting the real current file size and not a cached one
	    // this may result in rotating twice when cached file size is used on subsequent calls
	    clearstatcache();
	    if (@filesize($this->logFile) > $this->maxFileSize * 1024) {
	    	$this->rotateFiles();
	    	@flock($fp, LOCK_UN);
	    	@fclose($fp);
	    	@file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
	    } else {
	    	@fwrite($fp, $text);
	    	@flock($fp, LOCK_UN);
	    	@fclose($fp);
	    }
	    if ($this->fileMode !== null) {
	    	@chmod($this->logFile, $this->fileMode);
	    }
	}
	
    /**
     * 格式化message
     * @param array $messages
     */
	private function _format($messages)
	{
		$text = '';
		
		foreach($messages as $message)
		{
			list($msg,$app,$datetime) = $message;
			if(is_array($msg)) $msg = print_r($msg,true); //处理array的情况
			$text .= sprintf('[%s][%s]:%s',$datetime,$app,$msg) ."\n";
			
		}
		
		//header信息
		$h_text = 'begin';
		if(count($messages) >= $this->flushInteval){
			$h_text .= '<nums more than flushInteval : ' .$this->flushInteval .'>';
		}else{
			$h_text .= '<nums count: ' .count($messages) .'>';
		}
		
		$header = "[system]".str_pad($h_text,100,'+=',STR_PAD_BOTH)."\n";
		$footer = "[/system]".str_pad("end",100,'+=',STR_PAD_BOTH)."\n";;
		$text = $header .$text.$footer;
		
		return $text;
	}
	
	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file = $this->logFile;
		for ($i = $this->maxLogFiles; $i >= 0; --$i) {
			// $i == 0 is the original log file
			$rotateFile = $file . ($i === 0 ? '' : '.' . $i);
			if (is_file($rotateFile)) {
				// suppress errors because it's possible multiple processes enter into this section
				if ($i === $this->maxLogFiles) {
					@unlink($rotateFile);
				} else {
					if ($this->rotateByCopy) {
						@copy($rotateFile, $file . '.' . ($i + 1));
						if ($fp = @fopen($rotateFile, 'a')) {
							@ftruncate($fp, 0);
							@fclose($fp);
						}
					} else {
						@rename($rotateFile, $file . '.' . ($i + 1));
					}
				}
			}
		}
	}
	
}