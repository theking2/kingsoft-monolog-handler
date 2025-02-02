<?php declare(strict_types=1);
namespace Kingsoft\MonologHandler;

require_once __DIR__ . '/../vendor/autoload.php';
use Monolog\{Logger, LogRecord};

class CronRotatingFileHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        # Turn on error reporting
        error_reporting( E_ALL );
        mkdir( SETTINGS['log']['logs'], recursive: true );
        $this->current_file = SETTINGS['log']['logs'] . DIRECTORY_SEPARATOR . SETTINGS['log']['file'];
    }
    public function openLog(): Logger
    {
        $log = new Logger( SETTINGS['log']['name'] );
        return $log->pushHandler(
            handler: new CronRotatingFileHandler(
                filename: SETTINGS['log']['logs'] . DIRECTORY_SEPARATOR . SETTINGS['log']['file'],
                level: SETTINGS['log']['level'],
                rotateSettings: SETTINGS['logrotate']
            )
        );
    }
    public function waitForNextMinute(): void
    {
        $now        = time();
        $nextMinute = strtotime( date( 'Y-m-d H:i:59', $now ) ) + 1;
        sleep( $nextMinute - $now );
    }
    private string $current_file;
    public function testWrite()
    {
        $log = $this->openLog();
        $log->info( message: 'test1' );
        $this->assertFileExists( $this->current_file );
        $this->assertStringContainsString( 'test1', file_get_contents( $this->current_file ) );
        unset( $log );

        $this->waitForNextMinute();
        $log = $this->openLog();
        $log->info( message: 'test2' );
        unset( $log );
        // check if log file is rotated
        $this->assertFileExists( $this->current_file . '.1' );
        $this->assertStringNotContainsString( 'test1', file_get_contents( $this->current_file ) );
        $this->assertStringContainsString( 'test2', file_get_contents( $this->current_file ) );

        // check if list is limited to maxFiles
        $this->waitForNextMinute();
        $log = $this->openLog();
        $log->info( message: 'test3' );
        unset( $log );

        // log file should be rotated
        // but not twice
        $this->assertFileDoesNotExist( $this->current_file . '.3' );
    }
    public function tearDown(): void
    {
        unlink( filename: $this->current_file . '.state' );
        unlink( filename: $this->current_file );
        unlink( filename: $this->current_file . '.1' );
        unlink( filename: $this->current_file . '.2' );
        rmdir( dirname( $this->current_file ) );
    }
}
const SETTINGS = [
    'log'       => [
        'logs'  => 'D:\Projekten\theking2\Kingsoft\kingsoft-monolog-handler\logs',
        'file'  => 'test.log',
        'level' => 'info',
        'name'  => 'api'
    ],
    // cron tab default for every minute, maxsize 1MB, maxfiles 5
    'logrotate' => [
        'cronExpression' => '* * * * *',
        'maxsize'        => 1048576,
        'maxFiles'       => 2,
    ],

];
