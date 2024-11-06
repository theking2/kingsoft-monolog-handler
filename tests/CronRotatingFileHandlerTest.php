<?php declare(strict_types=1);
namespace Kingsoft\MonologHandler;
use Monolog\{Logger, LogRecord};

class CronRotatingFileHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testWrite()
    {
        $log = new Logger( SETTINGS[ 'log' ][ 'name' ] );
        $log->pushHandler(
            handler: new CronRotatingFileHandler(
                filename: 'test.log',
                level: SETTINGS[ 'log' ][ 'level' ]
            )
        );
        $log->debug( message: 'test' );
        $this->assertFileExists( 'test.log' );
        $this->assertStringContainsString( 'test', file_get_contents( 'test.log' ) );
        unlink( filename: 'test.log' );
        unlink( filename: 'test.log.state' );
    }
}
const SETTINGS = [ 
    'log'       => [ 
        'level' => 'debug',
        'file'  => 'test.log',
        'name'  => 'api'
    ],
    'logrotate' => [ 
        'cron'     => '0 0 * * *',
        'maxsize'  => 1048576,
        'maxfiles' => 5
    ],

];
