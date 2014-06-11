<?php
/** @package Builder::Controller */

/** import supporting libraries */
require_once("BaseController.php");
require_once("verysimple/IO/FolderHelper.php");
require_once("verysimple/DB/Reflection/DBServer.php");
require_once("libs/App/AppConfig.php");

/**
 * DefaultController is the entry point to the application
 *
 * @package Adserv::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class AnalyzerController extends BaseController
{

	/**
	 * Override here for any controller-specific functionality
	 */
	protected function Init()
	{
		parent::Init();
	}

	/**
	 * Analyze the database schema and display a listing of tables and options
	 */
	public function Analyze()
	{

		$cstring = $this->GetConnectionString();

		// initialize the database connection
		$handler = new DBEventHandler();
		$connection = new DBConnection($cstring, $handler);
		$server = new DBServer($connection);

		// load up the available packages (based on files: code/*.config)
		$folder = new FolderHelper( GlobalConfig::$APP_ROOT . '/code/' );
		$files = $folder->GetFiles('/config/');
		$packages = Array();

		foreach ($files as $fileHelper)
		{
			$packages[] = new AppConfig($fileHelper->Path);
		}
		
		uasort(
			$packages,
			function ($a, $b) {
				return $a->GetName() > $b->GetName() ? 1 : -1;
			}
		);

		// read and parse the database structure
		$dbSchema = new DBSchema($server);

		$appname = $this->getParamValue('appName', strtoupper($this->GetAppName($connection)));

		// header("Content-type: text/plain"); print_r($schema); die();

		// initialize parameters that will be passed on to the code templates
		$params = array();
		$params[] = new AppParameter('PathToVerySimpleScripts', '/scripts/verysimple/');
		$params[] = new AppParameter('PathToExtScripts', '/scripts/ext-2/');
		$params[] = new AppParameter('AppName', $this->getParamValue('appname', $dbSchema->Name));

		$this->Assign("dbSchema", $dbSchema);
		$this->Assign("packages",$packages);
		$this->Assign("params", $params);
		$this->Assign("appname", $appname);
		$this->Assign("appRoot", $this->getParamValue("appRoot", strtolower($appname)));
		$this->Assign("phreezePath", $this->getParamValue("phreezePath", "../phreeze/libs"));

		// $this->RenderEngine->savant->addPlugins(array('Savant3_Filter_studlycaps', 'filter'));

		$this->Assign('host', $cstring->Host);
		$this->Assign('port', $cstring->Port);
		$this->Assign('type', $cstring->Type);
		$this->Assign('schema', $cstring->DBName);
		$this->Assign('username', $cstring->Username);
		$this->Assign('password', $cstring->Password);

		$this->Render();
	}
        
        function LoadConfig() {
            try {
                $config = $this->LoadConfigFile();

                RequestUtil::Set('host', $config["dbConfig"]["host"]);
                RequestUtil::Set('port', $config["dbConfig"]["port"]);
                RequestUtil::Set('username', $config["dbConfig"]["user"]);
                RequestUtil::Set('password', $config["dbConfig"]["pass"]);
                RequestUtil::Set('schema', $config["dbConfig"]["schema"]);
                RequestUtil::Set('type', $config["dbConfig"]["type"]);
                RequestUtil::Set('appName', $config["appConfig"]["appname"]);
                RequestUtil::Set('appRoot', $config["appConfig"]["approot"]);
                RequestUtil::Set('phreezePath', $config["appConfig"]["includePath"]);
                
                $this->Assign('appName', $config["appConfig"]["appname"]);
                $this->Assign('appRoot', $config["appConfig"]["approot"]);
//                $this->Assign('appName', $config["appConfig"]["includePath"]);
//                $this->Assign('appName', $config["appConfig"]["includePhar"]);
//                $this->Assign('appName', $config["appConfig"]["enableLongPolling"]);
//                $this->Assign('appName', $config["appConfig"]["packageName"]);
                
                $this->Assign('tables', $config["tables"]);
                
                $this->Analyze();
            } catch (RuntimeException $e) {
                echo $e->getMessage();
            }
        }
        
        /**
         * Loads the configuration from the uploaded file and returns it as an associative array of Strings
         * 
         * @return type
         * @throws RuntimeException
         */
        private function LoadConfigFile() {
                // Undefined | Multiple Files | $_FILES Corruption Attack
                // If this request falls under any of them, treat it invalid.
                if (!isset($_FILES['configFile']['error']) || is_array($_FILES['configFile']['error'])) {
                        throw new RuntimeException('Invalid parameters.');
                }

                // Check $_FILES['configFile']['error'] value.
                switch ($_FILES['configFile']['error']) {
                        case UPLOAD_ERR_OK:
                                break;
                        case UPLOAD_ERR_NO_FILE:
                                throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                                throw new RuntimeException('Exceeded filesize limit.');
                        default:
                                throw new RuntimeException('Unknown errors.');
                }

                // You should also check filesize here.
                if ($_FILES['configFile']['size'] > 1000000) {
                        throw new RuntimeException('Exceeded filesize limit.');
                }

                // DO NOT TRUST $_FILES['configFile']['mime'] VALUE !!
                // Check MIME Type by yourself.
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES['configFile']['tmp_name']);

                $config = file_get_contents($_FILES['configFile']['tmp_name']);
                return json_decode($config, true);
        }
        
        private function getParamValue($paramName, $default) {
                return null !== RequestUtil::get($paramName, null) ? RequestUtil::get($paramName) : $default;
        }
}
?>