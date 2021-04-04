<?php

include("includes/app.php");

$obj = new IWP_EXPORT();

$obj->iwpRunDBDump();

class IWP_EXPORT{

	private $dbFolder;
	private $sqlFile;
	private $dbFile;
	private $commandPath;
	private $command;
	private $iwpTables;
	private $mysqlPath;
	private $brace;

	public function __construct(){
		$this->dbFolder = APP_ROOT.'/uploads/';
		$this->sqlFile  = 'iwp_db.gz';
		$this->dbFile   = $this->dbFolder.$this->sqlFile;
		$this->mysqlPath= "/usr/bin/mysqldump,/bin/mysqldump,/usr/local/bin/mysqldump,/usr/sfw/bin/mysqldump,/usr/xdg4/bin/mysqldump,/opt/bin/mysqldump";
		$this->brace = (substr(PHP_OS, 0, 3) == 'WIN') ? '"' : '';
	}

	public function checkMysqlPaths()
    {
        $paths = array(
            'mysql' => '',
            'mysqldump' => ''
        );
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $mysqlInstall = DB::getField("SHOW VARIABLES LIKE 'basedir'");
            if ($mysqlInstall) {
                $installPath       = str_replace('\\', '/', $mysqlInstall);
                $paths['mysql']     = $installPath . 'bin/mysql.exe';
                $paths['mysqldump'] = $installPath . 'bin/mysqldump.exe';
            } else {
                $paths['mysql']     = 'mysql.exe';
                $paths['mysqldump'] = 'mysqldump.exe';
            }
        } else {
        	foreach ($variable as $key => $value) {
        		# code...
        	}
            $paths['mysql'] = $this->iwp_mmb_exec('which mysql', true);
            if (empty($paths['mysql']))
                $paths['mysql'] = 'mysql'; // try anyway
            
            $paths['mysqldump'] = $this->iwp_mmb_exec('which mysqldump', true);
            if (empty($paths['mysqldump'])){
                $paths['mysqldump'] = 'mysqldump'; // try anyway         
            }
            
        }
        
        
        return $paths;
    }

    public function iwp_mmb_exec($command, $string = false, $rawreturn = false)
    {
        if ($command == '')
            return false;
        
        if ($this->iwp_mmb_function_exists('exec')) {
            $log = @exec($command, $output, $return);
            
            if ($string)
                return $log;
            if ($rawreturn)
                return $return;
            
            return $return ? false : true;
        } elseif ($this->iwp_mmb_function_exists('system')) {
            $log = @system($command, $return);
            
            if ($string)
                return $log;
            
            if ($rawreturn)
                return $return;
            
            return $return ? false : true;
        } elseif ($this->iwp_mmb_function_exists('passthru') && !$string) {
            $log = passthru($command, $return);
            
            if ($rawreturn)
                return $return;
            
            return $return ? false : true;
        }
        
        if ($rawreturn)
        	return -1;
        	
        return false;
    }

    public function iwp_mmb_function_exists($function_callback){
		
		if(!function_exists($function_callback))
			return false;
			
		$disabled = explode(', ', @ini_get('disable_functions'));
		if (in_array($function_callback, $disabled))
			return false;
			
		if (extension_loaded('suhosin')) {
			$suhosin = @ini_get("suhosin.executor.func.blacklist");
			if (empty($suhosin) == false) {
				$suhosin = explode(',', $suhosin);
				$blacklist = array_map('trim', $suhosin);
				$blacklist = array_map('strtolower', $blacklist);
				if(in_array($function_callback, $blacklist))
					return false;
			}
		}
		return true;
	}

	private function setCommadPath(){
		$this->commandPath = $this->checkMysqlPaths();
	}

	private function setRequiredTable(){
		$tablePrefix = Reg::get('config.SQL_TABLE_NAME_PREFIX');
		$command0 = DB::getFields("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME NOT LIKE '%history_raw%' AND TABLE_NAME  LIKE '".Reg::get('config.SQL_TABLE_NAME_PREFIX')."%' AND TABLE_SCHEMA = '".Reg::get('config.SQL_DATABASE')."'");
        $iwpTables = join("\" \"",$command0);
        $this->iwpTables = $iwpTables;
	}

	private function setCommad($command){
		$this->command = $this->brace . $command . $this->brace . ' --force --host="' . Reg::get('config.SQL_HOST') . '" --user="' . Reg::get('config.SQL_USERNAME') . '" --password="' . Reg::get('config.SQL_PASSWORD') . '" --add-drop-table --skip-lock-tables --extended-insert=FALSE "' . Reg::get('config.SQL_DATABASE') . '" "'.$this->iwpTables.'" | gzip > ' . $this->brace . $this->dbFile . $this->brace;
	}

	public function runTest(){
		$bin = explode(',' , $this->mysqlPath);
		foreach ($bin as $key => $value) {
			$command = $this->brace . $value . $this->brace . ' --force --host="' . Reg::get('config.SQL_HOST') . '" --user="' . Reg::get('config.SQL_USERNAME') . '" --password="' . Reg::get('config.SQL_PASSWORD') . '" --add-drop-table --skip-lock-tables --extended-insert=FALSE "' . Reg::get('config.SQL_DATABASE') . '" ""'.Reg::get('config.SQL_TABLE_NAME_PREFIX').'options"" > ' . $this->brace . $this->dbFile . $this->brace;
			$result = $this->iwp_mmb_exec($command);
			if (!$result) { 
			   	continue;
			}
			
			if ($this->iwp_mmb_get_file_size($this->dbFile) == 0 || !is_file($this->dbFile) || !$result) {
			    continue;
			}
			$this->setCommad($value);
		}
	}

	public function iwpRunDBDump(){
		$this->setRequiredTable();
		$this->runTest();
		if (!empty($this->command)) {
			$result = $this->iwp_mmb_exec($this->command);
			if ($this->iwp_mmb_get_file_size($this->dbFile) == 0 || !is_file($this->dbFile) || !$result) {
			    die("Database export fails please contact help@infinitewp.com");
			}else{
				die("Database export success <a href='uploads/iwp_db.gz'>Download</a>");
			}
		}else{
			 die("MySQL command execution fails");
		}

	}

	public function iwp_mmb_get_file_size($file)
	{
		clearstatcache();
		$normal_file_size = filesize($file);
		if(($normal_file_size !== false)&&($normal_file_size >= 0))
		{
			return $normal_file_size;
		}
		else
		{
			$file = realPath($file);
			if(!$file)
			{
				echo 'iwp_mmb_get_file_size_error : realPath error';
			}
			$ch = curl_init("file://" . $file);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_FILE);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			$data = curl_exec($ch);
			$curl_error = curl_error($ch);
			curl_close($ch);
			if ($data !== false && preg_match('/Content-Length: (\d+)/', $data, $matches)) {
				return (string) $matches[1];
			}
			else
			{
				echo 'iwp_mmb_get_file_size_error : '.$curl_error;
				return $normal_file_size;
			}
		}
	}
}