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

use \DateTimeImmutable as DTi;
use Monolog\Level;
use Monolog\Utils;
use Monolog\Handler;
use \Cron\CronExpression;

/**
 * Stores logs rotated by cron expression
 *
 * This rotation is only intended to be used as a workaround. Using logrotate to
 * handle the rotation is strongly encouraged when you can use it.
 *
 */
class CronRotatingFileHandler extends AbstractRotatingFileHandler implements Handler\HandlerInterface
{
  private string         $stateFilename;
  private DTI            $nextRotation;
  private CronExpression $cron;

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
    if( empty( $rotateSettings ) ) {
      throw new \InvalidArgumentException( 'Missing rotateSettings' );
    }
    if( empty( $rotateSettings['cronExpression'] ) ) {
      throw new \InvalidArgumentException( 'Missing cronExpression in rotateSettings' );
    }
    $cronExpression = $rotateSettings['cronExpression'] ?? '* * */1 * *';
    $this->cron     = new CronExpression( $cronExpression );

    $this->filename   = Utils::canonicalizePath( $filename );
    $this->mustRotate = $this->mustRotate();

    parent::__construct( $this->filename, $level, $rotateSettings, $bubble, $filePermission, $useLocking );
  }

  protected function mustRotate(): bool
  {
    $this->nextRotation = $this->getNextRotation();

    // check if log file is due based on state file modified time
    return $this->nextRotation <= new DTI();
  }

  /**
   * Get the next rotation date, based on the cron expression and the state file
   */
  protected function getNextRotation(): DTI
  {
    // create state file if not exists
    $stateFilename = Utils::canonicalizePath( $this->filename . '.state' );
    $fileInfo      = new \SplFileInfo( $stateFilename );
    if( !$fileInfo->isFile() ) {
      touch( $stateFilename );
    }
    // check if log file is due based on state file modified time
    $dateTime     = new DTI();                         // current datetime
    $filedateTime = $dateTime->setTimeStamp( $fileInfo->getMTime() ); // state-file datetime

    return DTI::createFromMutable( $this->cron->getNextRunDate( $filedateTime ) );           // next-run datetime
  }

}
