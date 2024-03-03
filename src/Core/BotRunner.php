<?php declare(strict_types=1);

namespace Nadybot\Core;

use function Amp\File\createDefaultDriver;
use function Amp\File\filesystem;
use function Safe\fclose;
use function Safe\fwrite;
use function Safe\ini_set;
use function Safe\json_encode;
use function Safe\realpath;
use function Safe\stream_get_contents;

use Amp\File\Driver\{BlockingFilesystemDriver, EioFilesystemDriver, ParallelFilesystemDriver};
use Amp\File\{FilesystemDriver};
use Amp\Http\Client\Connection\{DefaultConnectionFactory, UnlimitedConnectionPool};
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Interceptor\SetRequestHeaderIfUnset;
use Amp\Http\Tunnel\Http1TunnelConnector;
use Revolt\EventLoop;
use ErrorException;
use Exception;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\Config\BotConfig;
use Nadybot\Core\Modules\SETUP\Setup;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;

use ReflectionObject;
use Throwable;

class BotRunner {
	/** Nadybot's current version */
	public const VERSION = "7.0.0.alpha";

	public const AMP_FS_HANDLER = 'amp_fs_handler';

	/**
	 * The parsed command line arguments
	 *
	 * @var array<string,mixed>
	 */
	public static array $arguments = [];

	public ClassLoader $classLoader;

	protected static ?string $latestTag = null;

	protected static ?string $calculatedVersion = null;

	protected LoggerInterface $logger;

	/**
	 * The command line arguments
	 *
	 * @var string[]
	 */
	private array $argv = [];

	private ?BotConfig $configFile;

	/**
	 * Create a new instance
	 *
	 * @param string[] $argv
	 */
	public function __construct(array $argv) {
		$this->argv = $argv;
	}

	/**
	 * Return the (cached) version number of the bot.
	 * Depending on where you got the source from,
	 * it's either the latest tag, the branch or a fixed version
	 */
	public static function getVersion(bool $withBranch=true): string {
		if (!isset(static::$calculatedVersion)) {
			static::$calculatedVersion = static::calculateVersion();
		}
		if (!$withBranch) {
			return preg_replace("/@.+/", "", static::$calculatedVersion);
		}
		return static::$calculatedVersion;
	}

	/** Get the base directory of the bot */
	public static function getBasedir(): string {
		return realpath(dirname(__DIR__, 2));
	}

	/**
	 * Calculate the version number of the bot.
	 * Depending on where you got the source from,
	 * it's either the latest tag, the branch or a fixed version
	 */
	public static function calculateVersion(): string {
		$baseDir = static::getBasedir();
		if (!@file_exists("{$baseDir}/.git")) {
			return static::VERSION;
		}
		set_error_handler(function (int $num, string $str, string $file, int $line): void {
			throw new ErrorException($str, 0, $num, $file, $line);
		});
		try {
			$ref = explode(": ", trim(filesystem()->read("{$baseDir}/.git/HEAD")), 2)[1];
			$branch = explode("/", $ref, 3)[2];
			$latestTag = static::getLatestTag();
			if (!isset($latestTag)) {
				return $branch;
			}

			if ($latestTag === '') {
				$latestTag = static::VERSION;
			} elseif (strncmp(static::VERSION, $latestTag, min(strlen(static::VERSION), strlen($latestTag))) > 0) {
				$latestTag = static::VERSION;
			}
			if ($branch !== 'stable') {
				return "{$latestTag}@{$branch}";
			}
			$gitDescribe = static::getGitDescribe();
			if ($gitDescribe === null || $gitDescribe === $latestTag) {
				return "{$latestTag}";
			}
			return "{$latestTag}@stable";
		} catch (\Throwable $e) {
			return static::VERSION;
		} finally {
			restore_error_handler();
		}
	}

	/** @todo Rewrite with AMPHP3 */
	public static function getGitDescribe(): ?string {
		$baseDir = static::getBasedir();
		$descriptors = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];

