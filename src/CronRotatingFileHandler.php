<?php declare(strict_types=1);

namespace Kingsoft\MonologHandler;

/*
 * This file is part of the Monolog package.
 * 
 * (c) TheKing2 <theking2@king.ma>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Level;
use Monolog\Utils;
use Monolog\Handler;

/**
 * Stores logs rotated by cron expression
 *
 * This rotation is only intended to be used as a workaround. Using logrotate to
 * handle the rotation is strongly encouraged when you can use it.
 *
 */
class CronRotatingFileHandler extends AbstractRotatingFileHandler implements Handler\HandlerInterface
{
  private $stateFilename;

  /**
   * @param int      $maxFiles       The maximal amount of files to keep (0 means unlimited)
   * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
   * @param bool     $useLocking     Try to lock log file before doing any writes
   */
  public function __construct(
    string $filename,
    int|string|Level $level = Level\Level::Debug,
    array $rotateSettings = [],
    bool $bubble = false,
    ?int $filePermission = null,
    bool $useLocking = false
  ) {
    $this->filename = Utils::canonicalizePath( $filename );
 
    parent::__construct( $this->filename, $level, $rotateSettings, $bubble, $filePermission, $useLocking );
  }
  /**
   * checkRotate
   *
   * @return void
   */
  protected function rotate(): void
  {
    $maxFiles       = (int)$this->rotateSettings['maxFiles'] ?? 10;
    $minSize        = (int)$this->rotateSettings['minSize'] ?? 0;
    $compress       = (bool)$this->rotateSettings['compress'] ?? false;


    // create state file if not exist
    $rotation = ( new \Cesargb\Log\Rotation() )
      ->files( $maxFiles )
      ->minSize( $minSize );
    if( $compress ) {
      $rotation->compress();
    }
  }

	protected function getNextRotation(): \DateTimeImmutable
	{
	    $cronExpression = $rotateSettings['cronExpression'] ?? '* * */1 * *';

		$cron = new \Cron\CronExpression( $cronExpression );
    $stateFilename = Utils::canonicalizePath( $this->filename . '.state' );
    $fileInfo      = new \SplFileInfo( $stateFilename );
    if( !$fileInfo->isFile() ) {
      touch( $stateFilename );
    }
		// check if log file is due based on state file modified time
		$dateTime     = new \DateTimeImmutable();                         // current datetime
		$filedateTime = $dateTime->setTimeStamp( $fileInfo->getMTime() ); // state-file datetime

		return \DateTimeImmutable::createFromMutable($cron->getNextRunDate( $filedateTime ));           // next-run datetime

	}

}
