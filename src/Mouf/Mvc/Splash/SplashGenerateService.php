<?php
namespace Mouf\Mvc\Splash;

/**
 * This class is in charge of all tasks that generate files:
 *  - .htaccess generation
 *  - controllers generation
 *  ...
 *  
 * @author david
 * @Component
 */
class SplashGenerateService {
	/**
	 * Writes the .htaccess file
	 * 
	 * @param string $rootUri
	 * @param array<string> $exludeExtentions
	 * @param array<string> $exludeFolders
	 */
	public function writeHtAccess($rootUri, $exludeExtentions, $exludeFolders) {

		$modelsDirName = dirname(__FILE__);
		$splashDir = dirname($modelsDirName);
		$splashVersion = basename($splashDir);
		
		$strExtentions = implode('|', $exludeExtentions);
		$strFolders = '^' . implode('|^', $exludeFolders);
		
		$str = "Options FollowSymLinks
		RewriteEngine on
		RewriteBase $rootUri
		
		#RewriteCond %{REQUEST_FILENAME} !-f
		#RewriteCond %{REQUEST_FILENAME} !-d
		
		RewriteRule !((\.($strExtentions)$)|$strFolders) plugins/mvc/splash/".$splashVersion."/splash.php";
		
		file_put_contents(dirname(__FILE__)."/../../../../../.htaccess", $str);
	}
	
	/**
	 * 
	 * @param string $sourceDirectory
	 * @param string $controllerNamespace
	 * @param string $viewDirectory
	 */
	public function generateRootController($sourceDirectory, $controllerNamespace, $viewDirectory) {
		
		$rootControllerStr = '<?php
namespace '.$controllerNamespace.';
				
use Mouf\\Html\\HtmlElement\\HtmlBlock;
use Mouf\\Html\\Template\\TemplateInterface;
use Mouf\\Mvc\\Splash\\Controllers\\Controller;
				
/**
 * This is the controller in charge of managing the first page of the application.
 * 
 * @Component
 */
class RootController extends Controller {
	
	/**
	 * The template used by the controller.
	 *
	 * @var TemplateInterface
	 */
	public $template;
		
	/**
	 * This object represents the block of main content of the web page.
	 *
	 * @var HtmlBlock
	 */
	public $content;
	
	/**
	 * Page displayed when a user arrives on your web application.
	 * 
	 * @URL /
	 */
	public function index() {
		$this->content->addFile(ROOT_PATH."'.$sourceDirectory.$viewDirectory.'root/index.php", $this);
		$this->template->toHtml();
	}
}';
		$controllerPhpDirectory = ROOT_PATH.$sourceDirectory.str_replace('\\', '/',$controllerNamespace);
		mkdir($controllerPhpDirectory, 0777, true);
		file_put_contents($controllerPhpDirectory."RootController.php", $rootControllerStr);
		chmod($controllerPhpDirectory."RootController.php", 0666);

		$indexViewStr = '<?php /* @var $this '.$controllerNamespace.'RootController */ ?>
<h1>Welcome to Splash</h1>

<p>This file is your welcome page. It is generated by the RootController class and the '.$viewDirectory.'root/index.php file. Please feel free to customize it.</p>';
		
		mkdir(ROOT_PATH.$sourceDirectory.$viewDirectory."root", 0777, true);
		file_put_contents(ROOT_PATH.$sourceDirectory.$viewDirectory."root/index.php", $indexViewStr);
		chmod(ROOT_PATH.$sourceDirectory.$viewDirectory."root/index.php", 0666);		
	}
	
}