		$pid = proc_open("git describe --tags", $descriptors, $pipes, $baseDir);
		if ($pid === false) {
			return null;
		}
		fclose($pipes[0]);
		$gitDescribe = trim(stream_get_contents($pipes[1]) ?: "");
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($pid);
		return $gitDescribe;
	}

	/**
	 * Calculate the latest tag that the checkout was tagged with
	 * and return how many commits were done since then
	 * Like [number of commits, tag]
	 */
	public static function getLatestTag(): ?string {
		if (isset(static::$latestTag)) {
			return static::$latestTag;
		}
		$baseDir = static::getBasedir();
		$descriptors = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];

		$pid = proc_open("git tag -l", $descriptors, $pipes, $baseDir);
		if ($pid === false) {
			return static::$latestTag = null;
		}
		fclose($pipes[0]);
		$tags = explode("\n", trim(stream_get_contents($pipes[1]) ?: ""));
		$tags = array_diff($tags, ["nightly"]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($pid);

		$tags = array_map(
			function (string $tag): SemanticVersion {
				return new SemanticVersion($tag);
			},
			$tags
		);

		/** @var SemanticVersion[] $tags */
		usort(
			$tags,
			function (SemanticVersion $v1, SemanticVersion $v2): int {
				return $v1->cmp($v2);
			}
		);
		$tagString = array_pop($tags)->getOrigVersion();
		return static::$latestTag = $tagString;
	}

	public function checkRequiredPackages(): void {
		if (!class_exists("Revolt\\EventLoop")) {
			fwrite(
				STDERR,
				"Nadybot cannot find all the required composer modules in 'vendor'.\n".
				"Please run 'composer install' to install all missing modules\n".
				"or download one of the Nadybot bundles and copy the 'vendor'\n".
				"directory from the zip-file into the Nadybot main directory.\n".
				"\n".
				"See https://github.com/Nadybot/Nadybot/wiki/Running#cloning-the-repository\n".
				"for more information.\n"
			);
			sleep(5);
			exit(1);
		}
	}

	public function checkRequiredModules(): void {
		if (version_compare(PHP_VERSION, "8.1.17", "<")) {
			fwrite(STDERR, "Nadybot 7 needs at least PHP version 8 to run, you have " . PHP_VERSION . "\n");
			sleep(5);
			exit(1);
		}
		$missing = [];
		$requiredModules = [
			["bcmath", "gmp"],
			"ctype",
			"date",
			"dom",
			"filter",
			"json",
			"pcre",
			"PDO",
			"simplexml",
			["pdo_mysql", "pdo_sqlite"],
			"Reflection",
			"sockets",
			"fileinfo",
			"tokenizer",
		];
		foreach ($requiredModules as $requiredModule) {
			if (is_string($requiredModule) && !extension_loaded($requiredModule)) {
				$missing []= $requiredModule;
			} elseif (is_array($requiredModule)) {
				if (!count(array_filter($requiredModule, "extension_loaded"))) {
					$missing []= join(" or ", $requiredModule);
				}
			}
		}
		if (!count($missing)) {
			return;
		}
		fwrite(STDERR, "Nadybot needs the following missing PHP-extensions: " . join(", ", $missing) . ".\n");
		sleep(5);
		exit(1);
	}

	/** Run the bot in an endless loop */
	public function run(): void {
		if (!static::isLinux()) {
			putenv('AMP_FS_DRIVER=' . BlockingFilesystemDriver::class);
		}
		$this->parseOptions();
		// set default timezone
		date_default_timezone_set("UTC");

		$config = $this->getConfigFile();
		Registry::setInstance(Registry::formatName(BotConfig::class), $config);
		$retryHandler = new HttpRetry(8);
		Registry::injectDependencies($retryHandler);
		$rateLimitRetryHandler = new HttpRetryRateLimits();
		Registry::injectDependencies($rateLimitRetryHandler);
		$httpClientBuilder = (new HttpClientBuilder())
		->retry(0)
		->intercept(new SetRequestHeaderIfUnset("User-Agent", "Nadybot ".self::getVersion()))
		->intercept($retryHandler)
		->intercept($rateLimitRetryHandler);
		$httpProxy = getenv('http_proxy');
		if ($httpProxy !== false) {
			$proxyHost = parse_url($httpProxy, PHP_URL_HOST);
			$proxyScheme = parse_url($httpProxy, PHP_URL_SCHEME);
			$proxyPort = parse_url($httpProxy, PHP_URL_PORT) ?? ($proxyScheme === 'https' ? 443 : 80);
			if (is_string($proxyScheme) && is_string($proxyHost) && is_int($proxyPort)) {
				$connector = new Http1TunnelConnector("{$proxyHost}:{$proxyPort}");
				$httpClientBuilder = $httpClientBuilder->usingPool(
					new UnlimitedConnectionPool(
						new DefaultConnectionFactory($connector)
					)
				);
			}
		}
		Registry::setInstance("HttpClientBuilder", $httpClientBuilder);
		$this->checkRequiredModules();
		$this->checkRequiredPackages();
		$this->createMissingDirs();

		// these must happen first since the classes that are loaded may be used by processes below
		if (isset($config->general->timezone) && @date_default_timezone_set($config->general->timezone) === false) {
			die("Invalid timezone: \"{$config->general->timezone}\"\n");
		}
		$logFolderName = "{$config->paths->logs}/{$config->main->character}.{$config->main->dimension}";

		$this->setErrorHandling($logFolderName);
		$this->logger = new LoggerWrapper("Core/BotRunner");
		Registry::injectDependencies($this->logger);

		$this->sendBotBanner();

		if ($this->showSetupDialog()) {
			$config = $this->getConfigFile();
		}
		$this->setWindowTitle($config);


		$version = self::getVersion();
		$fsDriverClass = getenv('AMP_FS_DRIVER');
		if ($fsDriverClass !== false && class_exists($fsDriverClass) && is_subclass_of($fsDriverClass, FilesystemDriver::class)) {
			$fsDriver = new $fsDriverClass();
		} else {
			$fsDriver = createDefaultDriver();
		}
		if ($fsDriver instanceof EioFilesystemDriver) {
			$fsDriver = new ParallelFilesystemDriver();
		}
		Registry::setInstance("filesystem", filesystem($fsDriver));

		$this->logger->notice(
			"Starting {name} {version} on RK{dimension} using ".
			"PHP {phpVersion}, {loopType} event loop, ".
			"{fsType} filesystem, and {dbType}...",
			[
				"name" => $config->main->character,
				"version" => $version,
				"dimension" => $config->main->dimension,
				"phpVersion" => phpversion(),
				"loopType" => class_basename(EventLoop::getDriver()),
				"fsType" => class_basename($fsDriver),
				"dbType" => $config->database->type->name,
			]
		);

		$this->classLoader = new ClassLoader($config->paths->modules);
		Registry::injectDependencies($this->classLoader);
		$this->classLoader->loadInstances();
		$msgHub = Registry::getInstance(MessageHub::class);
		if (isset($msgHub) && $msgHub instanceof MessageHub) {
			LegacyLogger::registerMessageEmitters($msgHub);
		}

		$signalHandler = function (string $token, int $sigNo): void {
			$this->logger->notice('Shutdown requested.');
			exit;
		};
		$handlers = [];
		if (function_exists('sapi_windows_set_ctrl_handler')) {
			sapi_windows_set_ctrl_handler($signalHandler, true);
		} else {
			$handlers []= EventLoop::onSignal(SIGINT, $signalHandler);
			$handlers []= EventLoop::onSignal(SIGTERM, $signalHandler);
		}
		$this->connectToDatabase();
		if (function_exists('sapi_windows_set_ctrl_handler')) {
			sapi_windows_set_ctrl_handler($signalHandler, false);
		}
		foreach ($handlers as $handler) {
			EventLoop::cancel($handler);
		}
		$this->prefillSettingProperties();

		$this->runUpgradeScripts();
		EventLoop::run();
		if ((static::$arguments["migrate-only"]??true) === false) {
			exit(0);
		}

		[$server, $port] = $this->getServerAndPort($config);

		/** @var Nadybot */
		$chatBot = Registry::getInstance(Nadybot::class);

		// startup core systems, load modules and call setup methods
		/** @var DB */
		$db = Registry::getInstance(DB::class);
		if ($db->table(CommandManager::DB_TABLE)->exists()) {
			$this->logger->notice("Initializing modules...");
		} else {
			$this->logger->notice("Initializing modules and db tables...");
		}
		$chatBot->init($this);

		if ((static::$arguments["setup-only"]??true) === false) {
			exit(0);
		}

		// connect to ao chat server
		$chatBot->connectAO($config->main->login, $config->main->password, (string)$server, (int)$port);

		// pass control to Nadybot class
		$chatBot->run();
	}

	/** Utility function to check whether the bot is running Windows */
	public static function isWindows(): bool {
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/** Utility function to check whether the bot is running Linux */
	public static function isLinux(): bool {
		return PHP_OS_FAMILY === 'Linux';
	}

	public function getConfigFile(): BotConfig {
		if (isset($this->configFile)) {
			return $this->configFile;
		}
		$configFilePath = static::$arguments["c"] ?? "conf/config.php";
		return $this->configFile = BotConfig::loadFromFile($configFilePath);
	}

	protected function createMissingDirs(): void {
		foreach (get_object_vars($this->getConfigFile()->paths) as $name => $dir) {
			if (is_string($dir) && !@file_exists($dir)) {
				@mkdir($dir, 0700);
			}
		}
		foreach ($this->getConfigFile()->paths->modules as $dir) {
			if (is_string($dir) && !@file_exists($dir)) {
				@mkdir($dir, 0700);
			}
		}
	}

	/**
	 * Get AO's chat server hostname and port
	 *
	 * @return (string|int)[] [(string)Server, (int)Port]
	 *
	 * @phpstan-return array{string,int}
	 */
	protected function getServerAndPort(BotConfig $config): array {
		// Choose server
		if ($config->proxy?->enabled === true) {
			assert(isset($config->proxy));
			// For use with the AO chat proxy ONLY!
			$server = $config->proxy->server;
			$port = $config->proxy->port;
		} elseif ($config->main->dimension === 4) {
			$server = "chat.dt.funcom.com";
			$port = 7109;
		} elseif ($config->main->dimension === 5) {
			$server = "chat.d1.funcom.com";
			$port = 7105;
		} elseif ($config->main->dimension === 6) {
			$server = "chat.d1.funcom.com";
			$port = 7106;
		} else {
			$this->logger->error("No valid server to connect with! Available dimensions are 4, 5, and 6.");
			die();
		}
		return [$server, $port];
	}

	private function parseOptions(): void {
		$options = getopt(
			"c:v",
			[
				"help",
				"migrate-only",
				"setup-only",
				"strict",
				"log-config:",
				"migration-errors-fatal",
			],
			$restPos
		);
		if ($options === false) {
			fwrite(STDERR, "Unable to parse arguments passed to the bot.\n");
			sleep(5);
			exit(1);
		}
		$argv = array_slice($this->argv, $restPos);
		static::$arguments = $options;
		if (count($argv) > 0) {
			static::$arguments["c"] = array_shift($argv);
		}
		if (isset(static::$arguments["help"])) {
			$this->showSyntaxHelp();
			exit(0);
		}
	}

	private function showSyntaxHelp(): void {
		echo(
			"Usage: " . PHP_BINARY . " " . ($_SERVER["argv"][0] ?? "main.php").
			" [options] [-c] <config file>\n\n".
			"positional arguments:\n".
			"  <config file>         A Nadybot configuration file, usually conf/config.php\n".
			"\n".
			"options:\n".
			"  --help                Show this help message and exit\n".
			"  --migrate-only        Only run the database migration and then exit\n".
			"  --setup-only          Stop the bot after the setup handlers have been called\n".
			"  --log-config=<file>   Use an alternative config file for the logger. The default\n".
			"                        configuration is in conf/logging.json\n".
			"  --migration-errors-fatal\n".
			"                        Stop the bot startup if any of the database migrations fails\n".
			"  -v                    Enable logging INFO. Use -v -v to also log DEBUG\n"
		);
	}

	/**
	 * Make sure that the values of properties that are linked to settings
	 * is filled with the last known values from the database
	 */
	private function prefillSettingProperties(): void {
		/** @var SettingManager */
		$settingManager = Registry::getInstance(SettingManager::class);
		foreach (Registry::getAllInstances() as $name => $instance) {
			$refObj = new ReflectionObject($instance);
			foreach ($refObj->getProperties() as $refProp) {
				foreach ($refProp->getAttributes(NCA\DefineSetting::class, ReflectionAttribute::IS_INSTANCEOF) as $refAttr) {
					try {
						/** @var NCA\DefineSetting */
						$attr = $refAttr->newInstance();
					} catch (\Throwable $e) {
						$this->logger->error('Incompatible attribute #[{attrName}] in {loc}: {error}', [
							"attrName" => str_replace('Nadybot\Core\Attributes', 'NCA', $refAttr->getName()),
							"error" => $e->getMessage(),
							"exception" => $e,
							"loc" => $refProp->getDeclaringClass()->getName() . '::$' . $refProp->getName(),
						]);
						exit;
					}
					$attr->name ??= Nadybot::toSnakeCase($refProp->getName());
					try {
						$value = $settingManager->getTyped($attr->name);
					} catch (Throwable) {
						// If the database is initialized for the first time
						return;
					}
					if ($value !== null) {
						$this->logger->info('Setting {class}::${property} to {value}', [
							"class" => class_basename($instance),
							"property" => $refProp->getName(),
							"value" => json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
						]);
						try {
							$refProp->setValue($instance, $value);
						} catch (Throwable) {
						}
					}
				}
			}
		}
	}

	/** Get a message describing the bot's codebase */
	private function sendBotBanner(): void {
		$this->logger->notice(
			PHP_EOL.
			" _   _  __     ".PHP_EOL.
			"| \ | |/ /_    Nadybot version: {version}".PHP_EOL.
			"|  \| | '_ \   Project Site:    {project_url}".PHP_EOL.
			"| |\  | (_) |  In-Game Contact: {in_game_contact}".PHP_EOL.
			"|_| \_|\___/   Discord:         {discord_link}".PHP_EOL.
			PHP_EOL,
			[
				"version" => self::getVersion(),
				"project_url" => "https://github.com/Nadybot/Nadybot",
				"in_game_contact" => "Nady",
				"discord_link" => "https://discord.gg/aDR9UBxRfg",
			]
		);
	}

	/** Setup proper error-reporting, -handling and -logging */
	private function setErrorHandling(string $logFolderName): void {
		error_reporting(E_ALL & ~E_STRICT & ~E_WARNING & ~E_NOTICE);
		ini_set("log_errors", "1");
		ini_set('display_errors', "1");
		ini_set("error_log", "{$logFolderName}/php_errors.log");
	}

	/** Guide customer through setup if needed */
	private function showSetupDialog(): bool {
		if (!$this->shouldShowSetup()) {
			return false;
		}
		$setup = new Setup($this->getConfigFile());
		$setup->showIntro();
		$this->logger->notice("Reloading configuration and testing your settings.");
		return true;
	}

	/** Is information missing to run the bot? */
	private function shouldShowSetup(): bool {
		return empty($this->configFile->main->login) || empty($this->configFile->main->password) || empty($this->configFile->main->character);
	}

	/** Set the title of the command prompt window in Windows */
	private function setWindowTitle(BotConfig $config): void {
		if ($this->isWindows() === false) {
			return;
		}
		\Safe\system("title {$config->main->character} - Nadybot");
	}

	/** Connect to the database */
	private function connectToDatabase(): void {
		/** @var ?DB */
		$db = Registry::getInstance(DB::class);
		if (!isset($db)) {
			throw new Exception("Cannot find DB instance.");
		}
		$config = $this->getConfigFile();
		$db->connect($config->database);
	}

	/**
	 * Run migration scripts to keep the SQL schema up-to-date
	 */
	private function runUpgradeScripts(): void {
		/** @var DB */
		$db = Registry::getInstance(DB::class);
		$db->createDatabaseSchema();
	}
}
